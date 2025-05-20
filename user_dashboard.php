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

// Fallback image
$profileImg = (!empty($user['profile_image']) && file_exists('assets/img/profiles/' . $user['profile_image']))
    ? 'assets/img/profiles/' . $user['profile_image']
    : 'assets/img/profiles/default.png';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <?php include 'assets/includes/header_link.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php include 'assets/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">

<!-- Sidebar -->
<aside class="col-md-3 col-lg-2 px-3">
    <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
        <div class="text-center mb-4">
            <img src="<?= $profileImg ?>" class="rounded-circle img-thumbnail mb-3" width="100" height="100" alt="Profile">
            <h5 class="mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link text-dark" href="user_dashboard.php">
                    <i class="bi bi-house me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-dark" href="user_bookings.php">
                    <i class="bi bi-car-front me-2"></i> Rented Cars
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-dark" href="user_settings.php">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</aside>




        <!-- Main Content -->
        <main class="col-md-9 col-lg-10 px-md-5 pt-4">
            <h2 class="mb-4">Welcome back, <?= htmlspecialchars($user['first_name']) ?> 👋</h2>

            <!-- Dashboard Stats -->
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0 rounded-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-clock-history me-2"></i> Active Bookings</h5>
                <p class="fs-4"><?= $active ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0 rounded-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cash-stack me-2"></i> Total Spent</h5>
                <p class="fs-4">$<?= number_format($total_spent, 2) ?></p>
            </div>
        </div>
    </div>
</div>


            <!-- Recent Reservations -->
<div class="pb-5">
    <h4 class="mb-3">Recent Reservations</h4>

    <?php if ($reservations->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($res = $reservations->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <img src="assets/img/cars/<?= htmlspecialchars($res['car_image']) ?>" class="card-img-top rounded-top" alt="Car Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($res['car_type']) ?></h5>
                            <p class="card-text small">
                                <strong>From:</strong> <?= htmlspecialchars($res['pickup_name']) ?><br>
                                <strong>To:</strong> <?= htmlspecialchars($res['return_name']) ?><br>
                                <strong>Dates:</strong> <?= $res['start_date'] ?> → <?= $res['end_date'] ?><br>
                                <strong>Status:</strong> <?= ucfirst($res['status']) ?>
                            </p>
                            <a href="booking_detail.php?id=<?= $res['reservation_id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No recent reservations found.</div>
    <?php endif; ?>
</div>

        </main>
    </div>
</div>

<?php include('assets/includes/footer.php'); ?>
<?php include('assets/includes/footer_link.php'); ?>

</body>
</html>
