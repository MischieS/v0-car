<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if profile directory exists, if not create it
$profileDir = 'assets/img/profiles';
if (!file_exists($profileDir)) {
    mkdir($profileDir, 0755, true);
}

// Fallback image
$defaultImg = 'assets/img/profiles/default.png';
if (!file_exists($defaultImg)) {
    $defaultImg = 'assets/img/avatar.jpg'; // Fallback to a common avatar
}

$profileImg = !empty($user['user_profile_image']) && file_exists($profileDir . '/' . $user['user_profile_image'])
    ? $profileDir . '/' . $user['user_profile_image']
    : $defaultImg;

// Fetch stats
$activeStmt = $conn->prepare("SELECT COUNT(*) AS active_count FROM reservations WHERE user_id = ? AND status = 'confirmed' AND end_date >= CURDATE()");
$activeStmt->bind_param("i", $user_id);
$activeStmt->execute();
$active = $activeStmt->get_result()->fetch_assoc()['active_count'] ?? 0;

$spentStmt = $conn->prepare("SELECT SUM(total_price) AS total_spent FROM reservations WHERE user_id = ? AND status IN ('confirmed', 'completed')");
$spentStmt->bind_param("i", $user_id);
$spentStmt->execute();
$total_spent = $spentStmt->get_result()->fetch_assoc()['total_spent'] ?? 0;

$resStmt = $conn->prepare("
    SELECT r.*, c.car_type, c.car_image, l1.location_name AS pickup_name, l2.location_name AS return_name
    FROM reservations r
    JOIN cars c ON r.car_id = c.car_id
    JOIN locations l1 ON r.pickup_location = l1.location_id
    JOIN locations l2 ON r.return_location = l2.location_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 4
");
$resStmt->bind_param("i", $user_id);
$resStmt->execute();
$reservations = $resStmt->get_result();

// Get upcoming reservation
$upcomingStmt = $conn->prepare("
    SELECT r.*, c.car_type, c.car_image, l1.location_name AS pickup_name
    FROM reservations r
    JOIN cars c ON r.car_id = c.car_id
    JOIN locations l1 ON r.pickup_location = l1.location_id
    WHERE r.user_id = ? AND r.status = 'confirmed' AND r.start_date >= CURDATE()
    ORDER BY r.start_date ASC
    LIMIT 1
");
$upcomingStmt->bind_param("i", $user_id);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();
$upcomingReservation = $upcomingResult->num_rows > 0 ? $upcomingResult->fetch_assoc() : null;

// Get total reservations count
$totalResStmt = $conn->prepare("SELECT COUNT(*) AS total FROM reservations WHERE user_id = ?");
$totalResStmt->bind_param("i", $user_id);
$totalResStmt->execute();
$totalReservations = $totalResStmt->get_result()->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <?php include 'assets/includes/header_link.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4361ee;
            --card-border-radius: 16px;
            --btn-border-radius: 8px;
        }
        
        body {
            background-color: #f5f7fa;
        }
        
        .dashboard-container {
            padding: 2rem 0;
        }
        
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: var(--card-border-radius);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.15);
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }
        
        .welcome-card h2 {
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--card-border-radius);
            padding: 1.5rem;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .stat-card .icon.blue {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .stat-card .icon.purple {
            background-color: rgba(63, 55, 201, 0.1);
            color: var(--secondary-color);
        }
        
        .stat-card .icon.teal {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }
        
        .stat-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .upcoming-card {
            background: white;
            border-radius: var(--card-border-radius);
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border: none;
        }
        
        .upcoming-card .card-header {
            background-color: var(--light-color);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.5rem;
        }
        
        .upcoming-card .card-body {
            padding: 1.5rem;
        }
        
        .upcoming-card .countdown {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .countdown-item {
            text-align: center;
            margin: 0 0.5rem;
            min-width: 60px;
        }
        
        .countdown-item .number {
            font-size: 1.5rem;
            font-weight: 700;
            background-color: var(--light-color);
            border-radius: 8px;
            padding: 0.5rem;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .countdown-item .label {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .reservation-card {
            background: white;
            border-radius: var(--card-border-radius);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: none;
        }
        
        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .reservation-card .card-img-top {
            height: 160px;
            object-fit: cover;
        }
        
        .reservation-card .card-body {
            padding: 1.25rem;
        }
        
        .reservation-card .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.5rem 0.75rem;
            border-radius: 30px;
            font-weight: 500;
        }
        
        .reservation-card .badge.confirmed {
            background-color: rgba(76, 201, 240, 0.9);
        }
        
        .reservation-card .badge.completed {
            background-color: rgba(72, 149, 239, 0.9);
        }
        
        .reservation-card .badge.cancelled {
            background-color: rgba(247, 37, 133, 0.9);
        }
        
        .reservation-card .card-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .reservation-card .location {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .reservation-card .location i {
            margin-right: 0.5rem;
            color: #6c757d;
        }
        
        .reservation-card .dates {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #6c757d;
        }
        
        .reservation-card .dates i {
            margin-right: 0.5rem;
        }
        
        .btn-view {
            background-color: var(--light-color);
            color: var(--dark-color);
            border-radius: var(--btn-border-radius);
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar {
            background: white;
            border-radius: var(--card-border-radius);
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        
        .sidebar .profile {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .sidebar .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid var(--light-color);
        }
        
        .sidebar .profile h5 {
            margin-bottom: 0.25rem;
        }
        
        .sidebar .profile p {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-link.danger {
            color: var(--warning-color);
        }
        
        .sidebar .nav-link.danger:hover {
            background-color: rgba(247, 37, 133, 0.1);
        }
        
        @media (max-width: 991.98px) {
            .sidebar {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>

<?php include 'assets/includes/header.php'; ?>

<div class="dashboard-container">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar">
                    <div class="profile">
                        <img src="<?= $profileImg ?>" alt="Profile" class="shadow">
                        <h5><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                        <p class="small"><?= htmlspecialchars($user['user_email']) ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="user_dashboard.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user_bookings.php">
                                <i class="bi bi-car-front"></i> My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user_settings.php">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link danger" href="backend/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Welcome Card -->
                <div class="welcome-card mb-4">
                    <h2>Welcome back, <?= htmlspecialchars($user['first_name']) ?>! 👋</h2>
                    <p>Manage your car rentals and bookings from your personal dashboard.</p>
                </div>
                
                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="icon blue">
                                <i class="bi bi-car-front"></i>
                            </div>
                            <h3><?= $active ?></h3>
                            <p>Active Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="icon purple">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <h3>$<?= number_format($total_spent, 2) ?></h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="icon teal">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h3><?= $totalReservations ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Reservation -->
                <?php if ($upcomingReservation): ?>
                <div class="upcoming-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Reservation</h5>
                        <span class="badge bg-primary"><?= ucfirst($upcomingReservation['status']) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                <img src="assets/img/cars/<?= htmlspecialchars($upcomingReservation['car_image']) ?>" alt="Car" class="img-fluid rounded" style="max-height: 120px;">
                            </div>
                            <div class="col-md-8">
                                <h5><?= htmlspecialchars($upcomingReservation['car_type']) ?></h5>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                                    <span><?= htmlspecialchars($upcomingReservation['pickup_name']) ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-calendar3 me-2 text-primary"></i>
                                    <span><?= date('M d, Y', strtotime($upcomingReservation['start_date'])) ?> - <?= date('M d, Y', strtotime($upcomingReservation['end_date'])) ?></span>
                                </div>
                                
                                <?php
                                $today = new DateTime();
                                $pickupDate = new DateTime($upcomingReservation['start_date']);
                                $interval = $today->diff($pickupDate);
                                $daysLeft = $interval->days;
                                ?>
                                
                                <div class="countdown">
                                    <div class="countdown-item">
                                        <span class="number"><?= $daysLeft ?></span>
                                        <span class="label">Days</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="number"><?= $interval->h ?></span>
                                        <span class="label">Hours</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="number"><?= $interval->i ?></span>
                                        <span class="label">Minutes</span>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <a href="booking_detail.php?id=<?= $upcomingReservation['reservation_id'] ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Reservations -->
                <div class="mt-4">
                    <h4 class="mb-3">Recent Reservations</h4>
                    
                    <?php if ($reservations->num_rows > 0): ?>
                    <div class="row g-4">
                        <?php while ($res = $reservations->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="reservation-card">
                                <img src="assets/img/cars/<?= htmlspecialchars($res['car_image']) ?>" class="card-img-top" alt="Car">
                                <span class="badge <?= $res['status'] ?>"><?= ucfirst($res['status']) ?></span>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($res['car_type']) ?></h5>
                                    <div class="location">
                                        <i class="bi bi-geo-alt"></i>
                                        <span><?= htmlspecialchars($res['pickup_name']) ?> → <?= htmlspecialchars($res['return_name']) ?></span>
                                    </div>
                                    <div class="dates">
                                        <i class="bi bi-calendar3"></i>
                                        <span><?= date('M d', strtotime($res['start_date'])) ?> - <?= date('M d, Y', strtotime($res['end_date'])) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">$<?= number_format($res['total_price'], 2) ?></span>
                                        <a href="booking_detail.php?id=<?= $res['reservation_id'] ?>" class="btn btn-view">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="user_bookings.php" class="btn btn-outline-primary">View All Bookings</a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> No recent reservations found. Ready to book your first car?
                        <a href="booking_list.php" class="alert-link">Browse available cars</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('assets/includes/footer.php'); ?>
<?php include('assets/includes/footer_link.php'); ?>

</body>
</html>
