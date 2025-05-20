<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = (int)($_GET['user_id'] ?? 0);
if (!$user_id) {
    die('User ID is missing.');
}

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if (!$user) {
    die('User not found.');
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $user_role = trim($_POST['user_role'] ?? 'user');
    $profileImagePath = $user['user_profile_image'];

    // Handle file upload
    if (!empty($_FILES['user_profile_image']['name'])) {
        $tmp  = $_FILES['user_profile_image']['tmp_name'];
        $name = bin2hex(random_bytes(8)) . '.' . pathinfo($_FILES['user_profile_image']['name'], PATHINFO_EXTENSION);
        $dest = __DIR__ . '/uploads/' . $name;

        if (move_uploaded_file($tmp, $dest)) {
            $profileImagePath = 'uploads/' . $name;
        }
    }

    // Handle password change (if provided)
    $password_sql = '';
    $new_password = trim($_POST['new_password'] ?? '');
    if (!empty($new_password)) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $password_sql = ", password_hash = '$password_hash'";
    }

    // Update
    $update = $conn->prepare("
        UPDATE users SET 
            user_name = ?, 
            user_email = ?,
            first_name = ?,
            last_name = ?,
            phone_number = ?,
            address = ?,
            country = ?,
            city = ?,
            pincode = ?,
            user_role = ?,
            user_profile_image = ?
        WHERE user_id = ?
    ");
    
    $update->bind_param(
        'sssssssssssi', 
        $user_name, 
        $user_email,
        $first_name,
        $last_name,
        $phone_number,
        $address,
        $country,
        $city,
        $pincode,
        $user_role,
        $profileImagePath, 
        $user_id
    );

    if ($update->execute()) {
        // If password was changed, update it separately
        if (!empty($new_password)) {
            $pwd_update = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $pwd_update->bind_param('si', $password_hash, $user_id);
            $pwd_update->execute();
        }
        
        header('Location: admin_users.php?success=User updated successfully.');
        exit;
    } else {
        die('Update failed: ' . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit User</title>
  <?php include('assets/includes/header_link.php'); ?>
</head>
<body>

<div class="main-wrapper">
  <?php include('assets/includes/header.php'); ?>

  <!-- Breadcrumb -->
  <div class="breadcrumb-bar">
    <div class="container">
      <div class="row align-items-center text-center">
        <div class="col-md-12 col-12">
          <h2 class="breadcrumb-title">Edit User</h2>
          <nav aria-label="breadcrumb" class="page-breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item"><a href="admin_users.php">Users</a></li>
              <li class="breadcrumb-item active" aria-current="page">Edit User</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <!-- /Breadcrumb -->

  <div class="content dashboard-content">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 mx-auto">
          <form method="post" enctype="multipart/form-data" class="card shadow-sm">
            <div class="card-header bg-light">
              <h4 class="mb-0">Edit User: <?= htmlspecialchars($user['user_name']) ?></h4>
            </div>
            
            <div class="card-body">
              <div class="row">
                <!-- Profile Image Section -->
                <div class="col-md-3 text-center mb-4">
                  <div class="mb-3">
                    <?php if ($user['user_profile_image']): ?>
                      <img src="<?= htmlspecialchars($user['user_profile_image']) ?>" class="rounded-circle mb-3" style="width:150px;height:150px;object-fit:cover;">
                    <?php else: ?>
                      <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:150px;height:150px;margin:0 auto;">
                        <span style="font-size:50px;"><?= strtoupper(substr($user['user_name'], 0, 1)) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label d-block">Change Profile Image</label>
                    <input type="file" name="user_profile_image" class="form-control">
                    <small class="text-muted">Leave empty to keep existing.</small>
                  </div>
                </div>
                
                <!-- User Details Section -->
                <div class="col-md-9">
                  <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                      <label class="form-label">Username</label>
                      <input type="text" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Email Address</label>
                      <input type="email" name="user_email" value="<?= htmlspecialchars($user['user_email']) ?>" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">First Name</label>
                      <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Last Name</label>
                      <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Phone Number</label>
                      <input type="text" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" class="form-control">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">User Role</label>
                      <select name="user_role" class="form-select">
                        <option value="user" <?= $user['user_role'] === 'user' ? 'selected' : '' ?>>Regular User</option>
                        <option value="admin" <?= $user['user_role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                      </select>
                    </div>
                    
                    <!-- Address Information -->
                    <div class="col-md-12">
                      <label class="form-label">Address</label>
                      <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" class="form-control">
                    </div>
                    
                    <div class="col-md-4">
                      <label class="form-label">Country</label>
                      <input type="text" name="country" value="<?= htmlspecialchars($user['country']) ?>" class="form-control">
                    </div>
                    
                    <div class="col-md-4">
                      <label class="form-label">City</label>
                      <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>" class="form-control">
                    </div>
                    
                    <div class="col-md-4">
                      <label class="form-label">Postal Code</label>
                      <input type="text" name="pincode" value="<?= htmlspecialchars($user['pincode']) ?>" class="form-control">
                    </div>
                    
                    <!-- Password Change Section -->
                    <div class="col-12 mt-4">
                      <h5>Change Password</h5>
                      <hr>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">New Password</label>
                      <input type="password" name="new_password" class="form-control">
                      <small class="text-muted">Leave empty to keep current password.</small>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Confirm New Password</label>
                      <input type="password" name="confirm_password" class="form-control">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card-footer bg-light d-flex justify-content-between">
              <a href="admin_users.php" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include('assets/includes/footer.php'); ?>
</div>

<?php include('assets/includes/footer_link.php'); ?>

<script>
// Password confirmation validation
document.querySelector('form').addEventListener('submit', function(e) {
  const newPassword = document.querySelector('input[name="new_password"]').value;
  const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
  
  if (newPassword && newPassword !== confirmPassword) {
    e.preventDefault();
    alert('New password and confirmation do not match!');
  }
});
</script>

</body>
</html>
