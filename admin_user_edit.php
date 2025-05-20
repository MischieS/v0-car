<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

$user_id = (int)($_GET['user_id'] ?? 0);
if (!$user_id) {
    die('User ID is missing.');
}

// Fetch user
$stmt = $conn->prepare("SELECT user_name, user_profile_image FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if (!$user) {
    die('User not found.');
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['user_name'] ?? '');
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

    // Update
    $update = $conn->prepare("UPDATE users SET user_name = ?, user_profile_image = ? WHERE user_id = ?");
    $update->bind_param('ssi', $newName, $profileImagePath, $user_id);

    if ($update->execute()) {
        header('Location: admin_users.php?success=User updated successfully.');
        exit;
    } else {
        die('Update failed.');
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

  <div class="content dashboard-content">
    <div class="container">
      <h2 class="h4 mb-4">Edit User</h2>

      <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">

        <div class="mb-3 text-center">
          <?php if ($user['user_profile_image']): ?>
            <img src="<?= htmlspecialchars($user['user_profile_image']) ?>" class="rounded-circle mb-3" style="width:80px;height:80px;object-fit:cover;">
          <?php else: ?>
            <div class="badge bg-secondary mb-3 p-3">No Image</div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Change Profile Image</label>
          <input type="file" name="user_profile_image" class="form-control">
          <small class="text-muted">Leave empty to keep existing.</small>
        </div>

        <div class="d-flex justify-content-between">
          <a href="admin_users.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>

      </form>
    </div>
  </div>

  <?php include('assets/includes/footer.php'); ?>
</div>

<?php include('assets/includes/footer_link.php'); ?>

</body>
</html>
