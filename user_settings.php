<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$profileImg = $user['user_profile_image'] 
    ? 'assets/img/profiles/' . htmlspecialchars($user['user_profile_image']) 
    : 'assets/img/profiles/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Settings - Rent a Car</title>
  <?php include('assets/includes/header_link.php'); ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
  <style>
    #preview-image { max-width: 100%; max-height: 250px; border-radius: 5px; }
  </style>
</head>
<body>
<div class="main-wrapper">

  <?php include('assets/includes/header.php'); ?>

  <div class="breadcrumb-bar">
    <div class="container text-center">
      <h2 class="breadcrumb-title">Account Settings</h2>
    </div>
  </div>

  <div class="content py-5">
    <div class="container">
      <div class="col-lg-10 mx-auto">
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success text-center">Profile updated successfully!</div>
        <?php endif; ?>

        <form method="post" action="backend/update_user_profile.php">
          <!-- Profile -->
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
              <div class="mb-3 text-center">
                <img src="<?= $profileImg ?>" id="preview-image" class="rounded-circle mb-2" width="120" height="120" style="object-fit:cover">
                <div>
                  <input type="file" id="upload" accept="image/*" class="form-control mt-2">
                  <input type="hidden" name="cropped_profile" id="cropped_profile">
                </div>
              </div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">First Name</label>
                  <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Last Name</label>
                  <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Username</label>
                  <input type="text" name="user_name" class="form-control" value="<?= htmlspecialchars($user['user_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="user_email" class="form-control" value="<?= htmlspecialchars($user['user_email']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone Number</label>
                  <input type="text" name="user_phone" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>">
                </div>
              </div>
            </div>
          </div>

          <!-- Address -->
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0">Address Information</h5>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Address</label>
                  <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Country</label>
                  <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($user['country']) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">State</label>
                  <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($user['state']) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">City</label>
                  <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city']) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Zip Code</label>
                  <input type="text" name="zip_code" class="form-control" value="<?= htmlspecialchars($user['pincode']) ?>">
                </div>
              </div>
            </div>
          </div>

          <hr class="my-5">
<h5>Change Password</h5>
<form method="POST" action="backend/update_password.php" class="row g-3 mt-3">
  <div class="col-md-4">
    <label for="current_password" class="form-label">Current Password</label>
    <input type="password" name="current_password" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label for="new_password" class="form-label">New Password</label>
    <input type="password" name="new_password" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label for="confirm_password" class="form-label">Confirm New Password</label>
    <input type="password" name="confirm_password" class="form-control" required>
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-danger">Change Password</button>
  </div>
</form>


          <div class="text-end">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <?php include('assets/includes/footer.php'); ?>
</div>

<?php include('assets/includes/footer_link.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
let cropper;
const uploadInput = document.getElementById('upload');
uploadInput.addEventListener('change', e => {
  const file = e.target.files[0];
  if (!file || !file.type.startsWith('image/')) return;

  const reader = new FileReader();
  reader.onload = () => {
    const image = document.getElementById('preview-image');
    image.src = reader.result;

    if (cropper) cropper.destroy();
    cropper = new Cropper(image, {
      aspectRatio: 1,
      viewMode: 1,
      minCropBoxWidth: 100,
      minCropBoxHeight: 100,
      cropend() {
        const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
        document.getElementById('cropped_profile').value = canvas.toDataURL('image/jpeg');
      }
    });
  };
  reader.readAsDataURL(file);
});
</script>
</body>
</html>
