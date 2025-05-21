<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get current admin user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if site_settings table exists
$settingsTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'site_settings'");
if ($result->num_rows > 0) {
    $settingsTableExists = true;
    
    // Get site settings
    $settings_query = $conn->query("SELECT * FROM site_settings ORDER BY setting_group, setting_name");
    $site_settings = [];
    while ($setting = $settings_query->fetch_assoc()) {
        $site_settings[$setting['setting_group']][$setting['setting_name']] = $setting;
    }
}

// Check if user_activity table exists
$activityTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'user_activity'");
if ($result->num_rows > 0) {
    $activityTableExists = true;
    
    // Get recent activity
    $activity_query = $conn->query("
        SELECT a.*, u.user_name, u.user_email 
        FROM user_activity a 
        JOIN users u ON a.user_id = u.user_id 
        ORDER BY a.created_at DESC 
        LIMIT 50
    ");
    
    $activities = [];
    while ($activity = $activity_query->fetch_assoc()) {
        $activities[] = $activity;
    }
}

// Check if user_sessions table exists
$sessionsTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'user_sessions'");
if ($result->num_rows > 0) {
    $sessionsTableExists = true;
    
    // Get active sessions
    $sessions_query = $conn->query("
        SELECT s.*, u.user_name, u.user_email 
        FROM user_sessions s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.is_active = 1
        ORDER BY s.last_activity DESC
    ");
    
    $sessions = [];
    while ($session = $sessions_query->fetch_assoc()) {
        $sessions[] = $session;
    }
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which form was submitted
    $form_type = $_POST['form_type'] ?? '';
    
    // Profile Update
    if ($form_type === 'profile_update') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $user_name = trim($_POST['user_name'] ?? '');
        $user_email = trim($_POST['user_email'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        
        // Update profile
        $update = $conn->prepare("
            UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                user_name = ?, 
                user_email = ?,
                phone_number = ?
            WHERE user_id = ?
        ");
        
        $update->bind_param('sssssi', $first_name, $last_name, $user_name, $user_email, $phone_number, $user_id);
        
        if ($update->execute()) {
            // Log activity if table exists
            if ($activityTableExists) {
                $activity_desc = "Updated admin profile information";
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $conn->query("INSERT INTO user_activity (user_id, activity_type, activity_description, ip_address) 
                             VALUES ($user_id, 'profile_update', '$activity_desc', '$ip_address')");
            }
            
            $success_message = "Profile updated successfully!";
            
            // Refresh user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error_message = "Failed to update profile: " . $conn->error;
        }
    }
    
    // Password Update
    else if ($form_type === 'password_update') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All password fields are required.";
        } else if ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } else if (!password_verify($current_password, $user['password_hash'])) {
            $error_message = "Current password is incorrect.";
        } else {
            // Update password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $update->bind_param('si', $password_hash, $user_id);
            
            if ($update->execute()) {
                // Log activity if table exists
                if ($activityTableExists) {
                    $activity_desc = "Changed account password";
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $conn->query("INSERT INTO user_activity (user_id, activity_type, activity_description, ip_address) 
                                 VALUES ($user_id, 'password_change', '$activity_desc', '$ip_address')");
                }
                
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Failed to update password: " . $conn->error;
            }
        }
    }
    
    // Site Settings Update
    else if ($form_type === 'site_settings' && $settingsTableExists) {
        $setting_group = $_POST['setting_group'] ?? '';
        
        if (!empty($setting_group)) {
            // Get all settings for this group
            $settings_stmt = $conn->prepare("SELECT setting_name FROM site_settings WHERE setting_group = ?");
            $settings_stmt->bind_param('s', $setting_group);
            $settings_stmt->execute();
            $result = $settings_stmt->get_result();
            
            $updated = true;
            
            while ($setting = $result->fetch_assoc()) {
                $setting_name = $setting['setting_name'];
                $setting_value = $_POST[$setting_name] ?? '';
                
                // For checkboxes/toggles that might not be set
                if (strpos($setting_name, 'enable_') === 0 || strpos($setting_name, 'maintenance_') === 0) {
                    $setting_value = isset($_POST[$setting_name]) ? '1' : '0';
                }
                
                // Update setting
                $update = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_by = ? WHERE setting_name = ?");
                $update->bind_param('sis', $setting_value, $user_id, $setting_name);
                
                if (!$update->execute()) {
                    $updated = false;
                    break;
                }
            }
            
            if ($updated) {
                // Log activity if table exists
                if ($activityTableExists) {
                    $activity_desc = "Updated site settings: $setting_group";
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $conn->query("INSERT INTO user_activity (user_id, activity_type, activity_description, ip_address) 
                                 VALUES ($user_id, 'settings_update', '$activity_desc', '$ip_address')");
                }
                
                $success_message = ucfirst($setting_group) . " settings updated successfully!";
                
                // Refresh settings
                if ($settingsTableExists) {
                    $settings_query = $conn->query("SELECT * FROM site_settings ORDER BY setting_group, setting_name");
                    $site_settings = [];
                    while ($setting = $settings_query->fetch_assoc()) {
                        $site_settings[$setting['setting_group']][$setting['setting_name']] = $setting;
                    }
                }
            } else {
                $error_message = "Failed to update settings: " . $conn->error;
            }
        }
    }
}

// Helper function to format dates
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

// Helper function to get setting value
function getSetting($settings, $group, $name, $default = '') {
    return isset($settings[$group][$name]['setting_value']) ? $settings[$group][$name]['setting_value'] : $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Settings - Car Rental System</title>
    <?php include('assets/includes/header_link.php'); ?>
    <style>
        .nav-tabs .nav-link {
            border: none;
            color: #555;
            padding: 1rem;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link.active {
            color: #ff5b00;
            border-bottom: 3px solid #ff5b00;
            background: transparent;
            font-weight: 600;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            border-bottom: 3px solid #ddd;
            background-color: #f8f9fa;
        }
        .form-label {
            font-weight: 500;
            color: #444;
        }
        .settings-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .settings-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .settings-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
        }
        .settings-card .card-body {
            padding: 20px;
        }
        .activity-item {
            border-left: 3px solid #ff5b00;
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .activity-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ff5b00;
            left: -7px;
            top: 0;
        }
        .activity-time {
            color: #888;
            font-size: 0.8rem;
        }
        .password-strength-meter {
            height: 5px;
            background: #eee;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        .password-strength-meter div {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        .password-requirements li {
            font-size: 0.8rem;
            color: #777;
            transition: all 0.3s ease;
        }
        .password-requirements li.met {
            color: #28a745;
        }
        .password-requirements li.met i {
            color: #28a745;
        }
        .toggle-password {
            cursor: pointer;
        }
        .session-item {
            border-left: 3px solid #17a2b8;
            padding-left: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .session-item.current {
            border-left-color: #28a745;
            background-color: #f0fff0;
        }
        .btn-primary {
            background-color: #ff5b00;
            border-color: #ff5b00;
        }
        .btn-primary:hover {
            background-color: #e65100;
            border-color: #e65100;
        }
        .btn-outline-primary {
            color: #ff5b00;
            border-color: #ff5b00;
        }
        .btn-outline-primary:hover {
            background-color: #ff5b00;
            border-color: #ff5b00;
        }
        .form-control:focus, .form-select:focus {
            border-color: #ff5b00;
            box-shadow: 0 0 0 0.25rem rgba(255, 91, 0, 0.25);
        }
        .form-check-input:checked {
            background-color: #ff5b00;
            border-color: #ff5b00;
        }
        .tab-content {
            padding: 20px 0;
        }
        .dashboard-menu ul li a.active {
            background-color: #ff5b00;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include('assets/includes/header.php'); ?>

        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12 col-12">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <a href="admin_dashboard.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                        <h2 class="breadcrumb-title">Admin Settings</h2>
                        <nav aria-label="breadcrumb" class="page-breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="admin_dashboard.php">Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Settings</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <!-- Dashboard Menu -->
        <div class="dashboard-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="dashboard-menu">
                            <ul class="nav justify-content-center">
                                <li>
                                    <a href="admin_dashboard.php">
                                        <img src="assets/img/icons/dashboard-icon.svg" alt="Icon">
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="admin_bookings.php">
                                        <img src="assets/img/icons/booking-icon.svg" alt="Icon">
                                        <span>Bookings</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="admin_cars.php">
                                        <img src="assets/img/icons/car-rental-icon.svg" alt="Icon">
                                        <span>Cars</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="admin_users.php">
                                        <img src="assets/img/icons/profile-icon.svg" alt="Icon">
                                        <span>Users</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="admin_settings.php" class="active">
                                        <img src="assets/img/icons/settings-icon.svg" alt="Icon">
                                        <span>Settings</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Dashboard Menu -->

        <div class="content dashboard-content">
            <div class="container">
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!$settingsTableExists || !$activityTableExists || !$sessionsTableExists): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> Some required database tables are missing. Please run the <a href="backend/db_setup_additional.php" class="alert-link">database setup script</a> to create them.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
                                    <i class="fas fa-user me-2"></i>Profile
                                </button>
                            </li>
                            <?php if ($settingsTableExists): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="false">
                                    <i class="fas fa-cog me-2"></i>General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                                    <i class="fas fa-envelope me-2"></i>Contact
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking" type="button" role="tab" aria-controls="booking" aria-selected="false">
                                    <i class="fas fa-calendar-alt me-2"></i>Booking
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">
                                    <i class="fas fa-credit-card me-2"></i>Payment
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">
                                    <i class="fas fa-share-alt me-2"></i>Social
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if ($sessionsTableExists): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                                    <i class="fas fa-shield-alt me-2"></i>Security
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if ($activityTableExists): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">
                                    <i class="fas fa-history me-2"></i>Activity
                                </button>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <div class="tab-content" id="settingsTabsContent">
                            <!-- Profile Tab -->
                            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="row">
                                    <!-- Admin Profile -->
                                    <div class="col-lg-6">
                                        <div class="card settings-card mb-4">
                                            <div class="card-header">
                                                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Admin Profile</h5>
                                            </div>
                                            <div class="card-body">
                                                <form method="post" action="">
                                                    <input type="hidden" name="form_type" value="profile_update">
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">First Name</label>
                                                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Last Name</label>
                                                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Username</label>
                                                        <input type="text" name="user_name" class="form-control" value="<?= htmlspecialchars($user['user_name'] ?? '') ?>">
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Email Address</label>
                                                        <input type="email" name="user_email" class="form-control" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>">
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Phone Number</label>
                                                        <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                                                    </div>
                                                    
                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-primary btn-lg">
                                                            <i class="fas fa-save me-2"></i>Save Profile
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Change Password -->
                                    <div class="col-lg-6">
                                        <div class="card settings-card mb-4">
                                            <div class="card-header">
                                                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
                                            </div>
                                            <div class="card-body">
                                                <form method="post" action="" id="passwordForm">
                                                    <input type="hidden" name="form_type" value="password_update">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Current Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="current_password" id="current_password" class="form-control">
                                                            <span class="input-group-text toggle-password" data-target="current_password">
                                                                <i class="fas fa-eye"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="new_password" id="new_password" class="form-control">
                                                            <span class="input-group-text toggle-password" data-target="new_password">
                                                                <i class="fas fa-eye"></i>
                                                            </span>
                                                        </div>
                                                        <div class="password-strength-meter mt-2">
                                                            <div id="password-strength-bar"></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Confirm New Password</label>
                                                        <div class="input-group">
                                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                                            <span class="input-group-text toggle-password" data-target="confirm_password">
                                                                <i class="fas fa-eye"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Password Requirements:</label>
                                                        <ul class="password-requirements list-unstyled">
                                                            <li id="length-check"><i class="fas fa-times-circle me-2"></i>At least 8 characters</li>
                                                            <li id="uppercase-check"><i class="fas fa-times-circle me-2"></i>At least one uppercase letter</li>
                                                            <li id="number-check"><i class="fas fa-times-circle me-2"></i>At least one number</li>
                                                            <li id="special-check"><i class="fas fa-times-circle me-2"></i>At least one special character</li>
                                                            <li id="match-check"><i class="fas fa-times-circle me-2"></i>Passwords match</li>
                                                        </ul>
                                                    </div>
                                                    
                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-primary btn-lg" id="change-password-btn" disabled>
                                                            <i class="fas fa-key me-2"></i>Change Password
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- General Settings Tab -->
                            <?php if ($settingsTableExists): ?>
                            <div class="tab-pane fade" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="">
                                            <input type="hidden" name="form_type" value="site_settings">
                                            <input type="hidden" name="setting_group" value="general">
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Site Name</label>
                                                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'general', 'site_name', 'Car Rental System')) ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Site Description</label>
                                                    <input type="text" name="site_description" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'general', 'site_description', 'Rent your dream car today')) ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" <?= getSetting($site_settings, 'system', 'maintenance_mode') == '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                                </div>
                                                <small class="text-muted">When enabled, only administrators can access the site.</small>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-save me-2"></i>Save General Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Settings Tab -->
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="">
                                            <input type="hidden" name="form_type" value="site_settings">
                                            <input type="hidden" name="setting_group" value="contact">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Contact Email</label>
                                                <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'contact', 'contact_email')) ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Contact Phone</label>
                                                <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'contact', 'contact_phone')) ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Address</label>
                                                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars(getSetting($site_settings, 'contact', 'address')) ?></textarea>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-save me-2"></i>Save Contact Information
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Settings Tab -->
                            <div class="tab-pane fade" id="booking" role="tabpanel" aria-labelledby="booking-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Booking Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="">
                                            <input type="hidden" name="form_type" value="site_settings">
                                            <input type="hidden" name="setting_group" value="booking">
                                            
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enable_bookings" id="enable_bookings" <?= getSetting($site_settings, 'features', 'enable_bookings') == '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="enable_bookings">Enable Bookings</label>
                                                </div>
                                                <small class="text-muted">When disabled, users cannot make new bookings.</small>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Minimum Rental Days</label>
                                                    <input type="number" name="min_rental_days" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'booking', 'min_rental_days', '1')) ?>" min="1">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Maximum Rental Days</label>
                                                    <input type="number" name="max_rental_days" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'booking', 'max_rental_days', '30')) ?>" min="1">
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-save me-2"></i>Save Booking Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Settings Tab -->
                            <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="">
                                            <input type="hidden" name="form_type" value="site_settings">
                                            <input type="hidden" name="setting_group" value="payment">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Currency Symbol</label>
                                                <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'payment', 'currency_symbol', '$')) ?>">
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Tax Rate (%)</label>
                                                    <input type="number" name="tax_rate" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'payment', 'tax_rate', '10')) ?>" min="0" step="0.01">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Booking Fee</label>
                                                    <input type="number" name="booking_fee" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'payment', 'booking_fee', '5')) ?>" min="0" step="0.01">
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-save me-2"></i>Save Payment Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Social Media Tab -->
                            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Links</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="">
                                            <input type="hidden" name="form_type" value="site_settings">
                                            <input type="hidden" name="setting_group" value="social">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Facebook URL</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fab fa-facebook-f"></i></span>
                                                    <input type="url" name="facebook_url" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'social', 'facebook_url')) ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Twitter URL</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                                    <input type="url" name="twitter_url" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'social', 'twitter_url')) ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Instagram URL</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                                    <input type="url" name="instagram_url" class="form-control" value="<?= htmlspecialchars(getSetting($site_settings, 'social', 'instagram_url')) ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-save me-2"></i>Save Social Media Links
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Security Tab -->
                            <?php if ($sessionsTableExists): ?>
                            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Active Sessions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            These are all active user sessions across the system. You can terminate any session if suspicious activity is detected.
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>User</th>
                                                        <th>IP Address</th>
                                                        <th>Login Time</th>
                                                        <th>Last Activity</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($sessions)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">No active sessions found.</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($sessions as $session): ?>
                                                            <tr>
                                                                <td>
                                                                    <?= htmlspecialchars($session['user_name']) ?>
                                                                    <?php if ($session['user_id'] == $user_id): ?>
                                                                        <span class="badge bg-success ms-2">You</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($session['ip_address']) ?></td>
                                                                <td><?= formatDate($session['login_time']) ?></td>
                                                                <td><?= formatDate($session['last_activity']) ?></td>
                                                                <td>
                                                                    <?php if ($session['user_id'] != $user_id): ?>
                                                                        <button class="btn btn-sm btn-danger">
                                                                            <i class="fas fa-times-circle me-1"></i>Terminate
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Current Session</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="d-grid gap-2 mt-3">
                                            <button class="btn btn-warning">
                                                <i class="fas fa-sign-out-alt me-2"></i>Terminate All Other Sessions
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Activity Tab -->
                            <?php if ($activityTableExists): ?>
                            <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                                <div class="card settings-card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>User Activity Log</h5>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>Export Log
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            This log shows recent user activities across the system. Use this to monitor for suspicious behavior.
                                        </div>
                                        
                                        <div class="activity-log">
                                            <?php if (empty($activities)): ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                                    <p>No activity has been recorded yet.</p>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($activities as $activity): ?>
                                                    <div class="activity-item">
                                                        <div class="d-flex justify-content-between">
                                                            <strong><?= htmlspecialchars($activity['user_name']) ?></strong>
                                                            <span class="activity-time"><?= formatDate($activity['created_at']) ?></span>
                                                        </div>
                                                        <div>
                                                            <?php
                                                            $icon = '';
                                                            switch($activity['activity_type']) {
                                                                case 'login': $icon = '<i class="fas fa-sign-in-alt text-success me-1"></i>'; break;
                                                                case 'logout': $icon = '<i class="fas fa-sign-out-alt text-warning me-1"></i>'; break;
                                                                case 'profile_update': $icon = '<i class="fas fa-user-edit text-primary me-1"></i>'; break;
                                                                case 'password_change': $icon = '<i class="fas fa-key text-danger me-1"></i>'; break;
                                                                case 'settings_update': $icon = '<i class="fas fa-cog text-info me-1"></i>'; break;
                                                                default: $icon = '<i class="fas fa-info-circle text-secondary me-1"></i>'; break;
                                                            }
                                                            echo $icon . htmlspecialchars($activity['activity_description']);
                                                            ?>
                                                        </div>
                                                        <small class="text-muted">IP: <?= htmlspecialchars($activity['ip_address'] ?? 'Unknown') ?></small>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include('assets/includes/footer.php'); ?>
    </div>

    <?php include('assets/includes/footer_link.php'); ?>

    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Password strength meter
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('password-strength-bar');
        const lengthCheck = document.getElementById('length-check');
        const uppercaseCheck = document.getElementById('uppercase-check');
        const numberCheck = document.getElementById('number-check');
        const specialCheck = document.getElementById('special-check');
        const matchCheck = document.getElementById('match-check');
        const changePasswordBtn = document.getElementById('change-password-btn');
        
        function updatePasswordStrength() {
            if (!newPassword) return;
            
            const password = newPassword.value;
            let strength = 0;
            let color = '';
            
            // Check length
            const hasLength = password.length >= 8;
            if (hasLength) {
                strength += 25;
                lengthCheck.classList.add('met');
                lengthCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
            } else {
                lengthCheck.classList.remove('met');
                lengthCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
            }
            
            // Check uppercase
            const hasUppercase = /[A-Z]/.test(password);
            if (hasUppercase) {
                strength += 25;
                uppercaseCheck.classList.add('met');
                uppercaseCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
            } else {
                uppercaseCheck.classList.remove('met');
                uppercaseCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
            }
            
            // Check number
            const hasNumber = /[0-9]/.test(password);
            if (hasNumber) {
                strength += 25;
                numberCheck.classList.add('met');
                numberCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
            } else {
                numberCheck.classList.remove('met');
                numberCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
            }
            
            // Check special character
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            if (hasSpecial) {
                strength += 25;
                specialCheck.classList.add('met');
                specialCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
            } else {
                specialCheck.classList.remove('met');
                specialCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
            }
            
            // Set color based on strength
            if (strength <= 25) {
                color = '#dc3545'; // red
            } else if (strength <= 50) {
                color = '#ffc107'; // yellow
            } else if (strength <= 75) {
                color = '#fd7e14'; // orange
            } else {
                color = '#28a745'; // green
            }
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = color;
            
            // Check if passwords match
            if (confirmPassword.value) {
                if (newPassword.value === confirmPassword.value) {
                    matchCheck.classList.add('met');
                    matchCheck.querySelector('i').classList.replace('fa-times-circle', 'fa-check-circle');
                } else {
                    matchCheck.classList.remove('met');
                    matchCheck.querySelector('i').classList.replace('fa-check-circle', 'fa-times-circle');
                }
            }
            
            // Enable/disable submit button
            const currentPassword = document.getElementById('current_password').value;
            const isValid = hasLength && hasUppercase && hasNumber && hasSpecial && 
                           newPassword.value === confirmPassword.value && currentPassword;
            
            changePasswordBtn.disabled = !isValid;
        }
        
        if (newPassword) {
            newPassword.addEventListener('input', updatePasswordStrength);
            confirmPassword.addEventListener('input', updatePasswordStrength);
            document.getElementById('current_password').addEventListener('input', updatePasswordStrength);
        }
        
        // Remember active tab
        const triggerTabList = [].slice.call(document.querySelectorAll('#settingsTabs button'));
        triggerTabList.forEach(function (triggerEl) {
            triggerEl.addEventListener('click', function (event) {
                localStorage.setItem('activeSettingsTab', event.target.id);
            });
        });
        
        // Restore active tab
        const activeTab = localStorage.getItem('activeSettingsTab');
        if (activeTab) {
            const tab = document.querySelector('#' + activeTab);
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }
    </script>
</body>
</html>
