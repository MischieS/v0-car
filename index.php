<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check if the database tables exist
$requiredTables = ['cars', 'locations', 'car_brands', 'car_models'];
$missingTables = [];

foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    // Redirect to setup if any required table is missing
    header('Location: backend/setup_database.php');
    exit;
}

// Get featured cars (limit to 6)
$featuredCars = [];
$result = $conn->query("SELECT * FROM cars ORDER BY car_id DESC LIMIT 6");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $featuredCars[] = $row;
    }
}

// Get locations for the search form
$locations = [];
$result = $conn->query("SELECT * FROM locations ORDER BY location_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
}

include 'assets/includes/header_link.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Car Rental System</title>
</head>
<body>
    <?php include 'assets/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="home-banner">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-9">
                        <div class="banner-content">
                            <h1>Find Your Perfect Rental Car</h1>
                            <p>Book the selected car effortlessly, Pay for driving only, Book the Car Now</p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-3">
                        <div class="banner-img">
                            <img src="assets/img/car-right.png" class="img-fluid" alt="banner">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="section-search">
        <div class="container">
            <div class="search-box">
                <div class="search-heading">
                    <h2>Find Your Car</h2>
                </div>
                <form action="booking_list.php" method="get">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="search-group">
                                <label>Pickup Location</label>
                                <select name="location_id" class="form-control">
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location['location_id'] ?>"><?= htmlspecialchars($location['location_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="search-group">
                                <label>Pickup Date</label>
                                <input type="date" name="pickup_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="search-group">
                                <label>Return Date</label>
                                <input type="date" name="return_date" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="search-btn">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Featured Cars Section -->
    <section class="section-features">
        <div class="container">
            <div class="section-heading">
                <h2>Featured Cars</h2>
                <p>Find your dream car for your next journey</p>
            </div>
            <div class="row">
                <?php foreach ($featuredCars as $car): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="car-item">
                        <div class="car-img">
                            <a href="listing-details.php?id=<?= $car['car_id'] ?>">
                                <?php if (!empty($car['car_image'])): ?>
                                    <img src="<?= htmlspecialchars($car['car_image']) ?>" class="img-fluid" alt="<?= htmlspecialchars($car['car_type']) ?>">
                                <?php else: ?>
                                    <img src="assets/img/cars/car-default.jpg" class="img-fluid" alt="<?= htmlspecialchars($car['car_type']) ?>">
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="car-content">
                            <h3 class="car-title">
                                <a href="listing-details.php?id=<?= $car['car_id'] ?>"><?= htmlspecialchars($car['car_type']) ?></a>
                            </h3>
                            <div class="car-info">
                                <div class="car-price">
                                    <h5>$<?= number_format($car['car_price_perday'], 2) ?> <span>/ Day</span></h5>
                                </div>
                                <div class="car-specs">
                                    <span><i class="fas fa-car"></i> <?= htmlspecialchars($car['year']) ?></span>
                                    <span><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($car['fuel_type']) ?></span>
                                    <span><i class="fas fa-cog"></i> <?= htmlspecialchars($car['transmission']) ?></span>
                                </div>
                            </div>
                            <div class="car-footer">
                                <a href="listing-details.php?id=<?= $car['car_id'] ?>" class="btn btn-outline-primary">View Details</a>
                                <a href="booking_checkout.php?car_id=<?= $car['car_id'] ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all text-center">
                <a href="booking_list.php" class="btn btn-primary">View All Cars</a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section-works">
        <div class="container">
            <div class="section-heading">
                <h2>How It Works</h2>
                <p>Rent a car in 3 simple steps</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-4">
                    <div class="work-item">
                        <div class="work-icon">
                            <span>1</span>
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Find Your Car</h4>
                        <p>Browse our wide selection of rental cars to find the perfect match for your needs.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="work-item">
                        <div class="work-icon">
                            <span>2</span>
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4>Book Your Car</h4>
                        <p>Choose your pickup and return dates and complete the booking process.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="work-item">
                        <div class="work-icon">
                            <span>3</span>
                            <i class="fas fa-car"></i>
                        </div>
                        <h4>Enjoy Your Ride</h4>
                        <p>Pick up your car at the designated location and enjoy your journey.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'assets/includes/footer.php'; ?>
    <?php include 'assets/includes/footer_link.php'; ?>
</body>
</html>
