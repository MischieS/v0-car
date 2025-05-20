<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Fetch all bookings
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
");
if (!$bkStmt) {
    die('Prepare failed: ' . $conn->error);
}
$bkStmt->execute();
$bkRes = $bkStmt->get_result();


// Helper function for badge
function statusBadge($status) {
    return match($status) {
        'active'    => '<span class="badge bg-primary">Active</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        default     => '<span class="badge bg-secondary">'.htmlspecialchars($status).'</span>',
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Bookings – Admin</title>
  <?php include('assets/includes/header_link.php'); ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<div class="main-wrapper">

  <?php include('assets/includes/header.php'); ?>


		<!-- Breadscrumb Section -->
		<div class="breadcrumb-bar">
			<div class="container">
				<div class="row align-items-center text-center">
					<div class="col-md-12 col-12">
						<h2 class="breadcrumb-title">Bookings</h2>
						<nav aria-label="breadcrumb" class="page-breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Bookings</li>
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
									<a href="admin_dashboard.php" >
										<img src="assets/img/icons/dashboard-icon.svg" alt="Icon">
										<span>Dashboard</span>
									</a>
								</li>
								<li>
									<a href="admin_bookings.php" class="active">
										<img src="assets/img/icons/booking-icon.svg" alt="Icon">
										<span>Bookings</span>
									</a>
								</li>
								<li>
									<a href="admin_cars.php">
										<img src="assets/img/icons/car-icon.svg" alt="Icon">
										<span>Cars</span>
									</a>
								</li>
								<li>
									<a href="admin_users.php">
										<img src="assets/img/icons/user-icon.svg" alt="Icon">
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

        <div class="content dashboard-content py-5">
    <div class="container">
      <h4 class="text-center mb-4">All Bookings</h4>

      <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-primary">
            <tr>
              <th>Reservation ID</th>
              <th>User</th>
              <th>Car</th>
              <th>Mobile</th>
              <th>Pickup Location</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($b = $bkRes->fetch_assoc()): ?>
            <tr>
              <td>#<?= htmlspecialchars($b['reservation_id']) ?></td>
              <td><?= htmlspecialchars($b['user_name']) ?></td>
              <td><?= htmlspecialchars($b['car_type']) ?></td>
              <td><?= htmlspecialchars($b['phone_number']) ?></td>
              <td><?= htmlspecialchars($b['pickup_location']) ?></td>
              <td><?= date('d M Y', strtotime($b['start_date'])) ?></td>
              <td><?= date('d M Y', strtotime($b['end_date'])) ?></td>
              <td><?= statusBadge($b['status']) ?></td>
              <td>
                <div class="d-flex justify-content-center gap-2">
                  <a href="admin_booking_view.php?res_id=<?= urlencode($b['reservation_id']) ?>" 
                     class="btn btn-outline-primary btn-sm rounded-pill">
                    View
                  </a>

                  <?php if ($b['status'] == 'active'): ?>
                    <button onclick="confirmAction('complete', <?= $b['reservation_id'] ?>)"
                            class="btn btn-outline-success btn-sm rounded-pill">
                      Complete
                    </button>
                    <button onclick="confirmAction('cancel', <?= $b['reservation_id'] ?>)"
                            class="btn btn-outline-danger btn-sm rounded-pill">
                      Cancel
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <?php include('assets/includes/footer.php') ?>
</div>

<?php include('assets/includes/footer_link.php') ?>

<script>
// SweetAlert2 confirmation for cancel or complete
function confirmAction(action, resId) {
  Swal.fire({
    title: 'Are you sure?',
    text: "You want to " + action + " this booking!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: action === 'cancel' ? '#dc3545' : '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, ' + action
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'admin_booking_action.php?action=' + action + '&res_id=' + resId;
    }
  });
}
</script>

</body>
</html>
