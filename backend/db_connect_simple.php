<?php
// Simple database connection file - use this if you're having connection issues
// This file uses minimal code to connect to the database

// Database credentials - MODIFY THESE AS NEEDED
$servername = 'localhost'; // Try '127.0.0.1' if 'localhost' doesn't work
$username   = 'root';      // Default MySQL username
$password   = '';          // Default empty password for XAMPP/WAMP
$dbname     = 'car_rental_db';

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists
$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
if ($result->num_rows == 0) {
    // Create database
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
        die("Error creating database: " . $conn->error);
    }
}

// Select database
if (!$conn->select_db($dbname)) {
    die("Error selecting database: " . $conn->error);
}

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    // Create users table
    $sql = "CREATE TABLE `users` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(20) DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($sql)) {
        die("Error creating users table: " . $conn->error);
    }
    
    // Create default admin user
    $adminName = 'Admin User';
    $adminEmail = 'admin@example.com';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $adminRole = 'admin';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $adminName, $adminEmail, $adminPassword, $adminRole);
    $stmt->execute();
    
    // Create test user
    $testName = 'Test User';
    $testEmail = 'test@example.com';
    $testPassword = password_hash('password123', PASSWORD_DEFAULT);
    $testRole = 'user';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $testName, $testEmail, $testPassword, $testRole);
    $stmt->execute();
}

// Function to normalize mobile number (kept from original)
function normalizeMobile(string $raw): string
{
    $digits = preg_replace('/\D/', '', $raw);
    if (strlen($digits) === 10) {
        $digits = '90' . $digits;
    } elseif (!str_starts_with($digits, '90')) {
        $digits = '90' . $digits;
    }
    $digits = substr($digits, 0, 12);
    $formatted = '+' . substr($digits, 0, 2)
               . '(' . substr($digits, 2, 3) . ')'
               . substr($digits, 5);
    return preg_match('/^\+90$$\d{3}$$\d{7}$/', $formatted)
        ? $formatted
        : '';
}
?>
