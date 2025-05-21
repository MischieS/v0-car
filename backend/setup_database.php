<?php
// Simple database setup file
// This file checks if the database and required tables exist and creates them if needed

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'car_rental_db';

// Output header with Bootstrap styling
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container py-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-primary text-white'>
                        <h3 class='mb-0'>Database Setup</h3>
                    </div>
                    <div class='card-body'>
                        <div class='alert alert-info'>Setting up database tables. Please wait...</div>";

// Connect to MySQL server
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Create database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);

// Create tables
// USERS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS users (
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
) ENGINE=InnoDB");

// LOCATIONS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB");

// Insert default locations if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM locations");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO locations (location_name) VALUES
        ('Istanbul Office'), ('Ankara Office'), ('Izmir Office')");
}

// CARS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS cars (
    car_id INT AUTO_INCREMENT PRIMARY KEY,
    car_type VARCHAR(100) NOT NULL,
    car_price_perday DECIMAL(10,2) NOT NULL,
    year YEAR NOT NULL,
    location_id INT NOT NULL,
    car_class VARCHAR(50) NOT NULL,
    car_image VARCHAR(255),
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
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE RESTRICT
) ENGINE=InnoDB");

// CAR IMAGES TABLE
$conn->query("CREATE TABLE IF NOT EXISTS car_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
) ENGINE=InnoDB");

// RESERVATIONS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS reservations (
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
) ENGINE=InnoDB");

// SITE SETTINGS TABLE
$conn->query("CREATE TABLE IF NOT EXISTS site_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Insert default site settings if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM site_settings");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO site_settings (setting_name, setting_value, setting_group, is_public) VALUES
        ('site_name', 'Car Rental System', 'general', TRUE),
        ('site_description', 'Rent your dream car today', 'general', TRUE),
        ('contact_email', 'contact@example.com', 'contact', TRUE),
        ('contact_phone', '+1234567890', 'contact', TRUE),
        ('address', '123 Main Street, City, Country', 'contact', TRUE)");
}

// Add default admin user if not exists
$adminEmail = 'admin@gmail.com';
$result = $conn->query("SELECT * FROM users WHERE user_email = '$adminEmail'");
if ($result->num_rows === 0) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("
        INSERT INTO users (first_name, last_name, user_email, password_hash, user_role)
        VALUES ('Admin', 'User', '$adminEmail', '$pass', 'admin')
    ");
    echo "<div class='alert alert-success'>Admin user created: admin@gmail.com / admin123</div>";
}

// Add sample cars if cars table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM cars");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    // Create directories for car images if they don't exist
    $directories = ['../assets/img/cars', '../assets/img/users', '../assets/img/cars/gallery'];
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Sample cars data
    $sampleCars = [
        [
            'car_type' => 'Mercedes-Benz E-Class',
            'car_price_perday' => 89.99,
            'year' => 2023,
            'location_id' => 1,
            'car_class' => 'Luxury',
            'car_image' => 'assets/img/cars/mercedes-e-class.jpg',
            'featured' => 1,
            'transmission' => 'Automatic',
            'fuel_type' => 'Petrol',
            'passengers' => 5,
            'brand' => 'Mercedes',
            'model' => 'E-Class',
            'type' => 'Sedan'
        ],
        [
            'car_type' => 'BMW 5 Series',
            'car_price_perday' => 95.99,
            'year' => 2022,
            'location_id' => 2,
            'car_class' => 'Luxury',
            'car_image' => 'assets/img/cars/bmw-5-series.jpg',
            'featured' => 1,
            'transmission' => 'Automatic',
            'fuel_type' => 'Petrol',
            'passengers' => 5,
            'brand' => 'BMW',
            'model' => '5 Series',
            'type' => 'Sedan'
        ],
        [
            'car_type' => 'Audi Q7',
            'car_price_perday' => 120.00,
            'year' => 2023,
            'location_id' => 3,
            'car_class' => 'SUV',
            'car_image' => 'assets/img/cars/audi-q7.jpg',
            'featured' => 1,
            'transmission' => 'Automatic',
            'fuel_type' => 'Diesel',
            'passengers' => 7,
            'brand' => 'Audi',
            'model' => 'Q7',
            'type' => 'SUV'
        ]
    ];
    
    foreach ($sampleCars as $car) {
        $stmt = $conn->prepare("
            INSERT INTO cars (
                car_type, car_price_perday, year, location_id, car_class, car_image,
                featured, transmission, fuel_type, passengers, brand, model, type, image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $image = $car['car_image'];
        $stmt->bind_param(
            'sdiisisssissss',
            $car['car_type'], $car['car_price_perday'], $car['year'], $car['location_id'],
            $car['car_class'], $car['car_image'], $car['featured'], $car['transmission'],
            $car['fuel_type'], $car['passengers'], $car['brand'], $car['model'], $car['type'], $image
        );
        
        $stmt->execute();
        $carId = $conn->insert_id;
        
        // Add gallery images
        $conn->query("INSERT INTO car_images (car_id, image_path) VALUES
            ($carId, '{$car['car_image']}'),
            ($carId, 'assets/img/cars/gallery-1.jpg'),
            ($carId, 'assets/img/cars/gallery-2.jpg')
        ");
    }
    
    echo "<div class='alert alert-success'>Sample cars added to the database</div>";
}

// Close connection
$conn->close();

echo "<div class='alert alert-success'>Database setup completed successfully!</div>";
echo "<div class='mt-3'><a href='../index.php' class='btn btn-primary'>Go to Homepage</a></div>";
echo "</div></div></div></div></div>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body></html>";
?>
