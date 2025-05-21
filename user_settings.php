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

// Check if profile directory exists, if not create it
$profileDir = 'assets/img/profiles';
if (!file_exists($profileDir)) {
    mkdir($profileDir, 0755, true);
}

// Check if default image exists, if not use a placeholder
$defaultImg = 'assets/img/profiles/default.png';
if (!file_exists($defaultImg)) {
    $defaultImg = 'assets/img/avatar.jpg'; // Fallback to a common avatar
}

$profileImg = !empty($user['user_profile_image']) && file_exists($profileDir . '/' . $user['user_profile_image'])
    ? $profileDir . '/' . $user['user_profile_image']
    : $defaultImg;

// Check if user_activity table exists
$activityTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'user_activity'");
if ($result->num_rows > 0) {
    $activityTableExists = true;
    
    // Get user activity
    $activityStmt = $conn->prepare("
        SELECT * FROM user_activity 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $activityStmt->bind_param('i', $user_id);
    $activityStmt->execute();
    $activityResult = $activityStmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Account Settings - Rent a Car</title>
  <?php include('assets/includes/header_link.php'); ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
  <style>
    .settings-menu {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    .settings-menu .nav-link {
      color: #555;
      padding: 14px 20px;
      border-radius: 0;
      border-left: 4px solid transparent;
      transition: all 0.3s ease;
    }
    .settings-menu .nav-link:hover {
      background: rgba(0,0,0,0.02);
      color: #ff6e40;
    }
    .settings-menu .nav-link.active {
      background: rgba(255,110,64,0.08);
      color: #ff6e40;
      border-left: 4px solid #ff6e40;
      font-weight: 500;
    }
    .settings-menu .nav-link i {
      margin-right: 12px;
      width: 20px;
      text-align: center;
    }
    .settings-card {
      border: none;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      border-radius: 12px;
      margin-bottom: 25px;
      overflow: hidden;
    }
    .settings-card .card-header {
      background: #fff;
      border-bottom: 1px solid #f0f0f0;
      padding: 18px 24px;
    }
    .settings-card .card-body {
      padding: 24px;
    }
    .profile-upload {
      position: relative;
      width: 150px;
      margin: 0 auto;
    }
    .profile-upload .profile-img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    .profile-upload .upload-btn {
      position: absolute;
      right: 5px;
      bottom: 10px;
      background: #ff6e40;
      color: #fff;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 3px 8px rgba(255,110,64,0.4);
      transition: all 0.3s ease;
    }
    .profile-upload .upload-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 12px rgba(255,110,64,0.5);
    }
    .form-control {
      padding: 12px 15px;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      border-color: #ff6e40;
      box-shadow: 0 0 0 0.25rem rgba(255, 110, 64, 0.25);
    }
    .form-select {
      padding: 12px 15px;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      transition: all 0.3s ease;
    }
    .form-select:focus {
      border-color: #ff6e40;
      box-shadow: 0 0 0 0.25rem rgba(255, 110, 64, 0.25);
    }
    .form-label {
      font-weight: 500;
      margin-bottom: 8px;
      color: #444;
    }
    .password-strength {
      height: 6px;
      margin-top: 8px;
      border-radius: 6px;
      background: #e9ecef;
      overflow: hidden;
    }
    .password-strength-meter {
      height: 100%;
      border-radius: 6px;
      transition: width 0.3s ease;
    }
    .strength-weak { width: 25%; background-color: #dc3545; }
    .strength-fair { width: 50%; background-color: #ffc107; }
    .strength-good { width: 75%; background-color: #0dcaf0; }
    .strength-strong { width: 100%; background-color: #198754; }
    .activity-item {
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
      transition: all 0.2s ease;
    }
    .activity-item:hover {
      background-color: rgba(0,0,0,0.01);
    }
    .activity-item:last-child {
      border-bottom: none;
    }
    .activity-icon {
      width: 45px;
      height: 45px;
      border-radius: 12px;
      background: rgba(255,110,64,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
    }
    .activity-icon i {
      color: #ff6e40;
      font-size: 1.2rem;
    }
    .activity-content {
      flex: 1;
    }
    .activity-date {
      color: #6c757d;
      font-size: 12px;
    }
    .btn-primary {
      background-color: #ff6e40;
      border-color: #ff6e40;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background-color: #ff5722;
      border-color: #ff5722;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255,110,64,0.3);
    }
    .btn-outline-danger {
      border-color: #dc3545;
      color: #dc3545;
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .btn-outline-danger:hover {
      background-color: #dc3545;
      color: #fff;
      box-shadow: 0 5px 15px rgba(220,53,69,0.3);
    }
    .alert {
      border-radius: 10px;
      padding: 15px 20px;
      border: none;
      box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    .back-btn {
      display: inline-flex;
      align-items: center;
      color: #555;
      text-decoration: none;
      font-weight: 500;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    .back-btn:hover {
      color: #ff6e40;
      transform: translateX(-3px);
    }
    .back-btn i {
      margin-right: 8px;
    }
</style>
</head>
<body>
<div class="main-wrapper">

  <?php include('assets/includes/header.php'); ?>

  <div class="breadcrumb-bar">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-12 col-12">
          <h2 class="breadcrumb-title">Account Settings</h2>
          <nav aria-label="breadcrumb" class="page-breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Account Settings</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="content py-5">
    <div class="container">
    <a href="user_dashboard.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i> Profile updated successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['password_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i> Password changed successfully!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <div class="row">
        <!-- Settings Menu -->
        <div class="col-md-3 mb-4">
          <div class="settings-menu mb-4">
            <div class="p-4 text-center">
              <div class="profile-upload mb-3">
                <img src="<?= $profileImg ?>" alt="Profile" class="profile-img" id="preview-image">
                <label for="upload" class="upload-btn">
                  <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="upload" accept="image/*" class="d-none">
              </div>
              <h5 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
              <p class="text-muted mb-0"><?= htmlspecialchars($user['user_email']) ?></p>
            </div>
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
              <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab">
                <i class="fas fa-user"></i> Profile Information
              </button>
              <button class="nav-link" id="address-tab" data-bs-toggle="pill" data-bs-target="#address" type="button" role="tab">
                <i class="fas fa-map-pin"></i> Address Information
              </button>
              <button class="nav-link" id="password-tab" data-bs-toggle="pill" data-bs-target="#password" type="button" role="tab">
                <i class="fas fa-lock"></i> Change Password
              </button>
              <?php if ($activityTableExists): ?>
              <button class="nav-link" id="activity-tab" data-bs-toggle="pill" data-bs-target="#activity" type="button" role="tab">
                <i class="fas fa-history"></i> Account Activity
              </button>
              <?php endif; ?>
            </div>
          </div>
          
          
        </div>
        
        <!-- Settings Content -->
        <div class="col-md-9">
          <div class="tab-content" id="v-pills-tabContent">
            <!-- Profile Information -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
              <form method="post" action="backend/update_user_profile.php" enctype="multipart/form-data">
                <input type="hidden" name="cropped_profile" id="cropped_profile">
                <input type="hidden" name="update_profile" value="1">
                <div class="settings-card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Profile Information</h5>
                    <span class="badge bg-primary">Personal Details</span>
                  </div>
                  <div class="card-body">
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
                      <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="text-end mt-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                  </button>
                </div>
              </form>
            </div>
            
            <!-- Address Information -->
            <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
              <form method="post" action="backend/update_user_profile.php">
                <input type="hidden" name="update_address" value="1">
                <div class="settings-card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Address Information</h5>
                    <span class="badge bg-info">Location Details</span>
                  </div>
                  <div class="card-body">
                    <div class="row g-3">
                      <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select name="country" class="form-select">
                          <option value="">Select Country</option>
                          <option value="USA" <?= $user['country'] == 'USA' ? 'selected' : '' ?>>United States</option>
                          <option value="Canada" <?= $user['country'] == 'Canada' ? 'selected' : '' ?>>Canada</option>
                          <option value="UK" <?= $user['country'] == 'UK' ? 'selected' : '' ?>>United Kingdom</option>
                          <option value="Australia" <?= $user['country'] == 'Australia' ? 'selected' : '' ?>>Australia</option>
                          <option value="Germany" <?= $user['country'] == 'Germany' ? 'selected' : '' ?>>Germany</option>
                          <option value="France" <?= $user['country'] == 'France' ? 'selected' : '' ?>>France</option>
                          <option value="Spain" <?= $user['country'] == 'Spain' ? 'selected' : '' ?>>Spain</option>
                          <option value="Italy" <?= $user['country'] == 'Italy' ? 'selected' : '' ?>>Italy</option>
                        </select>
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
                
                <div class="text-end mt-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Address
                  </button>
                </div>
              </form>
            </div>
            
            <!-- Change Password -->
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
              <form method="POST" action="backend/update_password.php" id="password-form">
                <div class="settings-card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Change Password</h5>
                    <span class="badge bg-warning">Security</span>
                  </div>
                  <div class="card-body">
                    <div class="row g-3">
                      <div class="col-md-12">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                          <input type="password" name="current_password" id="current_password" class="form-control" required>
                          <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                            <i class="fas fa-eye"></i>
                          </button>
                        </div>
                      </div>
                      <div class="col-md-12">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                          <input type="password" name="new_password" id="new_password" class="form-control" required>
                          <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                            <i class="fas fa-eye"></i>
                          </button>
                        </div>
                        <div class="password-strength mt-2">
                          <div class="password-strength-meter"></div>
                        </div>
                        <small class="password-strength-text text-muted">Password strength: <span>Not entered</span></small>
                      </div>
                      <div class="col-md-12">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                          <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                          <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                            <i class="fas fa-eye"></i>
                          </button>
                        </div>
                        <div id="password-match" class="form-text"></div>
                      </div>
                      <div class="col-12">
                        <div class="alert alert-info">
                          <h6 class="mb-1"><i class="fas fa-info-circle me-1"></i> Password Requirements:</h6>
                          <ul class="mb-0 ps-3">
                            <li>At least 8 characters long</li>
                            <li>Include at least one uppercase letter</li>
                            <li>Include at least one number</li>
                            <li>Include at least one special character</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="text-end mt-3">
                  <button type="submit" class="btn btn-primary" id="change-password-btn">
                    <i class="fas fa-lock me-1"></i> Change Password
                  </button>
                </div>
              </form>
            </div>
            
            <!-- Account Activity -->
            <?php if ($activityTableExists): ?>
            <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
              <div class="settings-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Recent Activity</h5>
                  <span class="badge bg-secondary">Last 5 Activities</span>
                </div>
                <div class="card-body">
                  <?php if ($activityResult->num_rows > 0): ?>
                    <?php while ($activity = $activityResult->fetch_assoc()): ?>
                      <div class="activity-item d-flex align-items-center">
                        <div class="activity-icon">
                          <?php
                          $icon = 'history';
                          switch ($activity['activity_type']) {
                              case 'login': $icon = 'sign-in-alt'; break;
                              case 'profile_update': $icon = 'user-edit'; break;
                              case 'password_change': $icon = 'key'; break;
                              case 'booking': $icon = 'calendar-alt'; break;
                              case 'payment': $icon = 'credit-card'; break;
                          }
                          ?>
                          <i class="fas fa-<?= $icon ?>"></i>
                        </div>
                        <div class="activity-content">
                          <p class="mb-0"><?= htmlspecialchars($activity['activity_description']) ?></p>
                          <span class="activity-date">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                          </span>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <div class="text-center py-4">
                      <i class="fas fa-calendar-alt fs-1 text-muted"></i>
                      <p class="mt-2">No recent activity found</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="settings-card mt-4">
                <div class="card-header">
                  <h5 class="mb-0">Login Sessions</h5>
                </div>
                <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-desktop text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <h6 class="mb-0">Current Session</h6>
                          <small class="text-muted">
                            <?= $_SERVER['HTTP_USER_AGENT'] ?>
                          </small>
                        </div>
                        <span class="badge bg-success">Active</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="text-center mt-3">
                    <a href="backend/logout.php?all=1" class="btn btn-outline-danger btn-sm">
                      <i class="fas fa-sign-out-alt me-1"></i> Logout from all devices
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include('assets/includes/footer.php'); ?>
</div>

<!-- Modal for Image Cropping -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crop Profile Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="img-container">
          <img id="crop-image" src="#" class="img-fluid">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="crop-btn">Crop & Save</button>
      </div>
    </div>
  </div>
</div>

<?php include('assets/includes/footer_link.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
// Image cropping functionality
let cropper;
const uploadInput = document.getElementById('upload');
const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));

uploadInput.addEventListener('change', e => {
  const file = e.target.files[0];
  if (!file || !file.type.startsWith('image/')) return;

  const reader = new FileReader();
  reader.onload = () => {
    const image = document.getElementById('crop-image');
    image.src = reader.result;
    
    cropModal.show();
    
    setTimeout(() => {
      if (cropper) cropper.destroy();
      cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 1,
        minCropBoxWidth: 100,
        minCropBoxHeight: 100
      });
    }, 300);
  };
  reader.readAsDataURL(file);
});

document.getElementById('crop-btn').addEventListener('click', () => {
  try {
    const canvas = cropper.getCroppedCanvas({ 
      width: 300, 
      height: 300,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high'
    });
    
    if (!canvas) {
      alert('Error cropping image. Please try again with a different image.');
      return;
    }
    
    const croppedImage = canvas.toDataURL('image/jpeg', 0.9);
    
    document.getElementById('preview-image').src = croppedImage;
    document.getElementById('cropped_profile').value = croppedImage;
    
    cropModal.hide();
  } catch (e) {
    console.error('Error cropping image:', e);
    alert('Error processing image. Please try again.');
  }
});

// Password strength meter
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const passwordStrengthMeter = document.querySelector('.password-strength-meter');
const passwordStrengthText = document.querySelector('.password-strength-text span');
const passwordMatch = document.getElementById('password-match');

newPassword.addEventListener('input', function() {
  const password = this.value;
  let strength = 0;
  
  if (password.length >= 8) strength += 1;
  if (password.match(/[A-Z]/)) strength += 1;
  if (password.match(/[0-9]/)) strength += 1;
  if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
  
  passwordStrengthMeter.className = 'password-strength-meter';
  
  if (password.length === 0) {
    passwordStrengthMeter.style.width = '0';
    passwordStrengthText.textContent = 'Not entered';
  } else {
    switch (strength) {
      case 1:
        passwordStrengthMeter.classList.add('strength-weak');
        passwordStrengthText.textContent = 'Weak';
        break;
      case 2:
        passwordStrengthMeter.classList.add('strength-fair');
        passwordStrengthText.textContent = 'Fair';
        break;
      case 3:
        passwordStrengthMeter.classList.add('strength-good');
        passwordStrengthText.textContent = 'Good';
        break;
      case 4:
        passwordStrengthMeter.classList.add('strength-strong');
        passwordStrengthText.textContent = 'Strong';
        break;
    }
  }
});

// Password match validation
confirmPassword.addEventListener('input', function() {
  if (newPassword.value === this.value) {
    passwordMatch.textContent = 'Passwords match';
    passwordMatch.className = 'form-text text-success';
  } else {
    passwordMatch.textContent = 'Passwords do not match';
    passwordMatch.className = 'form-text text-danger';
  }
});

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
  button.addEventListener('click', function() {
    const targetId = this.getAttribute('data-target');
    const input = document.getElementById(targetId);
    const icon = this.querySelector('i');
    
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
});

// Form validation
document.getElementById('password-form').addEventListener('submit', function(e) {
  const newPass = newPassword.value;
  const confirmPass = confirmPassword.value;
  
  if (newPass !== confirmPass) {
    e.preventDefault();
    alert('Passwords do not match!');
    return false;
  }
  
  if (newPass.length < 8) {
    e.preventDefault();
    alert('Password must be at least 8 characters long!');
    return false;
  }
  
  // Check for password complexity
  let strength = 0;
  if (newPass.match(/[A-Z]/)) strength += 1;
  if (newPass.match(/[0-9]/)) strength += 1;
  if (newPass.match(/[^a-zA-Z0-9]/)) strength += 1;
  
  if (strength < 2) {
    e.preventDefault();
    alert('Password is too weak! Please include uppercase letters, numbers, and special characters.');
    return false;
  }
});

// Auto-dismiss alerts after 5 seconds
window.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
});
</script>
</body>
</html>
