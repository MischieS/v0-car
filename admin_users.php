<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// 1) Fetch all users
$usersStmt = $conn->query("
  SELECT user_id, user_name, user_email, user_profile_image, user_role
  FROM users
  ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin - Manage Users</title>
  <?php include('assets/includes/header_link.php'); ?>
</head>
<body>
<div class="main-wrapper">
  <?php include('assets/includes/header.php'); ?>

    <!-- Breadscrumb Section -->
    <div class="breadcrumb-bar">
			<div class="container">
				<div class="row align-items-center text-center">
					<div class="col-md-12 col-12">
						<h2 class="breadcrumb-title">Users</h2>
						<nav aria-label="breadcrumb" class="page-breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Users</li>
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
									<a href="admin_bookings.php" >
										<img src="assets/img/icons/booking-icon.svg" alt="Icon">
										<span>Bookings</span>
									</a>
								</li>
								<li>
									<a href="admin_cars.php" >
										<img src="assets/img/icons/payment-icon.svg" alt="Icon">
										<span>Cars</span>
									</a>
								</li>
								<li>
									<a href="admin_users.php" class="active">
										<img src="assets/img/icons/payment-icon.svg" alt="Icon">
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


  <div class="content dashboard-content">
    <div class="container">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4">Users Management</h2>
      </div>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Profile</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($user = $usersStmt->fetch_assoc()): ?>
              <tr>
                <td>#<?= $user['user_id'] ?></td>
                <td>
                  <?php if ($user['user_profile_image']): ?>
                    <img src="<?= htmlspecialchars($user['user_profile_image']) ?>" alt="Profile" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                  <?php else: ?>
                    <span class="badge bg-secondary">No Image</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user['user_name']) ?></td>
                <td><?= htmlspecialchars($user['user_email']) ?></td>
                <td><?= htmlspecialchars($user['user_role']) ?></td>
                <td class="text-end">
                  <a href="admin_user_edit.php?user_id=<?= urlencode($user['user_id']) ?>" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                  <button onclick="confirmDelete(<?= $user['user_id'] ?>)" class="btn btn-sm btn-outline-danger">Delete</button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <?php include('assets/includes/footer.php'); ?>
</div>

<?php include('assets/includes/footer_link.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(userId) {
  Swal.fire({
    title: 'Delete this user?',
    text: "This action is irreversible!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Delete',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'admin_user_action.php?action=delete&user_id=' + userId;
    }
  })
}
</script>

</body>
</html>
