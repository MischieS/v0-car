<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>Rent a Car</title>
	<?php include('assets/includes/header_link.php') ?>
</head>

<body>

	<div class="main-wrapper">

		<!-- Header -->
		<?php include('assets/includes/header.php') ?>
		<!-- /Header -->

		<!-- Breadscrumb Section -->
		<div class="breadcrumb-bar">
			<div class="container">
				<div class="row align-items-center text-center">
					<div class="col-md-12 col-12">
						<h2 class="breadcrumb-title">Admin Dashboard</h2>
						<nav aria-label="breadcrumb" class="page-breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
							</ol>
						</nav>
					</div>
				</div>
			</div>
		</div>
		<!-- /Breadscrumb Section -->

		<!-- Dashboard Menu -->
		<div class="dashboard-section">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-lg-12">
						<div class="dashboard-menu">
							<ul class="nav justify-content-center">
								<li>
									<a href="admin_dashboard.php" class="active">
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
									<a href="user_settings.php">
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

		<!-- Page Content -->
		<div class="content dashboard-content">
			<div class="container">

				<!-- Content Header -->
				<div class="content-header">
					<h4>Dashboard</h4>
				</div>
				<!-- /Content Header -->

				<!-- Dashboard -->
				<div class="row">

				<?php
// Fetch last 5 bookings with status (and reservation_id)
$bkStmt = $conn->prepare("
  SELECT 
    r.reservation_id,
    u.user_name,
    u.phone_number,        
    c.car_type,
    r.pickup_location,
    r.start_date,
    r.end_date,
    r.status
  FROM reservations r
  JOIN cars  c ON r.car_id  = c.car_id
  JOIN users u ON r.user_id = u.user_id
  ORDER BY r.created_at DESC
  LIMIT 5
");


if (!$bkStmt) {
    die('Prepare failed: ' . $conn->error);
}

$bkStmt->execute();
$bkRes = $bkStmt->get_result();

function statusBadge($status) {
    return match($status) {
        'active'    => '<span class="badge bg-primary">Upcoming</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        default     => '<span class="badge bg-secondary">'.htmlspecialchars($status).'</span>',
    };
}
?>
<div class="col-12 px-4">
  <h3 class="text-center mb-4">Last 5 Bookings</h3>
  <div class="d-grid gap-3">
    <?php while($b = $bkRes->fetch_assoc()): ?>
      <div class="card shadow-sm p-4">
        <div class="card-body">
          <div class="row align-items-center">
            <!-- Left: user / car / phone -->
            <div class="col-2 text-start">
              <p class="mb-1"><strong><?= htmlspecialchars($b['user_name']) ?></strong></p>
              <p class="mb-1"><i class="fas fa-car-side me-1"></i><?= htmlspecialchars($b['car_type']) ?></p>
              <p class="mb-0"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($b['phone_number']) ?></p>
            </div>

            <!-- Center: location / start / end -->
            <div class="col-8 text-center">
              <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($b['pickup_location']) ?></p>
              <p class="mb-1"><i class="fas fa-calendar-alt me-2"></i><?= date('d M Y, h:i A', strtotime($b['start_date'])) ?></p>
              <p class="mb-0"><i class="fas fa-calendar-check me-2"></i><?= date('d M Y, h:i A', strtotime($b['end_date'])) ?></p>
            </div>

            <!-- Right: status over larger button -->
            <div class="col-2 text-end">
              <div class="mb-3"><?= statusBadge($b['status']) ?></div>
			  <a href="admin_booking_view.php?res_id=<?= urlencode($b['reservation_id']) ?>"
   class="btn btn-primary btn-lg px-4">
   View
</a>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>


<!-- /Last 5 Bookings -->


				</div>
				<!-- /Dashboard -->

			</div>
		</div>
		<!-- /Page Content -->

		<!-- Footer -->
		<?php include('assets/includes/footer.php') ?>
		<!-- /Footer -->

	</div>
	<?php include('assets/includes/footer_link.php') ?>
</body>

</html>
