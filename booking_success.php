<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// 1. Get reservation ID from URL
$res_id = (int)($_GET['res_id'] ?? 0);
if (!$res_id) {
    die('Reservation ID missing.');
}

// 2. Fetch reservation + car + user data
$stmt = $conn->prepare("
    SELECT 
        r.reservation_id,
        r.start_date,
        r.end_date,
        r.pickup_location,
        r.return_location,
        r.total_price,
        c.car_type,
        c.car_image,
        u.first_name,
        u.last_name,
        u.phone_number,
        u.user_email,
        u.address,
        u.country,
        u.city,
        u.pincode
    FROM reservations r
    INNER JOIN cars c ON r.car_id = c.car_id
    INNER JOIN users u ON r.user_id = u.user_id
    WHERE r.reservation_id = ?
");
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('i', $res_id);
$stmt->execute();
$res = $stmt->get_result();
$reservation = $res->fetch_assoc();
if (!$reservation) {
    die('Reservation not found.');
}

// 3. Assign variables
$orderNumber  = $reservation['reservation_id'];
$carType      = $reservation['car_type'];
$carImage     = $reservation['car_image'];
$pickupLoc    = $reservation['pickup_location'];
$returnLoc    = $reservation['return_location'];
$pickupDate   = $reservation['start_date'];
$returnDate   = $reservation['end_date'];

$driverName   = $reservation['first_name'] . ' ' . $reservation['last_name'];
$driverMobile = $reservation['phone_number'];
$billingEmail = $reservation['user_email'];
$billingPhone = $reservation['phone_number'];
$streetAddr   = $reservation['address'];
$totalPrice   = number_format($reservation['total_price'], 2);

// 4. Generate a fake transaction ID
$transactionID = 'TRX' . date('Ymd') . sprintf('%06d', $orderNumber);
?>




<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>Order Recieved - Rent a Car</title>
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
			    	    <h2 class="breadcrumb-title">Checkout</h2>
				    	<nav aria-label="breadcrumb" class="page-breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Checkout</li>
							</ol>
						</nav>							
					</div>
				</div>
			</div>
		</div>
		<!-- /Breadscrumb Section -->
        
        <!-- Booking Success -->
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
						<div class="col-xl-8 col-lg-9">
							<div class="booking-wizard-lists">
								<ul>
									<li class="active activated">
										<span><img src="assets/img/icons/booking-head-icon-01.svg" alt="Booking Icon"></span>
										<h6>Location & Time</h6>
									</li>
									<li class="active activated">
										<span><img src="assets/img/icons/booking-head-icon-02.svg" alt="Booking Icon"></span>
										<h6>Add-Ons</h6>
									</li>
									<li class="active activated">
										<span><img src="assets/img/icons/booking-head-icon-03.svg" alt="Booking Icon"></span>
										<h6>Detail</h6>
									</li>
									<li class="active activated">
										<span><img src="assets/img/icons/booking-head-icon-04.svg" alt="Booking Icon"></span>
										<h6>Checkout</h6>
									</li>
									<li class="active">
										<span><img src="assets/img/icons/booking-head-icon-05.svg" alt="Booking Icon"></span>
										<h6>Booking Confirmed</h6>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="booking-card">
    <div class="success-book">
        <span class="success-icon"><i class="fa-solid fa-check-double"></i></span>
        <h5>Thank you! Your Order has been Received</h5>
		<h5 class="order-no">Order Number : <span>#<?= htmlspecialchars($orderNumber) ?></span></h5>
		</div>

    <div class="booking-header">
        <div class="booking-img-wrap">
            <div class="book-img">
                <img src="assets/img/cars/<?= htmlspecialchars($carImage) ?>" alt="Car Image">
            </div>
            <div class="book-info">
                <h6><?= htmlspecialchars($carType) ?></h6>
                <p><i class="feather-map-pin"></i> Pickup Location: <?= htmlspecialchars($pickupLoc) ?></p>
            </div>
        </div>
        <div class="book-amount">
            <p>Total Amount</p>
            <h6>$<?= htmlspecialchars($totalPrice) ?></h6>
        </div>
    </div>

    <div class="row">
        
        <!-- Car Pricing -->
        <div class="col-lg-6 col-md-6 d-flex">
            <div class="book-card flex-fill">
                <div class="book-head">
                    <h6>Car Pricing</h6>
                </div>
                <div class="book-body">
                    <ul class="pricing-lists">
                        <li><p>Trip Protection Fee</p><span> + $20.00</span></li>
                        <li><p>Tax (20%)</p><span> + <?= number_format(($reservation['total_price'] - 20 - 500) * 0.20, 2) ?></span></li>
                        <li><p>Refundable Deposit</p><span> + $500.00</span></li>
                        <li class="total"><p>Subtotal</p><span>+$<?= $totalPrice ?></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Location & Time -->
        <div class="col-lg-6 col-md-6 d-flex">
            <div class="book-card flex-fill">
                <div class="book-head">
                    <h6>Location & Time</h6>
                </div>
                <div class="book-body">
                    <ul class="location-lists">
                        <li><h6>Pickup</h6><p><?= htmlspecialchars($pickupLoc) ?></p><p><?= htmlspecialchars($pickupDate) ?></p></li>
                        <li><h6>Return</h6><p><?= htmlspecialchars($returnLoc) ?></p><p><?= htmlspecialchars($returnDate) ?></p></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Driver Details -->
        <div class="col-lg-6 col-md-6 d-flex">
            <div class="book-card flex-fill">
                <div class="book-head">
                    <h6>Driver Details</h6>
                </div>
                <div class="book-body">
                    <ul class="location-lists">
                        <li><h6>Driver Type</h6><p>Self Driving</p></li>
                    </ul>
                    <div class="driver-info">
                        <span><img src="assets/img/user.jpg" alt="Driver"></span>
                        <div class="driver-name">
                            <h6><?= htmlspecialchars($driverName) ?></h6>
                            <ul>
                                <li>Mobile: <?= htmlspecialchars($driverMobile) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="col-lg-6 col-md-6 d-flex">
            <div class="book-card flex-fill">
                <div class="book-head">
                    <h6>Billing Information</h6>
                </div>
                <div class="book-body">
                    <ul class="billing-lists">
                        <li><?= htmlspecialchars($driverName) ?></li>
                        <?php if (!empty($company)): ?>
                        <li><?= htmlspecialchars($company) ?></li>
                        <?php endif; ?>
                        <li><?= htmlspecialchars($streetAddr) ?></li>
                        <li><?= htmlspecialchars($billingPhone) ?></li>
                        <li><?= htmlspecialchars($billingEmail) ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="col-lg-6 col-md-6 d-flex">
            <div class="book-card flex-fill">
                <div class="book-head">
                    <h6>Payment Details</h6>
                </div>
                <div class="book-body">
                    <ul class="location-lists">
                        <li><h6>Payment Mode</h6><p>Card Payment</p></li>
						<li><h6>Transaction ID</h6><p><span>#<?= htmlspecialchars($transactionID) ?></span></p></li>
						</ul>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="col-lg-12">
            <div class="book-card mb-0">
                <div class="book-head">
                    <h6>Additional Information</h6>
                </div>
                <div class="book-body">
                    <ul class="location-lists">
                        <li>
                            <p>Rental companies typically require customers to return the vehicle with a full tank of fuel. 
                            If not, refueling charges may apply at higher rates.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

			</div>
			
		</div>
		<!-- /Booking Success -->
	
	   <!-- Footer -->
	   <?php include('assets/includes/footer.php') ?>
	   <!-- /Footer -->
				
	</div>
	<?php include('assets/includes/footer_link.php') ?>
</body>
</html>
