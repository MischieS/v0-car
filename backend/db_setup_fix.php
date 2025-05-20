<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'car_rental_db';

// Function to log messages
function logMessage($message, $type = 'info') {
    echo "<div class='message $type'>$message</div>";
}

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #2563eb; }
        .message { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .info { background-color: #dbeafe; color: #1e40af; }
        .success { background-color: #dcfce7; color: #166534; }
        .error { background-color: #fee2e2; color: #991b1b; }
        .warning { background-color: #fef3c7; color: #92400e; }
        .action-btn { display: inline-block; margin: 10px 0; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; }
        .action-btn:hover { background: #1d4ed8; }
        pre { background: #f1f5f9; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Setup</h1>";

try {
    // Create connection
    logMessage("Attempting to connect to MySQL server...");
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    logMessage("Connected to MySQL server successfully", "success");
    
    // Create database if it doesn't exist
    logMessage("Checking if database '$dbname' exists...");
    if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
        logMessage("Database created or already exists", "success");
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select database
    logMessage("Selecting database '$dbname'...");
    if ($conn->select_db($dbname)) {
        logMessage("Database selected successfully", "success");
    } else {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Create users table if it doesn't exist
    logMessage("Checking if 'users' table exists...");
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        logMessage("Creating 'users' table...");
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
        
        if ($conn->query($sql)) {
            logMessage("'users' table created successfully", "success");
        } else {
            throw new Exception("Error creating users table: " . $conn->error);
        }
        
        // Create default admin user
        logMessage("Creating default admin user...");
        $adminName = 'Admin User';
        $adminEmail = 'admin@example.com';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $adminRole = 'admin';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $adminName, $adminEmail, $adminPassword, $adminRole);
        
        if ($stmt->execute()) {
            logMessage("Default admin user created successfully", "success");
            logMessage("Admin credentials: admin@example.com / admin123", "info");
        } else {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    } else {
        logMessage("'users' table already exists", "info");
    }
    
    // Create user_tokens table if it doesn't exist
    logMessage("Checking if 'user_tokens' table exists...");
    $result = $conn->query("SHOW TABLES LIKE 'user_tokens'");
    if ($result->num_rows == 0) {
        logMessage("Creating 'user_tokens' table...");
        $sql = "CREATE TABLE `user_tokens` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `expires` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            logMessage("'user_tokens' table created successfully", "success");
            
            // Add foreign key if possible
            logMessage("Adding foreign key constraint...");
            $sql = "ALTER TABLE `user_tokens` 
                    ADD CONSTRAINT `fk_user_tokens_user_id` 
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
                    ON DELETE CASCADE;";
            
            if ($conn->query($sql)) {
                logMessage("Foreign key constraint added successfully", "success");
            } else {
                logMessage("Warning: Could not add foreign key constraint: " . $conn->error, "warning");
            }
        } else {
            throw new Exception("Error creating user_tokens table: " . $conn->error);
        }
    } else {
        logMessage("'user_tokens' table already exists", "info");
    }
    
    // Create test user for login testing
    logMessage("Creating test user for login testing...");
    $testName = 'Test User';
    $testEmail = 'test@example.com';
    $testPassword = password_hash('password123', PASSWORD_DEFAULT);
    $testRole = 'user';
    
    // Check if test user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $testName, $testEmail, $testPassword, $testRole);
        
        if ($stmt->execute()) {
            logMessage("Test user created successfully", "success");
            logMessage("Test user credentials: test@example.com / password123", "info");
        } else {
            logMessage("Warning: Could not create test user: " . $stmt->error, "warning");
        }
    } else {
        logMessage("Test user already exists", "info");
    }
    
    // Display database status
    logMessage("Database setup completed successfully", "success");
    
    echo "<h2>Database Status</h2>";
    
    // Check users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    logMessage("Users in database: " . $row['count'], "info");
    
    // Show sample users
    $result = $conn->query("SELECT id, name, email, role FROM users LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<h3>Sample Users</h3>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . ", Name: " . $row['name'] . ", Email: " . $row['email'] . ", Role: " . $row['role'] . "\n";
        }
        echo "</pre>";
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage(), "error");
}

echo "<div style='margin-top: 20px;'>
        <a href='../login.php' class='action-btn'>Go to Login Page</a>
        <a href='../index.php' class='action-btn'>Go to Home Page</a>
      </div>";

echo "</div></body></html>";
?>
