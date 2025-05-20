<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';

// Filter logic
$filterSQL = "";
if ($status_filter === 'active') {
    $filterSQL = "AND r.status = 'confirmed' AND r.end_date >= CURDATE()";
} elseif ($status_filter === 'ended') {
    $filterSQL = "AND (r.status = 'completed' OR r.end_date < CURDATE())";
} elseif ($status_filter === 'cancelled') {
    $filterSQL = "AND r.status = 'cancelled'";
}

// Fetch reservations
$stmt = $conn->prepare("
    SELECT r.*, c.car_type, c.car_image, 
           l1.location_name AS pickup_name, 
           l2.location_name AS return_name
    FROM reservations r
    JOIN cars c ON r.car_id = c.car_id
    JOIN locations l1 ON r.pickup_location = l1.location_id
    JOIN locations l2 ON r.return_location = l2.location_id
    WHERE r.user_id = ?
    $filterSQL
    ORDER BY r.start_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();

// Fetch user info
$userStmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$userStmt->bind_param('i', $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$profileImg = (!empty($user['profile_image']) && file_exists('assets/img/profiles/' . $user['profile_image']))
    ? 'assets/img/profiles/' . $user['profile_image']
    : 'assets/img/profiles/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rented Cars</title>
    <?php include 'assets/includes/header_link.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php include 'assets/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">

        <!-- Left Sidebar: Navigation -->
        <aside class="col-md-3 col-lg-2 px-3">
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                <div class="text-center mb-3">
                    <span class="badge rounded-pill bg-light text-dark mb-2">Profile</span>
                    <img src="<?= $profileImg ?>" class="rounded-circle img-thumbnail mb-3" width="100" height="100" alt="Profile">
                    <h5 class="mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center text-dark text-decoration-none" href="user_dashboard.php">
                            <i class="bi bi-house me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center text-warning fw-bold text-decoration-none" href="user_bookings.php">
                            <i class="bi bi-car-front me-2"></i> Rented Cars
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center text-dark text-decoration-none" href="user_settings.php">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link d-flex align-items-center text-danger text-decoration-none" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="col-md-7 col-lg-8 px-md-5 pt-4">
            <h2 class="mb-4">Welcome back, <?= htmlspecialchars($user['first_name']) ?> 👋</h2>

            <?php if ($reservations->num_rows > 0): ?>
                <div class="row">
                    <?php while ($res = $reservations->fetch_assoc()): ?>
                        <?php
                            $today = date('Y-m-d');
                            $statusText = ucfirst($res['status']);

                            if ($res['status'] === 'confirmed' && $res['end_date'] >= $today) {
                                $statusText = "Ends on " . $res['end_date'];
                            } elseif ($res['status'] === 'confirmed' && $res['end_date'] < $today) {
                                $statusText = "Ended on " . $res['end_date'];
                            } elseif ($res['status'] === 'completed') {
                                $statusText = "Completed on " . $res['end_date'];
                            }
                        ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3">
                                        <img src="assets/img/cars/<?= htmlspecialchars($res['car_image']) ?>" class="img-fluid rounded-3" alt="Car Image">
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="mb-1"><?= htmlspecialchars($res['car_type']) ?></h5>
                                        <p class="mb-1 small text-muted"><?= htmlspecialchars($res['pickup_name']) ?> → <?= htmlspecialchars($res['return_name']) ?></p>
                                        <p class="mb-1 small"><strong>Rental:</strong> <?= $res['start_date'] ?> → <?= $res['end_date'] ?></p>
                                        <p class="mb-1 small"><strong>Status:</strong> <?= $statusText ?></p>
                                    </div>
                                    <div class="col-md-3 text-md-end">
                                        <p class="fs-5 fw-semibold mb-2 text-primary">$<?= number_format($res['total_price'], 2) ?></p>
                                        <a href="booking_detail.php?id=<?= $res['reservation_id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No reservations found for this filter.</div>
            <?php endif; ?>
        </main>

        <!-- Right Sidebar: Filter -->
        <aside class="col-md-2 px-3">
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                <h6 class="mb-3 fw-bold">Filter by Status</h6>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= $status_filter === 'all' ? 'text-warning fw-bold' : 'text-dark' ?> text-decoration-none" href="user_bookings.php">All</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= $status_filter === 'active' ? 'text-warning fw-bold' : 'text-dark' ?> text-decoration-none" href="user_bookings.php?status=active">Active</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= $status_filter === 'ended' ? 'text-warning fw-bold' : 'text-dark' ?> text-decoration-none" href="user_bookings.php?status=ended">Ended</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= $status_filter === 'cancelled' ? 'text-warning fw-bold' : 'text-dark' ?> text-decoration-none" href="user_bookings.php?status=cancelled">Cancelled</a>
                    </li>
                </ul>
            </div>
        </aside>

    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
<?php include 'assets/includes/footer_link.php'; ?>
</body>
</html>
