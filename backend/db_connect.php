<?php
// db_connect.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Better error reporting

// Database credentials
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'car_rental_db';

// Function to log database errors
function logDbError($message) {
    $logFile = __DIR__ . '/db_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Try to connect to the database
try {
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        logDbError("Connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if database exists, create if not
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($result->num_rows == 0) {
        // Database doesn't exist, create it
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
            logDbError("Failed to create database: " . $conn->error);
            die("Failed to create database: " . $conn->error);
        }
    }
    
    // Select database
    if (!$conn->select_db($dbname)) {
        logDbError("Failed to select database: " . $conn->error);
        die("Failed to select database: " . $conn->error);
    }
    
    // Check if users table exists, create if not
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
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
            logDbError("Failed to create users table: " . $conn->error);
            die("Failed to create users table: " . $conn->error);
        }
        
        // Create default admin user
        $adminName = 'Admin User';
        $adminEmail = 'admin@example.com';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $adminRole = 'admin';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $adminName, $adminEmail, $adminPassword, $adminRole);
        
        if (!$stmt->execute()) {
            logDbError("Failed to create admin user: " . $stmt->error);
        }
        
        // Create test user
        $testName = 'Test User';
        $testEmail = 'test@example.com';
        $testPassword = password_hash('password123', PASSWORD_DEFAULT);
        $testRole = 'user';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $testName, $testEmail, $testPassword, $testRole);
        
        if (!$stmt->execute()) {
            logDbError("Failed to create test user: " . $stmt->error);
        }
    }
} catch (Exception $e) {
    logDbError("Exception: " . $e->getMessage());
    die("Database connection error: " . $e->getMessage());
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
