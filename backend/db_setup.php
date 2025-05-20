<?php
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

// LOCATIONS TABLE
$setup->query("CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB");

$setup->query("INSERT IGNORE INTO locations (location_name) VALUES
    ('Istanbul Office')");

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

// ADD SAMPLE CAR
$res = $setup->query("SELECT * FROM cars WHERE car_type = 'Sample Car'");
if ($res->num_rows === 0) {
    $setup->query("
        INSERT INTO cars (
            car_type, car_price_perday, year,
            location_id, car_class, car_image,
            brand_id, model_id, category_id,
            fuel_type_id, gear_type_id
        ) VALUES (
            'Sample Car', 89.99, 'automatic', 2023,
            1, 'Standard', 'sample.jpg',
            1, 1, 1,
            1, 2
        )
    ");
    $sampleCarId = $setup->insert_id;

    // Add gallery images
    $setup->query("INSERT INTO car_images (car_id, image_path) VALUES
        ($sampleCarId, 'sample_gallery1.jpg'),
        ($sampleCarId, 'sample_gallery2.jpg')
    ");
}

echo "✅ Database setup complete. Admin: admin@gmail.com / Pass: admin123";
?>
