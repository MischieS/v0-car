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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Admin Dashboard - Car Rental</title>
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
        .welcome-card {
            background: linear-gradient(135deg, #4070f4, #6a93ff);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .car-icon {
            background-color: #e7eeff;
            color: #4070f4;
        }
        .money-icon {
            background-color: #fff2e7;
            color: #ff9f43;
        }
        .booking-icon {
            background-color: #e7f9f7;
            color: #00cec9;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #777;
            font-size: 14px;
        }
        .info-alert {
            background-color: #e7f9ff;
            border-left: 4px solid #00b8d9;
            padding: 15px;
            border-radius: 5px;
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
                    
                    <a href="admin_dashboard.php" class="sidebar-link active">
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
                    <a href="admin_settings.php" class="sidebar-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    
                    <a href="backend/logout.php" class="sidebar-link logout-btn mt-5">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Welcome Card -->
                <div class="welcome-card mb-4">
                    <h2>Welcome back, Admin! 👋</h2>
                    <p class="mb-0">Manage your car rentals and bookings from your personal dashboard.</p>
                </div>
                
                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon car-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Active Bookings</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon money-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-value">$0.00</div>
                            <div class="stat-label">Total Spent</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon booking-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reservations -->
                <h4 class="mb-3">Recent Reservations</h4>
                <div class="info-alert">
                    <i class="fas fa-info-circle me-2"></i>
                    No recent reservations found. Ready to book your first car? 
                    <a href="booking_list.php" class="ms-2">Browse available cars</a>
                </div>
                
                <?php
                // Fetch recent bookings if needed
                $stmt = $conn->prepare("
                    SELECT r.reservation_id, c.car_type, r.start_date, r.end_date, r.status
                    FROM reservations r
                    JOIN cars c ON r.car_id = c.car_id
                    WHERE r.user_id = ?
                    ORDER BY r.created_at DESC
                    LIMIT 5
                ");
                
                if ($stmt) {
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
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
                    $stmt->close();
                }
                ?>
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
</body>
</html>
