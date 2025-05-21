<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'backend/db_connect.php';

// Debug session (can be removed in production)
// echo "<!-- Session debug: " . (isset($_SESSION['user_id']) ? "User ID: " . $_SESSION['user_id'] : "No user logged in") . " -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dreams Rent - Premium Car Rental Service</title>
    <?php include('assets/includes/header_link.php') ?>
    <style>
        /* Minimal modern styles with improved colors */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --secondary: #f59e0b;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --radius: 8px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background-color: #fff;
        }
        
        .section {
            padding: 60px 0;
        }
        
        .section-title {
            margin-bottom: 40px;
            text-align: center;
        }
        
        .section-title h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
        }
        
        .section-title p {
            font-size: 1rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Hero section */
        .hero-section {
            background-color: var(--light);
            padding: 80px 0 60px;
            position: relative;
        }
        
        .hero-content h1 {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .hero-content h1 span {
            color: var(--primary);
        }
        
        .hero-content p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: var(--gray);
            max-width: 90%;
        }
        
        .hero-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Search box */
        .search-container {
            background-color: #fff;
            border-radius: var(--radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: 40px;
        }
        
        .search-container h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            height: 48px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 8px 16px;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        
        .input-icon-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .input-with-icon {
            padding-left: 45px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            height: 48px;
            border-radius: var(--radius);
            font-weight: 600;
            padding: 0 24px;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Featured cars */
        .car-card {
            background-color: #fff;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .car-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        
        .car-image {
            height: 200px;
            overflow: hidden;
        }
        
        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .car-card:hover .car-image img {
            transform: scale(1.05);
        }
        
        .car-details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .car-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .car-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .car-meta-item {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .car-meta-item i {
            margin-right: 5px;
            color: var(--primary);
            font-size: 0.875rem;
            display: inline-block;
            width: 16px;
            text-align: center;
            vertical-align: middle;
        }
        
        .car-price {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }
        
        .price-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .price-period {
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .btn-book {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-book:hover {
            background-color: var(--primary-dark);
            color: white;
            text-decoration: none;
        }
        
        /* How it works */
        .process-section {
            background-color: var(--light);
        }
        
        .process-card {
            text-align: center;
            padding: 30px 20px;
            background-color: #fff;
            border-radius: var(--radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: 100%;
            position: relative;
        }
        
        .process-number {
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 auto 20px;
        }
        
        .process-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .process-description {
            font-size: 0.95rem;
            color: var(--gray);
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .hero-content h1 {
                font-size: 2.25rem;
            }
            
            .hero-image {
                margin-top: 30px;
            }
        }
        
        @media (max-width: 767px) {
            .section {
                padding: 40px 0;
            }
            
            .hero-section {
                padding: 40px 0;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .search-container {
                padding: 20px;
            }
            
            .process-card {
                margin-bottom: 20px;
            }
        }
        
        /* Header and navigation improvements */
        .header-navbar-rht .nav-link {
            background-color: var(--primary);
            color: white;
            border-radius: var(--radius);
            padding: 8px 16px;
            margin-left: 10px;
            transition: all 0.2s;
        }
        
        .header-navbar-rht .nav-link:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .header-navbar-rht .header-login {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .header-navbar-rht .header-login:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .dropdown-menu {
            border-radius: var(--radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
            padding: 10px 0;
        }
        
        .dropdown-item {
            padding: 8px 20px;
            color: var(--dark);
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: var(--light);
            color: var(--primary);
        }
        
        .dropdown-item i {
            margin-right: 8px;
            color: var(--primary);
            width: 16px;
            text-align: center;
        }
        
        /* No cars message */
        .no-cars-message {
            text-align: center;
            padding: 40px 20px;
            background-color: var(--light);
            border-radius: var(--radius);
            margin-bottom: 30px;
        }
        
        .no-cars-message h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .no-cars-message p {
            color: var(--gray);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Header -->
        <?php include('assets/includes/header.php') ?>
        <!-- /Header -->

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h1>Find and book your <span>perfect car</span></h1>
                            <p>Experience premium car rentals with our extensive fleet of vehicles. Book in seconds and hit the road with confidence.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="assets/img/car-hero-minimal.png" alt="Car Rental">
                        </div>
                    </div>
                </div>
                
                <!-- Search Box -->
                <div class="search-container">
                    <h3>Search for available cars</h3>
                    <form id="searchForm" action="booking_list.php" method="GET">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label for="location" class="form-label">Pickup Location</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-map-marker-alt input-icon"></i>
                                        <input type="text" id="location" name="location" class="form-control input-with-icon" placeholder="City, Airport, or Address">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group">
                                    <label for="pickup_date" class="form-label">Pickup Date</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-calendar input-icon"></i>
                                        <input type="text" id="pickup_date" name="pickup_date" class="form-control input-with-icon datepicker" placeholder="Select date" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group">
                                    <label for="return_date" class="form-label">Return Date</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-calendar-check input-icon"></i>
                                        <input type="text" id="return_date" name="return_date" class="form-control input-with-icon datepicker" placeholder="Select date" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn-primary w-100">
                                        <i class="fas fa-search me-2"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!-- /Hero Section -->
        
        <!-- Featured Cars Section -->
        <section class="section">
            <div class="container">
                <div class="section-title">
                    <h2>Featured Cars</h2>
                    <p>Discover our selection of premium vehicles for your next adventure</p>
                </div>
                
                <div class="row">
                    <?php
                    // Get featured cars from database
                    // First, check if the 'featured' column exists in the cars table
                    $checkColumn = $conn->query("SHOW COLUMNS FROM cars LIKE 'featured'");
                    
                    if ($checkColumn && $checkColumn->num_rows > 0) {
                        // 'featured' column exists, use it
                        $sql = "SELECT * FROM cars WHERE featured = 1 LIMIT 6";
                    } else {
                        // 'featured' column doesn't exist, get the most recent cars instead
                        $sql = "SELECT * FROM cars ORDER BY car_id DESC LIMIT 6";
                    }
                    
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($car = $result->fetch_assoc()) {
                            // Get car name - handle different database structures
                            $carName = '';
                            if (isset($car['car_type']) && !empty($car['car_type'])) {
                                $carName = $car['car_type'];
                            } elseif (isset($car['brand']) || isset($car['model'])) {
                                $brand = isset($car['brand']) ? $car['brand'] : '';
                                $model = isset($car['model']) ? $car['model'] : '';
                                $carName = trim($brand . ' ' . $model);
                            } else {
                                $carName = 'Car #' . ($car['car_id'] ?? $car['id'] ?? 'Unknown');
                            }
                            
                            // Get car image
                            $carImage = 'assets/img/cars/default-car.jpg';
                            if (isset($car['image']) && !empty($car['image'])) {
                                $carImage = $car['image'];
                            } elseif (isset($car['car_image']) && !empty($car['car_image'])) {
                                $carImage = $car['car_image'];
                            }
                            
                            // Get car ID for the link
                            $carId = isset($car['car_id']) ? $car['car_id'] : (isset($car['id']) ? $car['id'] : 0);
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="car-card">
                            <div class="car-image">
                                <img src="<?php echo htmlspecialchars($carImage); ?>" alt="<?php echo htmlspecialchars($carName); ?>">
                            </div>
                            <div class="car-details">
                                <h3 class="car-title"><?php echo htmlspecialchars($carName); ?></h3>
                                <div class="car-meta">
                                    <div class="car-meta-item">
                                        <i class="fas fa-car"></i> <?php echo htmlspecialchars($car['type'] ?? 'Sedan'); ?>
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission'] ?? 'Automatic'); ?>
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($car['fuel_type'] ?? 'Petrol'); ?>
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div>
                                        <span class="price-value">$<?php echo htmlspecialchars($car['price_per_day'] ?? $car['car_price_perday'] ?? '50'); ?></span>
                                        <span class="price-period">/ day</span>
                                    </div>
                                    <a href="listing-details.php?id=<?php echo $carId; ?>" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                        // No cars found message
                    ?>
                    <div class="col-12">
                        <div class="no-cars-message">
                            <h3>No Featured Cars Available</h3>
                            <p>We're currently updating our inventory. Please check back soon or browse all available cars.</p>
                            <a href="booking_list.php" class="btn-primary">View All Cars</a>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="booking_list.php" class="btn-primary">View All Cars</a>
                </div>
            </div>
        </section>
        <!-- /Featured Cars Section -->
        
        <!-- How It Works Section -->
        <section class="section process-section">
            <div class="container">
                <div class="section-title">
                    <h2>How It Works</h2>
                    <p>Renting a car with us is quick and easy</p>
                </div>
                
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <div class="process-card">
                            <div class="process-number">1</div>
                            <h3 class="process-title">Choose Your Car</h3>
                            <p class="process-description">Browse our selection and find the perfect vehicle for your needs.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <div class="process-card">
                            <div class="process-number">2</div>
                            <h3 class="process-title">Book & Pay</h3>
                            <p class="process-description">Secure your reservation with our easy payment system.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                        <div class="process-card">
                            <div class="process-number">3</div>
                            <h3 class="process-title">Pick Up Car</h3>
                            <p class="process-description">Collect your vehicle with minimal paperwork and no waiting.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="process-card">
                            <div class="process-number">4</div>
                            <h3 class="process-title">Enjoy Your Trip</h3>
                            <p class="process-description">Hit the road with confidence and 24/7 support if needed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /How It Works Section -->
        
        <!-- Footer -->
        <?php include('assets/includes/footer.php') ?>
        <!-- /Footer -->
    </div>
    
    <?php include('assets/includes/footer_link.php') ?>
    
    <script>
        $(document).ready(function() {
            // Initialize datepickers with proper formatting and constraints
            $('.datepicker').datepicker({
                format: 'mm/dd/yyyy',
                autoclose: true,
                startDate: new Date(),
                todayHighlight: true
            });
            
            // Set minimum return date based on pickup date
            $('#pickup_date').on('changeDate', function() {
                var pickupDate = new Date($('#pickup_date').val());
                var nextDay = new Date(pickupDate);
                nextDay.setDate(pickupDate.getDate() + 1);
                
                $('#return_date').datepicker('setStartDate', nextDay);
                
                // If return date is before pickup date, update it
                var returnDate = new Date($('#return_date').val());
                if (returnDate <= pickupDate) {
                    $('#return_date').datepicker('update', nextDay);
                }
            });
            
            // Form validation before submission
            $('#searchForm').on('submit', function(e) {
                var pickupDate = $('#pickup_date').val();
                var returnDate = $('#return_date').val();
                
                if (!pickupDate || !returnDate) {
                    e.preventDefault();
                    alert('Please select both pickup and return dates.');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>
