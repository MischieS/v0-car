<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dreams Rent - Premium Car Rental Service</title>
    <?php include('assets/includes/header_link.php') ?>
    <style>
        /* Minimal modern styles */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f59e0b;
            --light: #f9fafb;
            --dark: #1f2937;
            --gray: #6b7280;
            --border: #e5e7eb;
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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
        
        /* CTA section */
        .cta-section {
            background-color: var(--primary);
            color: white;
            text-align: center;
            padding: 60px 0;
        }
        
        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .cta-description {
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
        }
        
        .btn-cta {
            background-color: white;
            color: var(--primary);
            border: none;
            height: 48px;
            border-radius: var(--radius);
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-cta:hover {
            background-color: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: var(--primary);
            text-decoration: none;
        }
        
        .btn-cta i {
            margin-left: 8px;
        }
        
        /* Filter section */
        .filter-section {
            background-color: #fff;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            color: var(--dark);
        }
        
        .range-slider {
            height: 5px;
            position: relative;
            background-color: #e1e1e1;
            border-radius: 5px;
        }
        
        .range-selected {
            height: 100%;
            left: 30%;
            right: 30%;
            position: absolute;
            border-radius: 5px;
            background-color: var(--primary);
        }
        
        .range-input {
            position: relative;
            height: 5px;
        }
        
        .range-input input {
            position: absolute;
            width: 100%;
            height: 5px;
            top: -5px;
            background: none;
            pointer-events: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .range-input input::-webkit-slider-thumb {
            height: 17px;
            width: 17px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            background-color: #fff;
            pointer-events: auto;
            -webkit-appearance: none;
            cursor: pointer;
        }
        
        .range-input input::-moz-range-thumb {
            height: 17px;
            width: 17px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            background-color: #fff;
            pointer-events: auto;
            -moz-appearance: none;
            cursor: pointer;
        }
        
        .price-input {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
        
        .price-input .field {
            display: flex;
            width: 100%;
            height: 36px;
            align-items: center;
        }
        
        .field input {
            width: 100%;
            height: 100%;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 0 10px;
            font-size: 0.9rem;
        }
        
        .price-input .separator {
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .checkbox-group {
            margin-bottom: 5px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.95rem;
            color: var(--gray);
        }
        
        .checkbox-label input {
            margin-right: 10px;
        }
        
        .filter-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn-filter {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-filter:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-reset {
            background-color: transparent;
            color: var(--gray);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-reset:hover {
            background-color: var(--light);
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
                        
                        <!-- Advanced Filters (Initially Hidden) -->
                        <div id="advancedFilters" style="display: none;">
                            <hr>
                            <div class="row">
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Car Type</label>
                                        <select name="car_type" class="form-control">
                                            <option value="">All Types</option>
                                            <option value="sedan">Sedan</option>
                                            <option value="suv">SUV</option>
                                            <option value="luxury">Luxury</option>
                                            <option value="sports">Sports</option>
                                            <option value="convertible">Convertible</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Brand</label>
                                        <select name="brand" class="form-control">
                                            <option value="">All Brands</option>
                                            <?php
                                            require_once 'backend/db_connect.php';
                                            $sql = "SELECT DISTINCT brand FROM cars ORDER BY brand";
                                            $result = $conn->query($sql);
                                            if ($result && $result->num_rows > 0) {
                                                while($row = $result->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['brand']) . "'>" . htmlspecialchars($row['brand']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Transmission</label>
                                        <select name="transmission" class="form-control">
                                            <option value="">Any</option>
                                            <option value="automatic">Automatic</option>
                                            <option value="manual">Manual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Price Range (per day)</label>
                                        <div class="price-input">
                                            <div class="field">
                                                <input type="number" name="min_price" class="input-min" value="50">
                                            </div>
                                            <div class="separator">to</div>
                                            <div class="field">
                                                <input type="number" name="max_price" class="input-max" value="500">
                                            </div>
                                        </div>
                                        <div class="range-slider mt-2">
                                            <div class="range-selected"></div>
                                        </div>
                                        <div class="range-input">
                                            <input type="range" class="range-min" min="0" max="1000" value="50" step="10">
                                            <input type="range" class="range-max" min="0" max="1000" value="500" step="10">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Features</label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="features[]" value="bluetooth"> Bluetooth
                                            </label>
                                        </div>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="features[]" value="navigation"> Navigation
                                            </label>
                                        </div>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="features[]" value="sunroof"> Sunroof
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Fuel Type</label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="fuel[]" value="petrol"> Petrol
                                            </label>
                                        </div>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="fuel[]" value="diesel"> Diesel
                                            </label>
                                        </div>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="fuel[]" value="hybrid"> Hybrid
                                            </label>
                                        </div>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="fuel[]" value="electric"> Electric
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Passengers</label>
                                        <select name="passengers" class="form-control">
                                            <option value="">Any</option>
                                            <option value="2">2 or more</option>
                                            <option value="4">4 or more</option>
                                            <option value="5">5 or more</option>
                                            <option value="7">7 or more</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Sort By</label>
                                        <select name="sort" class="form-control">
                                            <option value="price_asc">Price: Low to High</option>
                                            <option value="price_desc">Price: High to Low</option>
                                            <option value="rating_desc">Highest Rated</option>
                                            <option value="newest">Newest Models</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="filter-actions">
                                <button type="button" class="btn-reset" id="resetFilters">Reset Filters</button>
                                <button type="submit" class="btn-filter">Apply Filters</button>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="#" id="toggleFilters" class="text-primary">
                                <span id="showFiltersText">Show Advanced Filters</span>
                                <span id="hideFiltersText" style="display: none;">Hide Advanced Filters</span>
                                <i class="fas fa-chevron-down ms-1" id="filterIcon"></i>
                            </a>
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
                    require_once 'backend/db_connect.php';
                    
                    // Get featured cars from database
                    $sql = "SELECT * FROM cars WHERE featured = 1 LIMIT 6";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($car = $result->fetch_assoc()) {
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="car-card">
                            <div class="car-image">
                                <img src="<?php echo htmlspecialchars($car['image'] ?? 'assets/img/cars/default-car.jpg'); ?>" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>">
                            </div>
                            <div class="car-details">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
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
                                        <span class="price-value">$<?php echo htmlspecialchars($car['price_per_day']); ?></span>
                                        <span class="price-period">/ day</span>
                                    </div>
                                    <a href="listing-details.php?id=<?php echo $car['id']; ?>" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                        // Fallback if no cars in database
                    ?>
                    <!-- Fallback Car 1 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="car-card">
                            <div class="car-image">
                                <img src="assets/img/cars/car-01.jpg" alt="Mercedes-Benz E-Class">
                            </div>
                            <div class="car-details">
                                <h3 class="car-title">Mercedes-Benz E-Class</h3>
                                <div class="car-meta">
                                    <div class="car-meta-item">
                                        <i class="fas fa-car"></i> Sedan
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-cog"></i> Automatic
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-gas-pump"></i> Hybrid
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div>
                                        <span class="price-value">$89</span>
                                        <span class="price-period">/ day</span>
                                    </div>
                                    <a href="listing-details.php?id=1" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fallback Car 2 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="car-card">
                            <div class="car-image">
                                <img src="assets/img/cars/car-02.jpg" alt="BMW 5 Series">
                            </div>
                            <div class="car-details">
                                <h3 class="car-title">BMW 5 Series</h3>
                                <div class="car-meta">
                                    <div class="car-meta-item">
                                        <i class="fas fa-car"></i> Sedan
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-cog"></i> Automatic
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-gas-pump"></i> Petrol
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div>
                                        <span class="price-value">$95</span>
                                        <span class="price-period">/ day</span>
                                    </div>
                                    <a href="listing-details.php?id=2" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fallback Car 3 -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="car-card">
                            <div class="car-image">
                                <img src="assets/img/cars/car-03.jpg" alt="Audi Q7">
                            </div>
                            <div class="car-details">
                                <h3 class="car-title">Audi Q7</h3>
                                <div class="car-meta">
                                    <div class="car-meta-item">
                                        <i class="fas fa-car"></i> SUV
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-cog"></i> Automatic
                                    </div>
                                    <div class="car-meta-item">
                                        <i class="fas fa-gas-pump"></i> Diesel
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div>
                                        <span class="price-value">$120</span>
                                        <span class="price-period">/ day</span>
                                    </div>
                                    <a href="listing-details.php?id=3" class="btn-book">Book Now</a>
                                </div>
                            </div>
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
        
        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <h2 class="cta-title">Ready to hit the road?</h2>
                <p class="cta-description">Book your dream car today and enjoy the freedom of the open road.</p>
                <a href="booking_list.php" class="btn-cta">Book Now <i class="fas fa-arrow-right"></i></a>
            </div>
        </section>
        <!-- /CTA Section -->
        
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
            
            // Toggle advanced filters
            $('#toggleFilters').on('click', function(e) {
                e.preventDefault();
                $('#advancedFilters').slideToggle(300);
                $('#showFiltersText, #hideFiltersText').toggle();
                $('#filterIcon').toggleClass('fa-chevron-down fa-chevron-up');
            });
            
            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#searchForm')[0].reset();
                
                // Reset range sliders
                $('.range-min').val(50);
                $('.range-max').val(500);
                $('.input-min').val(50);
                $('.input-max').val(500);
                updateRangeSlider();
            });
            
            // Range slider functionality
            const rangeInput = document.querySelectorAll(".range-input input");
            const priceInput = document.querySelectorAll(".price-input input");
            const range = document.querySelector(".range-selected");
            let priceGap = 10;
            
            function updateRangeSlider() {
                let minVal = parseInt(rangeInput[0].value);
                let maxVal = parseInt(rangeInput[1].value);
                
                if ((maxVal - minVal) < priceGap) {
                    if ($(this).hasClass("range-min")) {
                        rangeInput[0].value = maxVal - priceGap;
                    } else {
                        rangeInput[1].value = minVal + priceGap;
                    }
                } else {
                    priceInput[0].value = minVal;
                    priceInput[1].value = maxVal;
                    range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
                    range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
                }
            }
            
            rangeInput.forEach(input => {
                input.addEventListener("input", updateRangeSlider);
            });
            
            priceInput.forEach(input => {
                input.addEventListener("input", e => {
                    let minPrice = parseInt(priceInput[0].value);
                    let maxPrice = parseInt(priceInput[1].value);
                    
                    if ((maxPrice - minPrice >= priceGap) && maxPrice <= rangeInput[1].max) {
                        if (e.target.className === "input-min") {
                            rangeInput[0].value = minPrice;
                            range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
                        } else {
                            rangeInput[1].value = maxPrice;
                            range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
                        }
                    }
                });
            });
            
            // Initialize range slider on page load
            updateRangeSlider();
            
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
