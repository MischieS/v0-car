<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get admin information
$admin_name = $_SESSION['user_name'] ?? 'Admin';
$admin_email = $_SESSION['user_email'] ?? 'admin@example.com';

// Fetch statistics
$active_bookings = 0;
$total_spent = 0;
$total_bookings = 0;

// Try to get active bookings count
$active_bookings = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'active'");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $active_bookings = $row['count'];
        }
    }
} catch (Exception $e) {
    // Silently handle the error
    $active_bookings = 0;
}

// Try to get total spent - using price instead of total_amount
$total_spent = 0;
try {
    // First check if the price column exists in the reservations table
    $check_column = $conn->query("SHOW COLUMNS FROM reservations LIKE 'price'");
    
    if ($check_column->num_rows > 0) {
        $stmt = $conn->prepare("SELECT SUM(price) as total FROM reservations WHERE status = 'completed'");
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $total_spent = $row['total'] ?? 0;
            }
        }
    } else {
        // If price column doesn't exist, try total_price
        $check_column = $conn->query("SHOW COLUMNS FROM reservations LIKE 'total_price'");
        if ($check_column->num_rows > 0) {
            $stmt = $conn->prepare("SELECT SUM(total_price) as total FROM reservations WHERE status = 'completed'");
            if ($stmt && $stmt->execute()) {
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $total_spent = $row['total'] ?? 0;
                }
            }
        }
    }
} catch (Exception $e) {
    // Silently handle the error - we'll just show $0.00
    $total_spent = 0;
}

// Try to get total bookings
$total_bookings = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations");
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $total_bookings = $row['count'];
        }
    }
} catch (Exception $e) {
    // Silently handle the error
    $total_bookings = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental</title>
    <?php include('assets/includes/header_link.php') ?>
    <style>
        body {
            background-color: #f5f7fb;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 700;
            color: #333;
        }
        .admin-dropdown {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 6px 12px;
            color: #333;
            background-color: white;
            transition: all 0.3s;
        }
        .admin-dropdown:hover {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            height: 100%;
        }
        .profile-section {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .profile-title {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .profile-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .profile-email {
            color: #666;
            font-size: 14px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #333;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background-color: #f5f7fb;
        }
        .nav-link.active {
            background-color: #e7f1ff;
            color: #3b7ddd;
            font-weight: 500;
        }
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .welcome-card {
            background-color: #4e73df;
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .welcome-subtitle {
            opacity: 0.9;
        }
        .stats-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: 100%;
        }
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .car-icon {
            background-color: #e7f1ff;
            color: #3b7ddd;
        }
        .money-icon {
            background-color: #fff8e7;
            color: #ffc107;
        }
        .check-icon {
            background-color: #e7fff8;
            color: #28a745;
        }
        .stats-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stats-label {
            color: #666;
            font-size: 14px;
        }
        .reservations-section {
            margin-top: 30px;
        }
        .reservations-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .info-alert {
            background-color: #e7f5ff;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        .info-alert i {
            color: #3b7ddd;
            margin-right: 10px;
        }
        .browse-link {
            color: #3b7ddd;
            text-decoration: none;
            font-weight: 500;
        }
        .browse-link:hover {
            text-decoration: underline;
        }
        .logout-link {
            color: #dc3545;
        }
        .logout-link:hover {
            background-color: #ffeeee;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light py-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">Car Rental</a>
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
                    <button class="admin-dropdown dropdown-toggle" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin User
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="admin_dashboard.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="admin_settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="backend/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar">
                    <div class="profile-section">
                        <div class="profile-title">Profile</div>
                        <h5 class="profile-name">Admin User</h5>
                        <div class="profile-email"><?php echo htmlspecialchars($admin_email); ?></div>
                    </div>
                    
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="admin_bookings.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i> My Bookings
                    </a>
                    <a href="admin_cars.php" class="nav-link">
                        <i class="fas fa-car"></i> Cars
                    </a>
                    <a href="admin_users.php" class="nav-link">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="admin_settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    
                    <a href="backend/logout.php" class="nav-link logout-link mt-5">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h1 class="welcome-title">Welcome back, Admin! 👋</h1>
                    <p class="welcome-subtitle mb-0">Manage your car rentals and bookings from your personal dashboard.</p>
                </div>
                
                <!-- Stats Row -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon car-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stats-value"><?php echo $active_bookings; ?></div>
                            <div class="stats-label">Active Bookings</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon money-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stats-value">$<?php echo number_format($total_spent, 2); ?></div>
                            <div class="stats-label">Total Spent</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon check-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stats-value"><?php echo $total_bookings; ?></div>
                            <div class="stats-label">Total Bookings</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reservations -->
                <div class="reservations-section">
                    <h2 class="reservations-title">Recent Reservations</h2>
                    
                    <?php
                    // Fetch recent bookings
                    $has_reservations = false;
                    try {
                        $stmt = $conn->prepare("
                            SELECT r.reservation_id, c.car_type, r.start_date, r.end_date, r.status
                            FROM reservations r
                            JOIN cars c ON r.car_id = c.car_id
                            ORDER BY r.reservation_id DESC
                            LIMIT 5
                        ");
                        
                        if ($stmt && $stmt->execute()) {
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $has_reservations = true;
                                echo '<div class="list-group">';
                                while ($row = $result->fetch_assoc()) {
                                    $status_class = '';
                                    switch ($row['status']) {
                                        case 'active': $status_class = 'bg-primary'; break;
                                        case 'completed': $status_class = 'bg-success'; break;
                                        case 'cancelled': $status_class = 'bg-danger'; break;
                                        default: $status_class = 'bg-secondary';
                                    }
                                    
                                    echo '<a href="booking_detail.php?id=' . $row['reservation_id'] . '" class="list-group-item list-group-item-action">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<h5 class="mb-1">' . htmlspecialchars($row['car_type']) . '</h5>';
                                    echo '<span class="badge ' . $status_class . '">' . ucfirst($row['status']) . '</span>';
                                    echo '</div>';
                                    echo '<p class="mb-1">From: ' . date('M d, Y', strtotime($row['start_date'])) . ' - To: ' . date('M d, Y', strtotime($row['end_date'])) . '</p>';
                                    echo '</a>';
                                }
                                echo '</div>';
                            }
                        }
                    } catch (Exception $e) {
                        // If there's an error, we'll just show no reservations
                        $has_reservations = false;
                    }
                    
                    if (!$has_reservations) {
                        echo '<div class="info-alert">';
                        echo '<i class="fas fa-info-circle"></i>';
                        echo '<div>No recent reservations found. Ready to book your first car? <a href="booking_list.php" class="browse-link">Browse available cars</a></div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('assets/includes/footer_link.php') ?>
</body>
</html>
