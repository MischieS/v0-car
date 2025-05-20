<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dreams Rent - Premium Car Rental Service</title>
    <?php include('assets/includes/header_link.php') ?>
    <style>
        /* Custom styles for modern homepage */
        .hero-section {
            background: linear-gradient(135deg, #2a3f90 0%, #1e2d68 100%);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60%;
            height: 100%;
            background: url('assets/img/hero-pattern.png') no-repeat;
            background-size: cover;
            opacity: 0.1;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            color: #fff;
        }
        
        .hero-content h1 span {
            color: #ff9d00;
            position: relative;
        }
        
        .hero-content h1 span::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 4px;
            background: #ff9d00;
            border-radius: 2px;
        }
        
        .hero-content p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 90%;
        }
        
        .hero-image {
            position: relative;
            z-index: 1;
        }
        
        .hero-image img {
            max-width: 120%;
            margin-left: -10%;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.3));
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .search-box-modern {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            margin-top: -70px;
            position: relative;
            z-index: 99;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .search-box-modern .form-group {
            margin-bottom: 0;
        }
        
        .search-box-modern .form-control {
            height: 55px;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
            padding-left: 45px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .search-box-modern .form-control:focus {
            border-color: #2a3f90;
            box-shadow: 0 0 0 3px rgba(42, 63, 144, 0.1);
        }
        
        .search-box-modern .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2a3f90;
            font-size: 18px;
        }
        
        .search-box-modern .btn-search {
            height: 55px;
            border-radius: 8px;
            background: #2a3f90;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            width: 100%;
        }
        
        .search-box-modern .btn-search:hover {
            background: #1e2d68;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 63, 144, 0.2);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2a3f90;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: #ff9d00;
            border-radius: 2px;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .featured-car-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: all 0.3s;
            background: #fff;
        }
        
        .featured-car-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .featured-car-card .car-img {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .featured-car-card .car-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s;
        }
        
        .featured-car-card:hover .car-img img {
            transform: scale(1.1);
        }
        
        .featured-car-card .car-content {
            padding: 20px;
        }
        
        .featured-car-card .car-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2a3f90;
        }
        
        .featured-car-card .car-rating {
            margin-bottom: 10px;
            color: #ff9d00;
        }
        
        .featured-car-card .car-features {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .featured-car-card .car-feature {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
        }
        
        .featured-car-card .car-feature i {
            margin-right: 5px;
            color: #2a3f90;
        }
        
        .featured-car-card .car-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .featured-car-card .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2a3f90;
        }
        
        .featured-car-card .price span {
            font-size: 14px;
            font-weight: 400;
            color: #666;
        }
        
        .featured-car-card .btn-book {
            padding: 8px 20px;
            background: #2a3f90;
            color: #fff;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .featured-car-card .btn-book:hover {
            background: #1e2d68;
        }
        
        .how-it-works {
            background: #f8f9fa;
            padding: 80px 0;
        }
        
        .step-card {
            text-align: center;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 1;
            height: 100%;
        }
        
        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2a3f90 0%, #1e2d68 100%);
            border-radius: 12px;
            opacity: 0;
            z-index: -1;
            transition: all 0.3s;
        }
        
        .step-card:hover::before {
            opacity: 1;
        }
        
        .step-card:hover {
            transform: translateY(-10px);
        }
        
        .step-card:hover * {
            color: #fff;
        }
        
        .step-card .step-icon {
            width: 80px;
            height: 80px;
            background: rgba(42, 63, 144, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.3s;
        }
        
        .step-card:hover .step-icon {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .step-card .step-icon i {
            font-size: 30px;
            color: #2a3f90;
            transition: all 0.3s;
        }
        
        .step-card .step-number {
            position: absolute;
            top: -15px;
            right: -15px;
            width: 40px;
            height: 40px;
            background: #ff9d00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 5px 15px rgba(255, 157, 0, 0.3);
        }
        
        .step-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #2a3f90;
            transition: all 0.3s;
        }
        
        .step-card p {
            font-size: 15px;
            color: #666;
            margin-bottom: 0;
            transition: all 0.3s;
        }
        
        .testimonial-section {
            padding: 80px 0;
        }
        
        .testimonial-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin: 15px;
            position: relative;
        }
        
        .testimonial-card::before {
            content: '\f10d';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            color: rgba(42, 63, 144, 0.1);
        }
        
        .testimonial-card .testimonial-content {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .testimonial-card .testimonial-user {
            display: flex;
            align-items: center;
        }
        
        .testimonial-card .user-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .testimonial-card .user-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .testimonial-card .user-info h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #2a3f90;
        }
        
        .testimonial-card .user-info p {
            font-size: 14px;
            color: #666;
            margin-bottom: 0;
        }
        
        .testimonial-card .rating {
            color: #ff9d00;
            margin-top: 5px;
        }
        
        .benefits-section {
            background: linear-gradient(135deg, #2a3f90 0%, #1e2d68 100%);
            padding: 80px 0;
            color: #fff;
        }
        
        .benefit-card {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        
        .benefit-card .benefit-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .benefit-card .benefit-icon i {
            font-size: 24px;
            color: #ff9d00;
        }
        
        .benefit-card .benefit-content h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .benefit-card .benefit-content p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
        }
        
        .car-categories {
            padding: 80px 0;
        }
        
        .category-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
            height: 200px;
        }
        
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s;
        }
        
        .category-card:hover img {
            transform: scale(1.1);
        }
        
        .category-card .category-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: #fff;
        }
        
        .category-card .category-content h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .category-card .category-content p {
            font-size: 14px;
            margin-bottom: 0;
        }
        
        .cta-section {
            background: url('assets/img/cta-bg.jpg') no-repeat center center;
            background-size: cover;
            padding: 100px 0;
            position: relative;
            z-index: 1;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(42, 63, 144, 0.9) 0%, rgba(30, 45, 104, 0.9) 100%);
            z-index: -1;
        }
        
        .cta-content {
            text-align: center;
            color: #fff;
        }
        
        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta-content p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-cta {
            background: #ff9d00;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-cta:hover {
            background: #e68e00;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            color: #fff;
        }
        
        .btn-cta i {
            margin-left: 5px;
        }
        
        @media (max-width: 991px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-image img {
                max-width: 100%;
                margin-left: 0;
                margin-top: 30px;
            }
            
            .search-box-modern {
                margin-top: 30px;
            }
        }
        
        @media (max-width: 767px) {
            .hero-section {
                padding: 50px 0;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .search-box-modern .form-group {
                margin-bottom: 15px;
            }
            
            .step-card {
                margin-bottom: 30px;
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
                            <h1>Drive Your <span>Dreams</span> Today</h1>
                            <p>Experience premium car rentals with our extensive fleet of luxury, sports, and economy vehicles. Book in seconds and hit the road with confidence.</p>
                            <a href="booking_list.php" class="btn btn-cta">Explore Cars <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="assets/img/car-hero.png" alt="Luxury Car">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Hero Section -->
        
        <!-- Search Section -->
        <section class="search-section">
            <div class="container">
                <div class="search-box-modern">
                    <form action="booking_list.php" method="GET">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                                <div class="form-group position-relative">
                                    <label for="pickup_location" class="form-label">Pickup Location</label>
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                    <input type="text" class="form-control" id="pickup_location" name="pickup_location" placeholder="Enter city, airport, or address">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                <div class="form-group position-relative">
                                    <label for="pickup_date" class="form-label">Pickup Date</label>
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="text" class="form-control datetimepicker" id="pickup_date" name="pickup_date" placeholder="Select date">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
                                <div class="form-group position-relative">
                                    <label for="return_date" class="form-label">Return Date</label>
                                    <i class="fas fa-calendar-check input-icon"></i>
                                    <input type="text" class="form-control datetimepicker" id="return_date" name="return_date" placeholder="Select date">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="form-group h-100 d-flex align-items-end">
                                    <button type="submit" class="btn btn-search">
                                        <i class="fas fa-search me-2"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!-- /Search Section -->
        
        <!-- Featured Cars Section -->
        <section class="featured-cars-section py-5">
            <div class="container">
                <div class="section-title">
                    <h2>Featured Cars</h2>
                    <p>Discover our selection of premium vehicles for your next adventure</p>
                </div>
                
                <div class="row">
                    <!-- Featured Car 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="featured-car-card">
                            <div class="car-img">
                                <img src="assets/img/cars/car-01.jpg" alt="Mercedes-Benz E-Class">
                            </div>
                            <div class="car-content">
                                <h3 class="car-title">Mercedes-Benz E-Class</h3>
                                <div class="car-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <span>(4.8)</span>
                                </div>
                                <div class="car-features">
                                    <div class="car-feature">
                                        <i class="fas fa-car"></i> Sedan
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-cog"></i> Automatic
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-gas-pump"></i> Hybrid
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div class="price">$89 <span>/ day</span></div>
                                    <a href="listing-details.php?id=1" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Featured Car 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="featured-car-card">
                            <div class="car-img">
                                <img src="assets/img/cars/car-02.jpg" alt="BMW 5 Series">
                            </div>
                            <div class="car-content">
                                <h3 class="car-title">BMW 5 Series</h3>
                                <div class="car-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <span>(5.0)</span>
                                </div>
                                <div class="car-features">
                                    <div class="car-feature">
                                        <i class="fas fa-car"></i> Sedan
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-cog"></i> Automatic
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-gas-pump"></i> Petrol
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div class="price">$95 <span>/ day</span></div>
                                    <a href="listing-details.php?id=2" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Featured Car 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="featured-car-card">
                            <div class="car-img">
                                <img src="assets/img/cars/car-03.jpg" alt="Audi Q7">
                            </div>
                            <div class="car-content">
                                <h3 class="car-title">Audi Q7</h3>
                                <div class="car-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <span>(4.0)</span>
                                </div>
                                <div class="car-features">
                                    <div class="car-feature">
                                        <i class="fas fa-car"></i> SUV
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-cog"></i> Automatic
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-gas-pump"></i> Diesel
                                    </div>
                                </div>
                                <div class="car-price">
                                    <div class="price">$120 <span>/ day</span></div>
                                    <a href="listing-details.php?id=3" class="btn-book">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="booking_list.php" class="btn btn-cta">View All Cars <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>
        <!-- /Featured Cars Section -->
        
        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="container">
                <div class="section-title">
                    <h2>How It Works</h2>
                    <p>Renting a car with us is quick and easy - just follow these simple steps</p>
                </div>
                
                <div class="row">
                    <!-- Step 1 -->
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <div class="step-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3>Choose Your Car</h3>
                            <p>Browse our extensive fleet and select the perfect vehicle for your needs.</p>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="step-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h3>Book & Pay</h3>
                            <p>Select your dates and complete the booking with our secure payment system.</p>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <div class="step-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <h3>Pick Up Car</h3>
                            <p>Collect your vehicle from our convenient location with minimal paperwork.</p>
                        </div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="step-card">
                            <div class="step-number">4</div>
                            <div class="step-icon">
                                <i class="fas fa-road"></i>
                            </div>
                            <h3>Enjoy Your Trip</h3>
                            <p>Hit the road with confidence, knowing we provide 24/7 roadside assistance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /How It Works Section -->
        
        <!-- Testimonials Section -->
        <section class="testimonial-section">
            <div class="container">
                <div class="section-title">
                    <h2>Customer Reviews</h2>
                    <p>See what our satisfied customers have to say about their experience with us</p>
                </div>
                
                <div class="row">
                    <!-- Testimonial 1 -->
                    <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                "The service was exceptional from start to finish. The car was in perfect condition, and the staff was incredibly helpful. Will definitely use Dreams Rent again for my next trip!"
                            </div>
                            <div class="testimonial-user">
                                <div class="user-img">
                                    <img src="assets/img/testimonials/user-1.jpg" alt="John Smith">
                                </div>
                                <div class="user-info">
                                    <h5>John Smith</h5>
                                    <p>Business Traveler</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                "I rented a luxury car for my anniversary, and it made our special day even more memorable. The booking process was smooth, and the car was delivered right on time."
                            </div>
                            <div class="testimonial-user">
                                <div class="user-img">
                                    <img src="assets/img/testimonials/user-2.jpg" alt="Sarah Johnson">
                                </div>
                                <div class="user-info">
                                    <h5>Sarah Johnson</h5>
                                    <p>Vacation Traveler</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                "As a frequent traveler, I've used many car rental services, but Dreams Rent stands out for their exceptional fleet and customer service. Their prices are competitive too!"
                            </div>
                            <div class="testimonial-user">
                                <div class="user-img">
                                    <img src="assets/img/testimonials/user-3.jpg" alt="Michael Brown">
                                </div>
                                <div class="user-info">
                                    <h5>Michael Brown</h5>
                                    <p>Frequent Traveler</p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Testimonials Section -->
        
        <!-- Benefits Section -->
        <section class="benefits-section">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="text-white">Why Choose Us</h2>
                    <p class="text-white opacity-75">We offer the best car rental experience with premium services</p>
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <!-- Benefit 1 -->
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="benefit-content">
                                <h3>Premium Fleet</h3>
                                <p>Our fleet includes the latest models from top manufacturers, all meticulously maintained for your safety and comfort.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <!-- Benefit 2 -->
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="benefit-content">
                                <h3>Best Price Guarantee</h3>
                                <p>We offer competitive pricing with no hidden fees. If you find a better price elsewhere, we'll match it.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <!-- Benefit 3 -->
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="benefit-content">
                                <h3>24/7 Customer Support</h3>
                                <p>Our dedicated support team is available around the clock to assist you with any questions or concerns.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <!-- Benefit 4 -->
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="benefit-content">
                                <h3>Comprehensive Insurance</h3>
                                <p>All our rentals come with comprehensive insurance coverage for your peace of mind.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Benefits Section -->
        
        <!-- Car Categories Section -->
        <section class="car-categories">
            <div class="container">
                <div class="section-title">
                    <h2>Browse By Category</h2>
                    <p>Find the perfect vehicle for your specific needs</p>
                </div>
                
                <div class="row">
                    <!-- Category 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card">
                            <img src="assets/img/categories/luxury.jpg" alt="Luxury Cars">
                            <div class="category-content">
                                <h3>Luxury Cars</h3>
                                <p>Experience ultimate comfort and style</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card">
                            <img src="assets/img/categories/suv.jpg" alt="SUVs">
                            <div class="category-content">
                                <h3>SUVs</h3>
                                <p>Perfect for family trips and adventures</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card">
                            <img src="assets/img/categories/sports.jpg" alt="Sports Cars">
                            <div class="category-content">
                                <h3>Sports Cars</h3>
                                <p>Feel the thrill of high performance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Car Categories Section -->
        
        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Hit the Road?</h2>
                    <p>Book your dream car today and enjoy the freedom of the open road. Our premium fleet and exceptional service are waiting for you.</p>
                    <a href="booking_list.php" class="btn-cta">Book Now <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>
        <!-- /CTA Section -->
        
        <!-- Footer -->
        <?php include('assets/includes/footer.php') ?>
        <!-- /Footer -->
    </div>
    
    <?php include('assets/includes/footer_link.php') ?>
    
    <script>
        // Initialize date pickers
        $(document).ready(function() {
            $('.datetimepicker').datetimepicker({
                format: 'MM/DD/YYYY',
                minDate: new Date(),
                icons: {
                    up: "fas fa-chevron-up",
                    down: "fas fa-chevron-down",
                    next: 'fas fa-chevron-right',
                    previous: 'fas fa-chevron-left'
                }
            });
            
            // Add animation on scroll
            $(window).scroll(function() {
                var windowTop = $(window).scrollTop();
                
                $('.featured-car-card, .step-card, .testimonial-card, .benefit-card, .category-card').each(function() {
                    var elementTop = $(this).offset().top;
                    
                    if (windowTop > elementTop - $(window).height() + 100) {
                        $(this).addClass('animated fadeInUp');
                    }
                });
            });
        });
    </script>
</body>
</html>
