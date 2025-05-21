<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging during setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output HTML header with styling
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 2rem; padding-bottom: 2rem; background-color: #f8f9fa; }
        .setup-container { max-width: 900px; margin: 0 auto; }
        .card { margin-bottom: 1.5rem; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid rgba(0, 0, 0, 0.125); }
        .log-message { padding: 0.5rem; margin-bottom: 0.5rem; border-radius: 0.25rem; }
        .log-info { background-color: #cfe2ff; color: #084298; }
        .log-success { background-color: #d1e7dd; color: #0f5132; }
        .log-warning { background-color: #fff3cd; color: #856404; }
        .log-error { background-color: #f8d7da; color: #842029; }
        .table { font-size: 0.875rem; }
        .progress { height: 0.5rem; margin-bottom: 1rem; }
        code { background-color: #f1f1f1; padding: 0.2rem 0.4rem; border-radius: 0.2rem; }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1 class="mb-4 text-center">Car Rental System - Database Setup</h1>
        <div class="progress" id="setup-progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Setup Log</h5>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" id="toggle-details">Show Details</button>
                </div>
            </div>
            <div class="card-body">
                <div id="setup-log">
';

// Function to log messages with different types
function logMessage($message, $type = 'info') {
    echo "<div class='log-message log-{$type}'>{$message}</div>";
    // Flush output buffer to show progress in real-time
    ob_flush();
    flush();
    
    // Update progress bar via JavaScript
    static $progress = 0;
    $progress += 5;
    if ($progress > 100) $progress = 100;
    echo "<script>document.querySelector('#setup-progress .progress-bar').style.width = '{$progress}%';</script>";
    ob_flush();
    flush();
}

// Function to execute SQL safely and log results
function executeSql($conn, $sql, $description, $continueOnError = false) {
    try {
        logMessage("Executing: {$description}...");
        if ($conn->query($sql)) {
            logMessage("{$description} - Completed successfully", "success");
            return true;
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        logMessage("Error: {$description} - " . $e->getMessage(), "error");
        if (!$continueOnError) {
            throw $e; // Re-throw to stop execution
        }
        return false;
    }
}

try {
    // Step 1: Connect to MySQL server
    logMessage("Connecting to MySQL server...");
    $conn = new mysqli('localhost', 'root', '');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    logMessage("Connected to MySQL server successfully", "success");
    
    // Step 2: Create database if it doesn't exist
    $dbname = 'car_rental_db';
    executeSql($conn, "CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci", 
        "Creating database '{$dbname}'");
    
    // Step 3: Select the database
    if (!$conn->select_db($dbname)) {
        throw new Exception("Could not select database: " . $conn->error);
    }
    logMessage("Selected database '{$dbname}'", "success");
    
    // Step 4: Create users table
    $usersTable = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        user_name VARCHAR(50),
        user_email VARCHAR(100) NOT NULL UNIQUE,
        phone_number VARCHAR(20),
        address TEXT,
        country VARCHAR(100),
        state VARCHAR(100),
        city VARCHAR(100),
        pincode VARCHAR(20),
        user_profile_image VARCHAR(255),
        driving_licence_no VARCHAR(50),
        password_hash VARCHAR(255) NOT NULL,
        user_role ENUM('admin','user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    executeSql($conn, $usersTable, "Creating 'users' table");
    
    // Step 5: Create user_activity table
    $activityTable = "CREATE TABLE IF NOT EXISTS user_activity (
        activity_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        activity_type VARCHAR(50) NOT NULL,
        activity_description TEXT NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    executeSql($conn, $activityTable, "Creating 'user_activity' table", true);
    
    // Step 6: Create user_sessions table
    $sessionsTable = "CREATE TABLE IF NOT EXISTS user_sessions (
        session_id VARCHAR(64) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        is_active TINYINT(1) DEFAULT 1,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    executeSql($conn, $sessionsTable, "Creating 'user_sessions' table", true);
    
    // Step 7: Create locations table
    $locationsTable = "CREATE TABLE IF NOT EXISTS locations (
        location_id INT AUTO_INCREMENT PRIMARY KEY,
        location_name VARCHAR(255) NOT NULL UNIQUE
    )";
    executeSql($conn, $locationsTable, "Creating 'locations' table");
    
    // Insert default locations
    $locationsInsert = "INSERT IGNORE INTO locations (location_name) VALUES
        ('Istanbul Office'), ('Ankara Office'), ('Izmir Office')";
    executeSql($conn, $locationsInsert, "Adding default locations", true);
    
    // Step 8: Create car_brands table
    $brandsTable = "CREATE TABLE IF NOT EXISTS car_brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_name VARCHAR(50) NOT NULL UNIQUE
    )";
    executeSql($conn, $brandsTable, "Creating 'car_brands' table");
    
    // Insert default brands
    $brandsInsert = "INSERT IGNORE INTO car_brands (brand_name) VALUES
        ('Toyota'), ('BMW'), ('Mercedes'), ('Honda'), ('Audi')";
    executeSql($conn, $brandsInsert, "Adding default car brands", true);
    
    // Step 9: Create car_models table
    $modelsTable = "CREATE TABLE IF NOT EXISTS car_models (
        id INT AUTO_INCREMENT PRIMARY KEY,
        model_name VARCHAR(50) NOT NULL,
        brand_id INT NOT NULL,
        FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE CASCADE
    )";
    executeSql($conn, $modelsTable, "Creating 'car_models' table");
    
    // Insert default models
    $modelsInsert = "INSERT IGNORE INTO car_models (model_name, brand_id) VALUES
        ('Corolla', 1), ('Camry', 1),
        ('3 Series', 2), ('5 Series', 2),
        ('C-Class', 3), ('E-Class', 3),
        ('Civic', 4), ('Accord', 4),
        ('A3', 5), ('A6', 5)";
    executeSql($conn, $modelsInsert, "Adding default car models", true);
    
    // Step 10: Create car_categories table
    $categoriesTable = "CREATE TABLE IF NOT EXISTS car_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(50) NOT NULL UNIQUE
    )";
    executeSql($conn, $categoriesTable, "Creating 'car_categories' table");
    
    // Insert default categories
    $categoriesInsert = "INSERT IGNORE INTO car_categories (category_name) VALUES
        ('Sedan'), ('SUV'), ('Hatchback'), ('Convertible')";
    executeSql($conn, $categoriesInsert, "Adding default car categories", true);
    
    // Step 11: Create fuel_types table
    $fuelTypesTable = "CREATE TABLE IF NOT EXISTS fuel_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fuel_name VARCHAR(30) NOT NULL UNIQUE
    )";
    executeSql($conn, $fuelTypesTable, "Creating 'fuel_types' table");
    
    // Insert default fuel types
    $fuelTypesInsert = "INSERT IGNORE INTO fuel_types (fuel_name) VALUES
        ('Petrol'), ('Diesel'), ('Electric'), ('Hybrid')";
    executeSql($conn, $fuelTypesInsert, "Adding default fuel types", true);
    
    // Step 12: Create gear_types table
    $gearTypesTable = "CREATE TABLE IF NOT EXISTS gear_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gear_name VARCHAR(30) NOT NULL UNIQUE
    )";
    executeSql($conn, $gearTypesTable, "Creating 'gear_types' table");
    
    // Insert default gear types
    $gearTypesInsert = "INSERT IGNORE INTO gear_types (gear_name) VALUES
        ('Manual'), ('Automatic'), ('CVT')";
    executeSql($conn, $gearTypesInsert, "Adding default gear types", true);
    
    // Step 13: Create cars table
    $carsTable = "CREATE TABLE IF NOT EXISTS cars (
        car_id INT AUTO_INCREMENT PRIMARY KEY,
        car_type VARCHAR(100) NOT NULL,
        car_price_perday DECIMAL(10,2) NOT NULL,
        year YEAR NOT NULL,
        location_id INT NOT NULL,
        car_class VARCHAR(50) NOT NULL,
        car_image VARCHAR(255),
        brand_id INT,
        model_id INT,
        category_id INT,
        fuel_type_id INT,
        gear_type_id INT,
        featured TINYINT(1) DEFAULT 0,
        transmission VARCHAR(20) DEFAULT 'Automatic',
        fuel_type VARCHAR(20) DEFAULT 'Petrol',
        passengers INT DEFAULT 5,
        brand VARCHAR(50),
        model VARCHAR(50),
        type VARCHAR(50) DEFAULT 'Sedan',
        image VARCHAR(255),
        price_per_day DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE RESTRICT,
        FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE SET NULL,
        FOREIGN KEY (model_id) REFERENCES car_models(id) ON DELETE SET NULL,
        FOREIGN KEY (category_id) REFERENCES car_categories(id) ON DELETE SET NULL,
        FOREIGN KEY (fuel_type_id) REFERENCES fuel_types(id) ON DELETE SET NULL,
        FOREIGN KEY (gear_type_id) REFERENCES gear_types(id) ON DELETE SET NULL
    ) ENGINE=InnoDB";
    executeSql($conn, $carsTable, "Creating 'cars' table");
    
    // Step 14: Create car_images table
    $carImagesTable = "CREATE TABLE IF NOT EXISTS car_images (
        image_id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
    )";
    executeSql($conn, $carImagesTable, "Creating 'car_images' table");
    
    // Step 15: Create reservations table
    $reservationsTable = "CREATE TABLE IF NOT EXISTS reservations (
        reservation_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        pickup_location VARCHAR(255) NOT NULL,
        return_location VARCHAR(255) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('active','completed','cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
    )";
    executeSql($conn, $reservationsTable, "Creating 'reservations' table");
    
    // Step 16: Create site_settings table
    $settingsTable = "CREATE TABLE IF NOT EXISTS site_settings (
        setting_id INT AUTO_INCREMENT PRIMARY KEY,
        setting_group VARCHAR(50) NOT NULL,
        setting_name VARCHAR(100) NOT NULL,
        setting_value TEXT,
        updated_by INT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (setting_group, setting_name)
    )";
    executeSql($conn, $settingsTable, "Creating 'site_settings' table");
    
    // Insert default settings
    logMessage("Adding default site settings...");
    $defaultSettings = [
        // General settings
        ['general', 'site_name', 'Car Rental System'],
        ['general', 'site_description', 'Rent your dream car today'],
        ['system', 'maintenance_mode', '0'],
        
        // Contact settings
        ['contact', 'contact_email', 'contact@carrentalsystem.com'],
        ['contact', 'contact_phone', '+1 (555) 123-4567'],
        ['contact', 'address', '123 Main Street, City, Country'],
        
        // Feature toggles
        ['features', 'enable_bookings', '1'],
        
        // Booking settings
        ['booking', 'min_rental_days', '1'],
        ['booking', 'max_rental_days', '30'],
        
        // Payment settings
        ['payment', 'currency_symbol', '$'],
        ['payment', 'tax_rate', '10'],
        ['payment', 'booking_fee', '5'],
        
        // Social media
        ['social', 'facebook_url', 'https://facebook.com/carrentalsystem'],
        ['social', 'twitter_url', 'https://twitter.com/carrentalsystem'],
        ['social', 'instagram_url', 'https://instagram.com/carrentalsystem']
    ];
    
    $settingsStmt = $conn->prepare("INSERT IGNORE INTO site_settings (setting_group, setting_name, setting_value) VALUES (?, ?, ?)");
    $settingsCount = 0;
    
    foreach ($defaultSettings as $setting) {
        $settingsStmt->bind_param('sss', $setting[0], $setting[1], $setting[2]);
        if ($settingsStmt->execute()) {
            $settingsCount++;
        }
    }
    logMessage("Added {$settingsCount} default settings", "success");
    
    // Step 17: Add default admin user
    logMessage("Checking for admin user...");
    $adminEmail = 'admin@gmail.com';
    $adminResult = $conn->query("SELECT user_id FROM users WHERE user_email = '{$adminEmail}'");
    
    if ($adminResult->num_rows === 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $adminInsert = "INSERT INTO users (first_name, last_name, user_email, password_hash, user_role)
            VALUES ('Admin', 'User', '{$adminEmail}', '{$adminPass}', 'admin')";
        
        if ($conn->query($adminInsert)) {
            logMessage("Created default admin user: {$adminEmail} / admin123", "success");
        } else {
            logMessage("Failed to create admin user: " . $conn->error, "error");
        }
    } else {
        logMessage("Admin user already exists", "info");
    }
    
    // Step 18: Add example users
    logMessage("Adding example users...");
    $exampleUsers = [
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'user_email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'address' => '123 Main St',
            'country' => 'United States',
            'city' => 'New York',
            'state' => 'NY',
            'pincode' => '10001',
            'driving_licence_no' => 'DL12345678',
            'password' => 'password123'
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'user_email' => 'jane@example.com',
            'phone_number' => '+1987654321',
            'address' => '456 Oak Ave',
            'country' => 'United States',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'pincode' => '90001',
            'driving_licence_no' => 'DL87654321',
            'password' => 'password123'
        ]
    ];
    
    $userCount = 0;
    foreach ($exampleUsers as $user) {
        $email = $user['user_email'];
        $userResult = $conn->query("SELECT user_id FROM users WHERE user_email = '{$email}'");
        
        if ($userResult->num_rows === 0) {
            $pass = password_hash($user['password'], PASSWORD_DEFAULT);
            $userInsert = "INSERT INTO users (
                first_name, last_name, user_email, phone_number, 
                address, country, city, state, pincode, 
                driving_licence_no, password_hash, user_role
            ) VALUES (
                '{$user['first_name']}', '{$user['last_name']}', '{$user['user_email']}', '{$user['phone_number']}',
                '{$user['address']}', '{$user['country']}', '{$user['city']}', '{$user['state']}', '{$user['pincode']}',
                '{$user['driving_licence_no']}', '{$pass}', 'user'
            )";
            
            if ($conn->query($userInsert)) {
                $userCount++;
            }
        }
    }
    logMessage("Added {$userCount} example users", "success");
    
    // Step 19: Add example cars
    logMessage("Adding example cars...");
    $exampleCars = [
        [
            'car_type' => 'Mercedes-Benz E-Class',
            'car_price_perday' => 89.99,
            'year' => 2023,
            'location_id' => 1,
            'car_class' => 'Luxury',
            'car_image' => 'assets/img/cars/mercedes-e-class.jpg',
            'brand_id' => 3,
            'model_id' => 6,
            'category_id' => 1,
            'fuel_type_id' => 1,
            'gear_type_id' => 2,
            'featured' => 1,
            'transmission' => 'Automatic',
            'fuel_type' => 'Petrol',
            'passengers' => 5,
            'brand' => 'Mercedes',
            'model' => 'E-Class',
            'type' => 'Sedan',
            'image' => 'assets/img/cars/mercedes-e-class.jpg',
            'price_per_day' => 89.99
        ],
        [
            'car_type' => 'BMW 5 Series',
            'car_price_perday' => 95.99,
            'year' => 2022,
            'location_id' => 2,
            'car_class' => 'Luxury',
            'car_image' => 'assets/img/cars/bmw-5-series.jpg',
            'brand_id' => 2,
            'model_id' => 4,
            'category_id' => 1,
            'fuel_type_id' => 1,
            'gear_type_id' => 2,
            'featured' => 1,
            'transmission' => 'Automatic',
            'fuel_type' => 'Petrol',
            'passengers' => 5,
            'brand' => 'BMW',
            'model' => '5 Series',
            'type' => 'Sedan',
            'image' => 'assets/img/cars/bmw-5-series.jpg',
            'price_per_day' => 95.99
        ],
        [
            'car_type' => 'Audi Q7',
            'car_price_perday' => 120.00,
            'year' => 2023,
            'location_id' => 3,
            'car_class' => 'SUV',
            'car_image' => 'assets/img/cars/audi-q7.jpg',
            'brand_id' => 5,
            'model_id' => 10,
            'category_id' => 2,
            'fuel_type_id' => 2,
            'gear_type_id' => 2,
            'featured' => 1,
            'transmission' => 'Automatic',
            'fuel_type' => 'Diesel',
            'passengers' => 7,
            'brand' => 'Audi',
            'model' => 'Q7',
            'type' => 'SUV',
            'image' => 'assets/img/cars/audi-q7.jpg',
            'price_per_day' => 120.00
        ]
    ];
    
    // Clear existing example cars to avoid duplicates
    $conn->query("DELETE FROM car_images WHERE car_id IN (SELECT car_id FROM cars WHERE car_type IN ('Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi Q7'))");
    $conn->query("DELETE FROM cars WHERE car_type IN ('Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi Q7')");
    
    $carCount = 0;
    foreach ($exampleCars as $car) {
        $carInsert = "INSERT INTO cars (
            car_type, car_price_perday, year, location_id, car_class, car_image,
            brand_id, model_id, category_id, fuel_type_id, gear_type_id, featured,
            transmission, fuel_type, passengers, brand, model, type, image, price_per_day
        ) VALUES (
            '{$car['car_type']}', {$car['car_price_perday']}, {$car['year']}, {$car['location_id']},
            '{$car['car_class']}', '{$car['car_image']}', {$car['brand_id']}, {$car['model_id']},
            {$car['category_id']}, {$car['fuel_type_id']}, {$car['gear_type_id']}, {$car['featured']},
            '{$car['transmission']}', '{$car['fuel_type']}', {$car['passengers']}, '{$car['brand']}',
            '{$car['model']}', '{$car['type']}', '{$car['image']}', {$car['price_per_day']}
        )";
        
        if ($conn->query($carInsert)) {
            $carId = $conn->insert_id;
            $carCount++;
            
            // Add gallery images
            $conn->query("INSERT INTO car_images (car_id, image_path) VALUES
                ({$carId}, '{$car['image']}'),
                ({$carId}, 'assets/img/cars/gallery-1.jpg'),
                ({$carId}, 'assets/img/cars/gallery-2.jpg')
            ");
        }
    }
    logMessage("Added {$carCount} example cars with images", "success");
    
    // Step 20: Create example reservations
    logMessage("Setting up example reservations...");
    
    // Get user IDs
    $userQuery = $conn->query("SELECT user_id FROM users WHERE user_email IN ('john@example.com', 'jane@example.com')");
    $userIds = [];
    while ($row = $userQuery->fetch_assoc()) {
        $userIds[] = $row['user_id'];
    }
    
    // Get car IDs
    $carQuery = $conn->query("SELECT car_id FROM cars WHERE car_type IN ('Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi Q7')");
    $carIds = [];
    while ($row = $carQuery->fetch_assoc()) {
        $carIds[] = $row['car_id'];
    }
    
    // Create reservations if we have users and cars
    if (!empty($userIds) && !empty($carIds)) {
        $userIdsStr = implode(',', $userIds);
        $carIdsStr = implode(',', $carIds);
        
        // Clear existing reservations for these users and cars
        $conn->query("DELETE FROM reservations WHERE user_id IN ({$userIdsStr}) AND car_id IN ({$carIdsStr})");
        
        // Create example reservations
        $exampleReservations = [
            [
                'user_id' => $userIds[0],
                'car_id' => $carIds[0],
                'start_date' => date('Y-m-d', strtotime('+2 days')),
                'end_date' => date('Y-m-d', strtotime('+5 days')),
                'pickup_location' => 'Istanbul Office',
                'return_location' => 'Istanbul Office',
                'total_price' => 269.97, // 3 days * 89.99
                'status' => 'active'
            ],
            [
                'user_id' => $userIds[0],
                'car_id' => $carIds[1],
                'start_date' => date('Y-m-d', strtotime('+1 week')),
                'end_date' => date('Y-m-d', strtotime('+10 days')),
                'pickup_location' => 'Ankara Office',
                'return_location' => 'Ankara Office',
                'total_price' => 287.97, // 3 days * 95.99
                'status' => 'active'
            ],
            [
                'user_id' => $userIds[0],
                'car_id' => $carIds[2],
                'start_date' => date('Y-m-d', strtotime('+2 weeks')),
                'end_date' => date('Y-m-d', strtotime('+17 days')),
                'pickup_location' => 'Izmir Office',
                'return_location' => 'Istanbul Office',
                'total_price' => 360.00, // 3 days * 120.00
                'status' => 'active'
            ]
        ];
        
        $reservationCount = 0;
        foreach ($exampleReservations as $reservation) {
            $reservationInsert = "INSERT INTO reservations (
                user_id, car_id, start_date, end_date,
                pickup_location, return_location, total_price, status
            ) VALUES (
                {$reservation['user_id']}, {$reservation['car_id']}, '{$reservation['start_date']}', '{$reservation['end_date']}',
                '{$reservation['pickup_location']}', '{$reservation['return_location']}', {$reservation['total_price']}, '{$reservation['status']}'
            )";
            
            if ($conn->query($reservationInsert)) {
                $reservationCount++;
            }
        }
        logMessage("Added {$reservationCount} example reservations", "success");
    } else {
        logMessage("Could not create reservations: missing users or cars", "warning");
    }
    
    // Step 21: Create necessary directories
    logMessage("Creating necessary directories...");
    $directories = ['assets/img/cars', 'assets/img/users', 'assets/img/cars/gallery'];
    $dirCount = 0;
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0755, true)) {
                $dirCount++;
            }
        }
    }
    logMessage("Created {$dirCount} directories", "success");
    
    // Step 22: Check for the featured column in cars table
    logMessage("Checking for 'featured' column in cars table...");
    $featuredResult = $conn->query("SHOW COLUMNS FROM cars LIKE 'featured'");
    
    if ($featuredResult->num_rows === 0) {
        logMessage("Adding 'featured' column to cars table...");
        if ($conn->query("ALTER TABLE cars ADD COLUMN featured TINYINT(1) DEFAULT 0")) {
            logMessage("Added 'featured' column to cars table", "success");
            
            // Mark some cars as featured
            $conn->query("UPDATE cars SET featured = 1 ORDER BY car_id DESC LIMIT 6");
            logMessage("Marked 6 cars as featured", "success");
        } else {
            logMessage("Failed to add 'featured' column: " . $conn->error, "error");
        }
    } else {
        logMessage("'featured' column already exists in cars table", "info");
    }
    
    // Final success message
    logMessage("Database setup completed successfully!", "success");
    
    // Display database statistics
    echo "<div class='card mt-4'>
            <div class='card-header'>
                <h5 class='mb-0'>Database Statistics</h5>
            </div>
            <div class='card-body'>
                <div class='table-responsive'>
                    <table class='table table-sm'>
                        <thead>
                            <tr>
                                <th>Table</th>
                                <th>Records</th>
                            </tr>
                        </thead>
                        <tbody>";
    
    $tables = ['users', 'cars', 'car_brands', 'car_models', 'locations', 'reservations', 'site_settings'];
    foreach ($tables as $table) {
        $countResult = $conn->query("SELECT COUNT(*) as count FROM {$table}");
        $count = $countResult->fetch_assoc()['count'];
        echo "<tr>
                <td>{$table}</td>
                <td>{$count}</td>
              </tr>";
    }
    
    echo "      </tbody>
                    </table>
                </div>
            </div>
        </div>";
    
    // Display login credentials
    echo "<div class='card mt-4'>
            <div class='card-header'>
                <h5 class='mb-0'>Login Credentials</h5>
            </div>
            <div class='card-body'>
                <div class='table-responsive'>
                    <table class='table table-sm'>
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Admin</td>
                                <td>admin@gmail.com</td>
                                <td>admin123</td>
                            </tr>
                            <tr>
                                <td>User</td>
                                <td>john@example.com</td>
                                <td>password123</td>
                            </tr>
                            <tr>
                                <td>User</td>
                                <td>jane@example.com</td>
                                <td>password123</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";
    
} catch (Exception $e) {
    logMessage("Critical Error: " . $e->getMessage(), "error");
    echo "<div class='card mt-4 border-danger'>
            <div class='card-header bg-danger text-white'>
                <h5 class='mb-0'>Setup Failed</h5>
            </div>
            <div class='card-body'>
                <p>The database setup encountered a critical error:</p>
                <div class='log-message log-error'>{$e->getMessage()}</div>
                <p class='mt-3'>Please check your MySQL configuration and try again.</p>
            </div>
        </div>";
}

// Close connection if it exists
if (isset($conn) && $conn) {
    $conn->close();
}

// Output HTML footer with JavaScript
echo '
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-primary me-2">Go to Homepage</a>
            <a href="../login.php" class="btn btn-success">Go to Login</a>
        </div>
    </div>
    <script>
        // Toggle details button
        document.getElementById("toggle-details").addEventListener("click", function() {
            const logDiv = document.getElementById("setup-log");
            const btn = document.getElementById("toggle-details");
            
            if (logDiv.classList.contains("d-none")) {
                logDiv.classList.remove("d-none");
                btn.textContent = "Hide Details";
            } else {
                logDiv.classList.add("d-none");
                btn.textContent = "Show Details";
            }
        });
        
        // Set progress bar to 100% when done
        document.querySelector("#setup-progress .progress-bar").style.width = "100%";
    </script>
</body>
</html>';
?>
