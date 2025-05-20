<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// 1. Get reservation ID
$res_id = (int)($_GET['res_id'] ?? 0);
if (!$res_id) die('Reservation ID is missing.');

// 2. Fetch reservation + user + car
$stmt = $conn->prepare("
  SELECT 
    r.*,
    u.first_name, u.last_name, u.phone_number, u.user_email, u.address, u.country, u.city, u.pincode,
    c.car_type, c.car_image, c.car_price_perday
  FROM reservations r
  INNER JOIN users u ON r.user_id = u.user_id
  INNER JOIN cars c   ON r.car_id = c.car_id
  WHERE r.reservation_id = ?
");
if (!$stmt) die('Prepare failed: ' . $conn->error);
$stmt->bind_param('i', $res_id);
$stmt->execute();
$res = $stmt->get_result();
$reservation = $res->fetch_assoc();
$car = [
  'car_type' => $reservation['car_type'] ?? '',
  'car_image' => $reservation['car_image'] ?? '',
];

$pickupLoc  = $reservation['pickup_location'] ?? '';
$returnLoc  = $reservation['return_location'] ?? '';
$pickupDate = $reservation['start_date'] ?? '';
$returnDate = $reservation['end_date'] ?? '';
if (!$reservation) die('Reservation not found.');

// 3. Handle POST (update user info)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['driver_first_name']);
    $last_name  = trim($_POST['driver_last_name']);
    $phone      = trim($_POST['phone']);
    $email      = trim($_POST['email']);
    $address    = trim($_POST['address']);
    $country    = trim($_POST['country']);
    $city       = trim($_POST['city']);
    $pincode    = trim($_POST['pincode']);

    // Optional: Add validation here

    $user_id = $reservation['user_id'];

    $update = $conn->prepare("
      UPDATE users
      SET 
        first_name = ?, last_name = ?, phone_number = ?, user_email = ?, 
        address = ?, country = ?, city = ?, pincode = ?
      WHERE user_id = ?
    ");
    if (!$update) die('Prepare failed: ' . $conn->error);
    $update->bind_param('ssssssssi', 
      $first_name, $last_name, $phone, $email, 
      $address, $country, $city, $pincode, $user_id
    );
    if (!$update->execute()) {
      die('Execute failed: ' . $update->error);
    }

    header('Location: booking_payment.php?res_id=' . urlencode($res_id));
    exit;
}

// 4. Calculate prices
$pickup = new DateTime($reservation['start_date']);
$return = new DateTime($reservation['end_date']);
$days = max(1, $pickup->diff($return)->days);

$baseCharge    = $days * (float)$reservation['car_price_perday'];
$protectionFee = 20.00;
$taxAmount     = $baseCharge * 0.20;
$deposit       = 500.00;
$totalPrice    = $baseCharge + $taxAmount + $protectionFee + $deposit;
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checkout – Rent a Car</title>
  <?php include 'assets/includes/header_link.php'; ?>
</head>
<body>
  <div class="main-wrapper">

    <!-- Header -->
    <?php include 'assets/includes/header.php'; ?>
    <!-- /Header -->

    <!-- Breadcrumb & Wizard -->
    <div class="breadcrumb-bar">
      <div class="container">
        <div class="row align-items-center text-center">
          <div class="col-12">
            <h2 class="breadcrumb-title">Checkout</h2>
            <nav aria-label="breadcrumb" class="page-breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="booking-new-module py-4">
      <div class="container">
        <div class="booking-wizard-head mb-4">
          <div class="row align-items-center">
            <div class="col-lg-3">
              <div class="booking-head-title">
                <h4>Reserve Your Car</h4>
                <p>Complete the following steps</p>
              </div>
            </div>
            <div class="col-xl-6 col-lg-9">
              <div class="booking-wizard-lists">
                <ul class="list-unstyled d-flex mb-0">
                  <li class="active activated me-4 text-center">
                    <span><img src="assets/img/icons/booking-head-icon-01.svg" alt="Icon"></span>
                    <h6>Location & Time</h6>
                  </li>
                  <li class="active me-4 text-center">
                    <span><img src="assets/img/icons/booking-head-icon-03.svg" alt="Icon"></span>
                    <h6>Detail</h6>
                  </li>
                  <li class="me-4 text-center">
                    <span><img src="assets/img/icons/booking-head-icon-04.svg" alt="Icon"></span>
                    <h6>Checkout</h6>
                  </li>
                  <li class="text-center">
                    <span><img src="assets/img/icons/booking-head-icon-05.svg" alt="Icon"></span>
                    <h6>Booking Confirmed</h6>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
		<div class="row">
  <!-- Sidebar -->
  <div class="col-lg-4 theiaStickySidebar">
    <div class="booking-sidebar">

      <!-- Car Details Card -->
      <div class="booking-sidebar-card mb-4">
        <div class="booking-sidebar-head">
          <h5>Car Details</h5>
        </div>
        <div class="booking-sidebar-body">
          <div class="booking-car-detail d-flex mb-3">
            <img src="assets/img/cars/<?= htmlspecialchars($car['car_image']) ?>"
                 class="img-fluid me-3" style="width:80px" alt="">
            <div>
              <h6><?= htmlspecialchars($car['car_type']) ?></h6>
            </div>
          </div>
          <ul class="booking-vehicle-rates list-unstyled mb-0">
            <li class="d-flex justify-content-between">
              <span>Rental (<?= $days ?> day<?= $days>1?'s':'' ?>)</span>
              <span>$<?= number_format($baseCharge,2) ?></span>
            </li>
            <li class="d-flex justify-content-between">
              <span>Trip Protection Fee</span>
              <span>$<?= number_format($protectionFee,2) ?></span>
            </li>
            <li class="d-flex justify-content-between">
              <span>Tax</span>
              <span>$<?= number_format($taxAmount,2) ?></span>
            </li>
            <li class="d-flex justify-content-between">
              <span>Refundable Deposit</span>
              <span>$<?= number_format($deposit,2) ?></span>
            </li>
            <li class="d-flex justify-content-between fw-bold">
              <span>Subtotal</span>
              <span>$<?= number_format($totalPrice,2) ?></span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Delivery & Pickup Card -->
      <div class="booking-sidebar-card mb-4">
        <div class="booking-sidebar-head d-flex justify-content-between align-items-center">
          <h5>Delivery & Pickup</h5>
        </div>
        <div class="booking-sidebar-body">
          <ul class="location-address-info list-unstyled mb-0">
            <li class="mb-3">
              <h6>Delivery Location</h6>
              <p><?= htmlspecialchars($returnLoc) ?> — <?= htmlspecialchars($pickupDate) ?></p>            </li>
            <li>
              <h6>Pickup Location</h6>
              <p><?= htmlspecialchars($returnLoc) ?> — <?= htmlspecialchars($returnDate) ?></p>            </li>
          </ul>
        </div>
      </div>

      <!-- Estimated Total -->
      <div class="total-rate-card">
        <div class="vehicle-total-price d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Estimated Total</h5>
          <span class="h5 text-primary mb-0">$<?= number_format($totalPrice,2) ?></span>
        </div>
      </div>

    </div>
  </div>
  <!-- /Sidebar -->
  
  <!-- Form column begins here… -->
  <!-- Form Column -->
  <div class="col-lg-8">
    <div class="booking-information-main">
    <form method="post" enctype="multipart/form-data" id="billingForm">
  <div class="booking-information-card mb-4">
    <div class="booking-info-head">
      <span><i class="bx bx-add-to-queue"></i></span>
      <h5>Billing Info</h5>
    </div>
    <div class="booking-info-body">
      <div class="row g-3">
        
        <div class="col-md-6">
          <label class="form-label">First Name <span class="text-danger">*</span></label>
          <input type="text" name="driver_first_name" class="form-control" required 
          value="<?= htmlspecialchars($reservation['first_name'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">Last Name <span class="text-danger">*</span></label>
          <input type="text" name="driver_last_name" class="form-control" required 
          value="<?= htmlspecialchars($reservation['last_name'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">Company</label>
          <input type="text" name="company" class="form-control" placeholder="Optional">
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Street Address <span class="text-danger">*</span></label>
          <input type="text" name="address" class="form-control" required 
          value="<?= htmlspecialchars($reservation['address'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">Country <span class="text-danger">*</span></label>
          <input type="text" name="country" class="form-control" required 
          value="<?= htmlspecialchars($reservation['country'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">City <span class="text-danger">*</span></label>
          <input type="text" name="city" class="form-control" required 
          value="<?= htmlspecialchars($reservation['city'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">Pincode <span class="text-danger">*</span></label>
          <input type="text" name="pincode" class="form-control" required 
          value="<?= htmlspecialchars($reservation['pincode'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required 
 value="<?= htmlspecialchars($reservation['email'] ?? '') ?>">        </div>
        
        <div class="col-md-6">
          <label class="form-label">Phone Number <span class="text-danger">*</span></label>
          <input type="tel" name="phone" class="form-control" required
 pattern="^/+?[0-9/s/-/(/)]{7,20}$"
 title="Enter a valid phone number like +90(533)4023922"
 value="<?= htmlspecialchars($reservation['phone_number'] ?? '') ?>">
        </div>
        
        <div class="col-12">
          <label class="form-label">Additional Information</label>
          <textarea name="additional_info" class="form-control" rows="4" placeholder="Optional"></textarea>
        </div>
        
        <div class="col-12">
          <label class="form-label">Driving Licence Number <span class="text-danger">*</span></label>
          <input type="text" name="driving_licence_no" class="form-control" required
            pattern="^[A-Za-z0-9]{5,20}$"
            title="Enter a valid licence number (letters and numbers only)">
        </div>
        
        <div class="col-12">
          <label class="form-label">Upload Documents * (jpg/png/pdf)</label>
          <input type="file" name="uploaded_documents[]" class="form-control" multiple required>
          <p class="img-size-info">Max 4MB each.</p>
        </div>
        
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="terms_accepted" id="terms_accepted" required>
            <label class="form-check-label" for="terms_accepted">
              I have read and accept Terms &amp; Conditions <span class="text-danger">*</span>
            </label>
          </div>
        </div>
        
      </div>
    </div>
  </div>

  <div class="booking-info-btns d-flex justify-content-end">
    <a href="booking_checkout.php?car_id=<?= urlencode($car_id) ?>&pickup_date=<?= urlencode($pickup_date) ?>&return_date=<?= urlencode($return_date) ?>&pickup_location=<?= urlencode($pickupLoc) ?>&return_location=<?= urlencode($returnLoc) ?>" 
       class="btn btn-secondary me-2">
      ← Back to Checkout
    </a>
    <button type="submit" class="btn btn-primary">Confirm &amp; Pay Now</button>
  </div>
</form>

<!-- Auto-validation JS -->
<script>
  window.addEventListener('load', () => {
    const form = document.getElementById('billingForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        form.reportValidity();
      }
    });
  });
</script>

    </div>
  </div>
  <!-- /Form Column -->
</div> <!-- end .row -->
