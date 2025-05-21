<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get admin information
$admin_name = $_SESSION['user_name'];
$admin_email = $_SESSION['user_email'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Admin Settings - Car Rental</title>
    <?php include('assets/includes/header_link.php') ?>
    <style>
        .sidebar {
            background-color: #f8f9fa;
            min-height: calc(100vh - 70px);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            color: #555;
            transition: all 0.3s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background-color: #e7eeff;
            color: #4070f4;
            text-decoration: none;
        }
        .sidebar-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .settings-header {
            background-color: #222;
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .back-btn {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .back-btn:hover {
            background-color: rgba(255,255,255,0.2);
            color: #fff;
        }
        .back-btn i {
            margin-right: 5px;
        }
        .settings-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #eee;
        }
        .settings-tab {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 2px solid transparent;
        }
        .settings-tab.active {
            border-bottom: 2px solid #4070f4;
            color: #4070f4;
        }
        .settings-tab:hover:not(.active) {
            background-color: #f8f9fa;
        }
        .settings-content {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .profile-section {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .profile-name {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .profile-email {
            color: #777;
            font-size: 14px;
        }
        .logout-btn {
            color: #ff5252;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background-color: #ffeeee;
            color: #ff5252;
        }
        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s;
        }
        .activity-item:hover {
            background-color: #f8f9fa;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-time {
            color: #777;
            font-size: 12px;
        }
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
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.png" alt="Car Rental" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="booking_list.php">Cars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact_us.php">Contact Us</a>
                    </li>
                </ul>
                <div class="dropdown">
                    <a class="btn btn-outline-primary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Admin User
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_dashboard.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="admin_settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="backend/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Settings Header -->
    <div class="settings-header">
        <div class="container">
            <a href="admin_dashboard.php" class="back-btn mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1>Admin Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                    <li class="breadcrumb-item"><a href="admin_dashboard.php" class="text-white-50">Admin</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar">
                    <div class="profile-section">
                        <div>Profile</div>
                        <h5 class="profile-name">Admin User</h5>
                        <div class="profile-email"><?php echo htmlspecialchars($admin_email); ?></div>
                    </div>
                    
                    <a href="admin_dashboard.php" class="sidebar-link">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="admin_bookings.php" class="sidebar-link">
                        <i class="fas fa-calendar-check"></i> My Bookings
                    </a>
                    <a href="admin_cars.php" class="sidebar-link">
                        <i class="fas fa-car"></i> Cars
                    </a>
                    <a href="admin_users.php" class="sidebar-link">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="admin_settings.php" class="sidebar-link active">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    
                    <a href="backend/logout.php" class="sidebar-link logout-btn mt-5">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="settings-card">
                    <div class="settings-tabs">
                        <div class="settings-tab active" onclick="showTab('profile')">Profile</div>
                        <div class="settings-tab" onclick="showTab('security')">Security</div>
                        <div class="settings-tab" onclick="showTab('notifications')">Notifications</div>
                        <div class="settings-tab" onclick="showTab('activity')">Activity</div>
                    </div>
                    
                    <!-- Profile Tab -->
                    <div id="profile-tab" class="settings-content">
                        <h4 class="mb-4">Profile Information</h4>
                        <form action="backend/update_user_profile.php" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($admin_name); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin_email); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" value="">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                    
                    <!-- Security Tab -->
                    <div id="security-tab" class="settings-content" style="display: none;">
                        <h4 class="mb-4">Security Settings</h4>
                        <form action="backend/update_password.php" method="post">
                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Active Sessions</h5>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Current Session</h6>
                                        <p class="text-muted mb-0 small">
                                            <i class="fas fa-desktop me-1"></i> 
                                            <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-danger" onclick="if(confirm('Are you sure you want to log out from all devices?')) window.location.href='backend/logout_all.php'">
                            Log Out From All Devices
                        </button>
                    </div>
                    
                    <!-- Notifications Tab -->
                    <div id="notifications-tab" class="settings-content" style="display: none;">
                        <h4 class="mb-4">Notification Preferences</h4>
                        <form action="backend/update_user_settings.php" method="post">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" checked>
                                <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                <p class="text-muted small">Receive booking confirmations and updates via email</p>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications">
                                <label class="form-check-label" for="sms_notifications">SMS Notifications</label>
                                <p class="text-muted small">Receive booking confirmations and updates via SMS</p>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="marketing_emails" name="marketing_emails">
                                <label class="form-check-label" for="marketing_emails">Marketing Emails</label>
                                <p class="text-muted small">Receive promotional offers and newsletters</p>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                        </form>
                    </div>
                    
                    <!-- Activity Tab -->
                    <div id="activity-tab" class="settings-content" style="display: none;">
                        <h4 class="mb-4">Recent Activity</h4>
                        <?php
                        // Fetch user activity if available
                        $activity_query = "SELECT * FROM user_activity WHERE user_id = ? ORDER BY activity_time DESC LIMIT 10";
                        $stmt = $conn->prepare($activity_query);
                        
                        if ($stmt) {
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<div class="activity-item">';
                                    echo '<div class="d-flex justify-content-between">';
                                    echo '<div>' . htmlspecialchars($row['activity_description']) . '</div>';
                                    echo '<div class="activity-time">' . date('M d, Y h:i A', strtotime($row['activity_time'])) . '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>No recent activity found.</p>';
                            }
                            $stmt->close();
                        } else {
                            echo '<div class="alert alert-warning">Activity tracking is not available.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="booking_list.php" class="text-white-50">Booking</a></li>
                        <li><a href="about_us.php" class="text-white-50">About Us</a></li>
                        <li><a href="faq.php" class="text-white-50">FAQ</a></li>
                        <li><a href="contact_us.php" class="text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Account</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="text-white-50">Login</a></li>
                        <li><a href="register.php" class="text-white-50">Register</a></li>
                        <li><a href="#" class="text-white-50">Forgot Password</a></li>
                        <li><a href="user_bookings.php" class="text-white-50">My Bookings</a></li>
                        <li><a href="user_settings.php" class="text-white-50">Profile</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Privacy Policy</a></li>
                        <li><a href="#" class="text-white-50">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 (888) 760 1940</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> support@example.com</li>
                    </ul>
                    <div class="input-group mt-3">
                        <input type="email" class="form-control" placeholder="Enter You Email Here">
                        <button class="btn btn-primary" type="button"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <?php include('assets/includes/footer_link.php') ?>
    
    <script>
    function showTab(tabName) {
        // Hide all tabs
        document.getElementById('profile-tab').style.display = 'none';
        document.getElementById('security-tab').style.display = 'none';
        document.getElementById('notifications-tab').style.display = 'none';
        document.getElementById('activity-tab').style.display = 'none';
        
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.settings-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Show selected tab
        document.getElementById(tabName + '-tab').style.display = 'block';
        
        // Add active class to clicked tab
        event.currentTarget.classList.add('active');
    }
    </script>
</body>
</html>
