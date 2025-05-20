<?php
// db_connect.php - Enhanced with better error handling and debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Change error reporting mode

// Database credentials
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'car_rental_db';

// Error logging function
function logDatabaseError($message) {
    $logFile = __DIR__ . '/database_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function connectDatabase(): ?mysqli {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create connection
        $conn = new mysqli($servername, $username, $password);
        
        // Check connection
        if ($conn->connect_error) {
            logDatabaseError("Connection failed: " . $conn->connect_error);
            return null;
        }
        
        // Check if database exists
        $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        if ($result->num_rows == 0) {
            // Database doesn't exist, create it
            if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
                logDatabaseError("Failed to create database: " . $conn->error);
                return null;
            }
        }
        
        // Select database
        if (!$conn->select_db($dbname)) {
            logDatabaseError("Failed to select database: " . $conn->error);
            return null;
        }
        
        // Check if users table exists
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
                logDatabaseError("Failed to create users table: " . $conn->error);
                return null;
            }
            
            // Create default admin user
            $adminName = 'Admin User';
            $adminEmail = 'admin@example.com';
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $adminRole = 'admin';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $adminName, $adminEmail, $adminPassword, $adminRole);
            
            if (!$stmt->execute()) {
                logDatabaseError("Failed to create admin user: " . $stmt->error);
            } else {
                logDatabaseError("Created default admin user: admin@example.com / admin123");
            }
        }
        
        // Check if user_tokens table exists
        $result = $conn->query("SHOW TABLES LIKE 'user_tokens'");
        if ($result->num_rows == 0) {
            // Create user_tokens table
            $sql = "CREATE TABLE IF NOT EXISTS `user_tokens` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `expires` DATETIME NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if (!$conn->query($sql)) {
                logDatabaseError("Failed to create user_tokens table: " . $conn->error);
            }
        }
        
        return $conn;
    } catch (Exception $e) {
        logDatabaseError("Exception: " . $e->getMessage());
        return null;
    }
}

// Connect to database
$conn = connectDatabase();

// Check if connection failed
if ($conn === null) {
    // For non-AJAX requests, redirect to an error page
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        if (!headers_sent()) {
            header("Location: ../db_error.php");
            exit;
        }
    }
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
