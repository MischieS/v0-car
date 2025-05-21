<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Replace the HTML header with Bootstrap styling
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
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

mysqli_report(MYSQLI_REPORT_OFF);
$setup = new mysqli('localhost', 'root', '');
if ($setup->connect_error) die('Setup DB failed: ' . $setup->connect_error);

$db = 'car_rental_db';
$setup->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$setup->select_db($db) or die('Select DB failed: ' . $setup->error);

// USERS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS users (
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

// USER ACTIVITY LOG TABLE
$setup->query("CREATE TABLE IF NOT EXISTS user_activity (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB");

// USER SESSIONS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB");

// LOCATIONS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO locations (location_name) VALUES
    ('Istanbul Office'), ('Ankara Office'), ('Izmir Office')");

// BRANDS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS car_brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO car_brands (brand_name) VALUES
    ('Toyota'), ('BMW'), ('Mercedes'), ('Honda'), ('Audi')");

// MODELS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS car_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(50) NOT NULL,
    brand_id INT NOT NULL,
    FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE CASCADE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO car_models (model_name, brand_id) VALUES
    ('Corolla', 1), ('Camry', 1),
    ('3 Series', 2), ('5 Series', 2),
    ('C-Class', 3), ('E-Class', 3),
    ('Civic', 4), ('Accord', 4),
    ('A3', 5), ('A6', 5)");

// CATEGORIES TABLE
$setup->query("CREATE TABLE IF NOT EXISTS car_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO car_categories (category_name) VALUES
    ('Sedan'), ('SUV'), ('Hatchback'), ('Convertible')");

// FUEL TYPES
$setup->query("CREATE TABLE IF NOT EXISTS fuel_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fuel_name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO fuel_types (fuel_name) VALUES
    ('Petrol'), ('Diesel'), ('Electric'), ('Hybrid')");

// GEAR TYPES
$setup->query("CREATE TABLE IF NOT EXISTS gear_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gear_name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO gear_types (gear_name) VALUES
    ('Manual'), ('Automatic'), ('CVT')");

// CARS TABLE (extended)
$setup->query("CREATE TABLE IF NOT EXISTS cars (
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
) ENGINE=InnoDB");

// CAR IMAGES TABLE (for gallery)
$setup->query("CREATE TABLE IF NOT EXISTS car_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
) ENGINE=InnoDB");

// RESERVATIONS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS reservations (
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
$setup->query("CREATE TABLE IF NOT EXISTS site_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB");

// Insert default site settings
$setup->query("INSERT IGNORE INTO site_settings (setting_name, setting_value, setting_group, is_public) VALUES
    ('site_name', 'Car Rental System', 'general', TRUE),
    ('site_description', 'Rent your dream car today', 'general', TRUE),
    ('contact_email', 'contact@example.com', 'contact', TRUE),
    ('contact_phone', '+1234567890', 'contact', TRUE),
    ('address', '123 Main Street, City, Country', 'contact', TRUE),
    ('facebook_url', 'https://facebook.com/', 'social', TRUE),
    ('twitter_url', 'https://twitter.com/', 'social', TRUE),
    ('instagram_url', 'https://instagram.com/', 'social', TRUE),
    ('enable_bookings', '1', 'features', FALSE),
    ('maintenance_mode', '0', 'system', FALSE),
    ('currency_symbol', '$', 'payment', TRUE),
    ('tax_rate', '10', 'payment', FALSE),
    ('booking_fee', '5', 'payment', FALSE),
    ('min_rental_days', '1', 'booking', TRUE),
    ('max_rental_days', '30', 'booking', TRUE)
");

// ADD DEFAULT ADMIN
$adminEmail = 'admin@gmail.com';
$res = $setup->query("SELECT * FROM users WHERE user_email = '$adminEmail'");
if ($res->num_rows === 0) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $setup->query("
        INSERT INTO users (first_name, last_name, user_email, password_hash, user_role)
        VALUES ('Admin', 'User', '$adminEmail', '$pass', 'admin')
    ");
}

// ADD EXAMPLE USERS
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

foreach ($exampleUsers as $user) {
    $email = $user['user_email'];
    $res = $setup->query("SELECT * FROM users WHERE user_email = '$email'");
    if ($res->num_rows === 0) {
        $pass = password_hash($user['password'], PASSWORD_DEFAULT);
        $setup->query("
            INSERT INTO users (
                first_name, last_name, user_email, phone_number, 
                address, country, city, state, pincode, 
                driving_licence_no, password_hash, user_role
            ) VALUES (
                '{$user['first_name']}', '{$user['last_name']}', '{$user['user_email']}', '{$user['phone_number']}',
                '{$user['address']}', '{$user['country']}', '{$user['city']}', '{$user['state']}', '{$user['pincode']}',
                '{$user['driving_licence_no']}', '$pass', 'user'
            )
        ");
    }
}

// ADD EXAMPLE CARS
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

// Clear existing cars first to avoid duplicates
$setup->query("DELETE FROM car_images WHERE car_id IN (SELECT car_id FROM cars WHERE car_type IN ('Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi Q7'))");
$setup->query("DELETE FROM cars WHERE car_type IN ('Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi Q7')");

foreach ($exampleCars as $car) {
    $setup->query("
        INSERT INTO cars (
            car_type, car_price_perday, year, location_id, car_class, car_image,
            brand_id, model_id, category_id, fuel_type_id, gear_type_id, featured,
            transmission, fuel_type, passengers, brand, model, type, image, price_per_day
        ) VALUES (
            '{$car['car_type']}', {$car['car_price_perday']}, {$car['year']}, {$car['location_id']},
            '{$car['car_class']}', '{$car['car_image']}', {$car['brand_id']}, {$car['model_id']},
            {$car['category_id']}, {$car['fuel_type_id']}, {$car['gear_type_id']}, {$car['featured']},
            '{$car['transmission']}', '{$car['fuel_type']}', {$car['passengers']}, '{$car['brand']}',
            '{$car['model']}', '{$car['type']}', '{$car['image']}', {$car['price_per_day']}
        )
    ");
    
    $carId = $setup->insert_id;
    
    // Add gallery images
    $setup->query("INSERT INTO car_images (car_id, image_path) VALUES
        ($carId, '{$car['image']}'),
        ($carId, 'assets/img/cars/gallery-1.jpg'),
        ($carId, 'assets/img/cars/gallery-2.jpg')
    ");
}

// ADD EXAMPLE RESERVATIONS
// First, get user IDs
$userQuery = $setup->query("SELECT user_id FROM users WHERE user_email IN ('john@example.com', 'jane@example.com')");
$userIds = [];
while ($row = $userQuery->fetch_assoc()) {
    $userIds[] = $row['user_id'];
}

// Get car IDs
$carQuery = $setup->query("SELECT car_id FROM cars WHERE car_type IN ('Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi Q7')");
$carIds = [];
while ($row = $carQuery->fetch_assoc()) {
    $carIds[] = $row['car_id'];
}

// Clear existing reservations for these users and cars
if (!empty($userIds) && !empty($carIds)) {
    $userIdsStr = implode(',', $userIds);
    $carIdsStr = implode(',', $carIds);
    $setup->query("DELETE FROM reservations WHERE user_id IN ($userIdsStr) AND car_id IN ($carIdsStr)");
    
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
            'user_id' => $userIds[1],
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
    
    foreach ($exampleReservations as $reservation) {
        $setup->query("
            INSERT INTO reservations (
                user_id, car_id, start_date, end_date,
                pickup_location, return_location, total_price, status
            ) VALUES (
                {$reservation['user_id']}, {$reservation['car_id']}, '{$reservation['start_date']}', '{$reservation['end_date']}',
                '{$reservation['pickup_location']}', '{$reservation['return_location']}', {$reservation['total_price']}, '{$reservation['status']}'
            )
        ");
    }
}

// Create directories for car images if they don't exist
$directories = ['assets/img/cars', 'assets/img/users', 'assets/img/cars/gallery'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

echo "✅ Database setup complete. Admin: admin@gmail.com / Pass: admin123<br>";
echo "✅ Example users created: john@example.com and jane@example.com (password: password123)<br>";
echo "✅ Example cars and reservations added.";

// Replace the success messages at the end with Bootstrap styling
echo "<div class='alert alert-success'>Database setup completed successfully!</div>";
echo "<div class='mt-3'><a href='../index.php' class='btn btn-primary'>Go to Homepage</a></div>";
echo "</div></div></div></div></div>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body></html>";
?>
