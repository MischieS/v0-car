<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// If not logged in, redirect (optional)
if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit;
}

// Fetch locations
$locStmt = $conn->query("SELECT location_name FROM locations ORDER BY location_name");
$locationOptions = [];
while ($row = $locStmt->fetch_assoc()) {
	$locationOptions[] = $row['location_name'];
}

// Get car_id, pickup_date, return_date
$params      = array_merge($_GET, $_POST);
$car_id      = (int)($params['car_id'] ?? 0);
$pickup_date = $params['pickup_date'] ?? $params['start_date'] ?? null;
$return_date = $params['return_date'] ?? $params['end_date'] ?? null;
if (!$car_id || !$pickup_date || !$return_date) {
	die('Missing booking parameters.');
}

// Fetch car info
$stmt = $conn->prepare("
    SELECT 
      c.car_price_perday,
      c.car_type,
      c.car_image,
      l.location_name
    FROM cars c
    LEFT JOIN locations l ON c.location_id = l.location_id
    WHERE c.car_id = ?
");
$stmt->bind_param('i', $car_id);
$stmt->execute();
$res = $stmt->get_result();
if (! $car = $res->fetch_assoc()) {
	die('Car not found.');
}

// Pricing
$pickup = new DateTime($pickup_date);
$return = new DateTime($return_date);
$days   = max(1, $pickup->diff($return)->days);
$baseCharge    = $days * (float)$car['car_price_perday'];
$taxAmount     = $baseCharge * 0.20;
$protectionFee = 20.00;
$deposit       = 500.00;
$totalPrice    = $baseCharge + $taxAmount + $protectionFee + $deposit;

// Fetch user data (for auto-filling)
$userStmt = $conn->prepare("
	SELECT first_name, last_name, phone_number
	FROM users
	WHERE user_id = ?
");
$userStmt->bind_param('i', $_SESSION['user_id']);
$userStmt->execute();
$userRes = $userStmt->get_result();
$user = $userRes->fetch_assoc();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$pickupLoc  = trim($_POST['pickup_location'] ?? '');
	$returnLoc  = trim($_POST['return_location'] ?? '');
	$dfn        = trim($_POST['driver_first_name'] ?? '');
	$dln        = trim($_POST['driver_last_name'] ?? '');
	$rawMobile  = trim($_POST['driver_mobile'] ?? '');

	// Update user's info
	$up = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone_number=? WHERE user_id=?");
	$up->bind_param('sssi', $dfn, $dln, $rawMobile, $_SESSION['user_id']);
	if (! $up->execute()) {
		die('User update failed: ' . $up->error);
	}

	// Insert reservation
	$ins = $conn->prepare("
	  INSERT INTO reservations (
	    user_id, car_id,
	    start_date, end_date,
	    pickup_location, return_location,
	    total_price
	  ) VALUES (?, ?, ?, ?, ?, ?, ?)
	");
	if (! $ins) {
		die('Prepare failed: ' . $conn->error);
	}

	$ins->bind_param(
		'iissssd',
		$_SESSION['user_id'],
		$car_id,
		$pickup_date,
		$return_date,
		$pickupLoc,
		$returnLoc,
		$totalPrice
	);

	if (! $ins->execute()) {
		die('Execute failed: ' . $ins->error);
	}

	// Redirect to booking confirmation
	header(sprintf(
		'Location: booking_detail.php?car_id=%d&pickup_date=%s&return_date=%s&pickup_location=%s&return_location=%s&res_id=%d',
		$car_id,
		urlencode($pickup_date),
		urlencode($return_date),
		urlencode($pickupLoc),
		urlencode($returnLoc),
		$conn->insert_id
	));
	exit;
}
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

		<!-- Breadcrumb -->
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
		<!-- /Breadcrumb -->

		<!-- Booking Detail -->
		<div class="booking-new-module">
			<div class="container">
				<div class="booking-wizard-head">
					<div class="row align-items-center">
						<div class="col-xl-4 col-lg-3">
							<div class="booking-head-title">
								<h4>Reserve Your Car</h4>
								<p>Complete the following steps</p>
							</div>
						</div>
						<div class="col-xl-6 col-lg-9">
							<div class="booking-wizard-lists">
								<ul>
									<li class="active">
										<span><img src="assets/img/icons/booking-head-icon-01.svg" alt="Booking Icon"></span>
										<h6>Location & Time</h6>
									</li>
									<li>
										<span><img src="assets/img/icons/booking-head-icon-03.svg" alt="Booking Icon"></span>
										<h6>Detail</h6>
									</li>
									<li>
										<span><img src="assets/img/icons/booking-head-icon-04.svg" alt="Booking Icon"></span>
										<h6>Checkout</h6>
									</li>
									<li>
										<span><img src="assets/img/icons/booking-head-icon-05.svg" alt="Booking Icon"></span>
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
							<div class="booking-sidebar-card">
								<div class="booking-sidebar-head">
									<h5>Car Details</h5>
								</div>
								<div class="booking-sidebar-body">
									<div class="booking-car-detail">
										<span class="car-img">
											<img src="assets/img/cars/<?= htmlspecialchars($car['car_image']) ?>"
												class="img-fluid" alt="">
										</span>
										<div class="care-more-info">
											<h5><?= htmlspecialchars($car['car_type']) ?></h5>
											<p><?= htmlspecialchars($car['location_name']) ?></p>
										</div>
									</div>
									<div class="booking-vehicle-rates">
										<ul>
											<li>
												<h6>Rental (<?= $days ?> day<?= $days > 1 ? 's' : '' ?>)</h6>
												<h5>+ $<?= number_format($baseCharge, 2) ?></h5>
											</li>
											<li>
												<h6>Trip Protection Fee</h6>
												<h5>+ $<?= number_format($protectionFee, 2) ?></h5>
											</li>
											<li>
												<h6>Tax (20%)</h6>
												<h5>+ $<?= number_format($taxAmount, 2) ?></h5>
											</li>
											<li>
												<h6>Refundable Deposit</h6>
												<h5>+ $<?= number_format($deposit, 2) ?></h5>
											</li>
											<li class="total-rate">
												<h6>Subtotal</h6>
												<h5>+ $<?= number_format($totalPrice, 2) ?></h5>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="total-rate-card">
								<div class="vehicle-total-price">
									<h5>Estimated Total</h5>
									<span>$<?= number_format($totalPrice, 2) ?></span>
								</div>
							</div>
						</div>
					</div>
					<!-- /Sidebar -->

					<!-- Form -->
					<div class="col-lg-8">
						<div class="booking-information-main">
							<form id="checkoutForm" novalidate method="post" enctype="multipart/form-data">

								<!-- Location -->
								<div class="booking-information-card pickup-location mb-4">
									<div class="booking-info-head">
										<span><i class="bx bxs-car-garage"></i></span>
										<h5>Location</h5>
									</div>
									<div class="booking-info-body">
										<div class="form-custom mb-3">
											<label class="form-label">Pickup Location</label>
											<select name="pickup_location" id="pickup_location" class="form-control" required>
												<option value="">Select pickup</option>
												<?php foreach ($locationOptions as $loc): ?>
													<option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
												<?php endforeach; ?>
											</select>
										</div>

										<div class="form-custom mb-3">
											<label class="form-label">Return Location</label>
											<select name="return_location" id="return_location" class="form-control" required>
												<option value="">Select return</option>
												<?php foreach ($locationOptions as $loc): ?>
													<option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
								</div>



								<!-- Driver Details -->
								<div class="booking-information-card mb-4">
									<div class="booking-info-head">
										<span><i class="bx bx-user-pin"></i></span>
										<h5>Driver Details</h5>
									</div>
									<div class="booking-info-body">
										<div class="row g-3">
											<div class="col-md-6">
												<label class="form-label">First Name <span class="text-danger">*</span></label>
												<input type="text" name="driver_first_name" class="form-control" required value="<?= $user['first_name'] ?>">
											</div>
											<div class="col-md-6">
												<label class="form-label">Last Name <span class="text-danger">*</span></label>
												<input type="text" name="driver_last_name" class="form-control" required value="<?= $user['last_name'] ?>">
											</div>
											<div class="col-md-4">
												<label class="form-label">Driver Age <span class="text-danger">*</span></label>
												<input
													type="number"
													name="driver_age"
													class="form-control"
													required
													min="18"
													max="120"
													oninput="this.value=this.value.replace(/[^0-9]/g,'')"
													placeholder="e.g. 30">
											</div>
											<div class="col-md-8">
												<label class="form-label">Mobile Number <span class="text-danger">*</span></label>
												<input
													type="tel"
													name="driver_mobile"
													class="form-control"
													required
													value="<?= $user['phone_number'] ?>"
													placeholder="+90(533)4023922"
													pattern="^/+90/([0-9]{3}/)/d{7}$"
													title="Enter Turkish mobile as +90(533)4023922">
											</div>

											<script>
												document.addEventListener('DOMContentLoaded', () => {
													const phone = document.querySelector('input[name="driver_mobile"]');

													phone.addEventListener('focus', () => {
														if (!phone.value.startsWith('+90')) {
															phone.value = '+90';
														}
													});

													phone.addEventListener('input', () => {
														let v = phone.value.replace(/[^+/d]/g, '');

														// Keep only one '+' at front
														v = v.startsWith('+') ?
															'+' + v.slice(1).replace(//+/g, '') :
															'+' + v.replace(//+/g, '');

														// Force '+90'
														if (!v.startsWith('+90')) v = '+90';

														// Grab up to 10 digits after '90'
														let rest = v.slice(3).replace(//D/g, '').slice(0, 10);

														// Build final: +90(AAA)BBBBBBB
														let out = '+90';
														if (rest.length > 0) {
															const area = rest.slice(0, 3);
															out += '(' + area + (area.length === 3 ? ')' : '');
															if (rest.length > 3) out += rest.slice(3);
														}
														phone.value = out;
													});
												});
											</script>

											<div class="col-12">
												<div class="form-check">
													<input class="form-check-input" type="checkbox" name="age_confirmed" id="age_confirmed" value="1" required>
													<label class="form-check-label" for="age_confirmed">
														I confirm driver’s age is above 20 years old
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Hidden booking params -->
								<input type="hidden" name="car_id" value="<?= $car_id ?>">
								<input type="hidden" name="pickup_date" value="<?= htmlspecialchars($pickup_date) ?>">
								<input type="hidden" name="return_date" value="<?= htmlspecialchars($return_date) ?>">

								<div class="booking-info-btns d-flex justify-content-end">
									<a
										href="listing-details.php?car_id=<?= $car_id ?>"
										class="btn btn-secondary me-2">
										Back
									</a>
									<button type="submit" class="btn btn-primary"
										href="booking_detail.php?car_id=<?= $car_id ?>
      &pickup_date=<?= urlencode($pickup_date) ?>
      &return_date=<?= urlencode($return_date) ?>">
										Continue Booking
									</button>
								</div>

							</form>
							<script>
								// Wait until everything’s parsed
								window.addEventListener('load', () => {
									const form = document.getElementById('checkoutForm');
									if (!form) return;

									form.addEventListener('submit', function(e) {
										// Let the browser check validity
										if (!form.checkValidity()) {
											e.preventDefault(); // stop actual submit
											form.reportValidity(); // show tooltips
											return false;
										}
										// if valid, form will submit normally
									});
								});
							</script>

						</div>
					</div>
					<!-- /Form -->
				</div>
			</div>
		</div>
		<!-- /Booking Detail -->

		<!-- Footer -->
		<?php include 'assets/includes/footer.php'; ?>
		<!-- /Footer -->
	</div>
	<?php include 'assets/includes/footer_link.php'; ?>
</body>

</html>