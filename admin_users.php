<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// 1) Fetch all users
$usersStmt = $conn->query("
  SELECT user_id, user_name, user_email, user_profile_image, user_role, phone_number
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
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="fas fa-plus-circle me-2"></i>Add New User
        </button>
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
        <th>Phone</th>
        <th>Role</th>
        <th class="text-center">Actions</th>
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
              <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                <span><?= strtoupper(substr($user['user_name'], 0, 1)) ?></span>
              </div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($user['user_name']) ?></td>
          <td><?= htmlspecialchars($user['user_email']) ?></td>
          <td><?= htmlspecialchars($user['phone_number'] ?: 'N/A') ?></td>
          <td>
            <?php if ($user['user_role'] === 'admin'): ?>
              <span class="badge bg-danger">Administrator</span>
            <?php else: ?>
              <span class="badge bg-info">Regular User</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <a href="admin_user_edit.php?user_id=<?= urlencode($user['user_id']) ?>" class="btn btn-sm btn-outline-primary me-1">
              <i class="fas fa-edit"></i> Edit
            </a>
            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
              <button onclick="confirmDelete(<?= $user['user_id'] ?>)" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash"></i> Delete
              </button>
            <?php else: ?>
              <button class="btn btn-sm btn-outline-secondary" disabled title="You cannot delete your own account">
                <i class="fas fa-trash"></i> Delete
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

    </div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="admin_user_action.php" method="post">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="user_name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="user_email" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone_number" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">User Role <span class="text-danger">*</span></label>
              <select name="user_role" class="form-select" required>
                <option value="user">Regular User</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label">Address</label>
              <input type="text" name="address" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Country</label>
              <input type="text" name="country" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Postal Code</label>
              <input type="text" name="pincode" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
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
