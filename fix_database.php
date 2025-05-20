<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - MODIFY THESE IF NEEDED
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'car_rental_db';

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Quick Fix</title>
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
        <h1>Database Quick Fix</h1>";

// Function to log messages
function logMessage($message, $type = 'info') {
    echo "<div class='message $type'>$message</div>";
}

try {
    // Step 1: Try to connect to MySQL
    logMessage("Step 1: Connecting to MySQL server...");
    
    // Try localhost first
    try {
        $conn = new mysqli($servername, $username, $password);
        if ($conn->connect_error) {
            throw new Exception("Connection to localhost failed: " . $conn->connect_error);
        }
        logMessage("Connected to MySQL server at 'localhost' successfully", "success");
    } catch (Exception $e) {
        // Try 127.0.0.1 instead
        logMessage("Connection to localhost failed, trying 127.0.0.1...", "warning");
        try {
            $servername = '127.0.0.1';
            $conn = new mysqli($servername, $username, $password);
            if ($conn->connect_error) {
                throw new Exception("Connection to 127.0.0.1 failed: " . $conn->connect_error);
            }
            logMessage("Connected to MySQL server at '127.0.0.1' successfully", "success");
        } catch (Exception $e2) {
            // Try with empty password
            if ($password !== '') {
                logMessage("Connection to 127.0.0.1 failed, trying with empty password...", "warning");
                try {
                    $password = '';
                    $conn = new mysqli($servername, $username, $password);
                    if ($conn->connect_error) {
                        throw new Exception("Connection with empty password failed: " . $conn->connect_error);
                    }
                    logMessage("Connected to MySQL server with empty password successfully", "success");
                } catch (Exception $e3) {
                    throw new Exception("All connection attempts failed. Please check if MySQL is running and your credentials are correct.");
                }
            } else {
                throw new Exception("All connection attempts failed. Please check if MySQL is running and your credentials are correct.");
            }
        }
    }
    
    // Step 2: Create database if it doesn't exist
    logMessage("Step 2: Checking if database '$dbname' exists...");
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($result->num_rows == 0) {
        logMessage("Database '$dbname' does not exist, creating it...");
        if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
            logMessage("Database created successfully", "success");
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    } else {
        logMessage("Database '$dbname' already exists", "success");
    }
    
    // Step 3: Select database
    logMessage("Step 3: Selecting database '$dbname'...");
    if ($conn->select_db($dbname)) {
        logMessage("Database selected successfully", "success");
    } else {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Step 4: Create users table if it doesn't exist
    logMessage("Step 4: Checking if 'users' table exists...");
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
            logMessage("Warning: Could not create admin user: " . $stmt->error, "warning");
        }
        
        // Create test user
        logMessage("Creating test user...");
        $testName = 'Test User';
        $testEmail = 'test@example.com';
        $testPassword = password_hash('password123', PASSWORD_DEFAULT);
        $testRole = 'user';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $testName, $testEmail, $testPassword, $testRole);
        
        if ($stmt->execute()) {
            logMessage("Test user created successfully", "success");
            logMessage("Test user credentials: test@example.com / password123", "info");
        } else {
            logMessage("Warning: Could not create test user: " . $stmt->error, "warning");
        }
    } else {
        logMessage("'users' table already exists", "success");
    }
    
    // Step 5: Create user_tokens table if it doesn't exist
    logMessage("Step 5: Checking if 'user_tokens' table exists...");
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
            try {
                $sql = "ALTER TABLE `user_tokens` 
                        ADD CONSTRAINT `fk_user_tokens_user_id` 
                        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
                        ON DELETE CASCADE;";
                
                if ($conn->query($sql)) {
                    logMessage("Foreign key constraint added successfully", "success");
                } else {
                    logMessage("Warning: Could not add foreign key constraint: " . $conn->error, "warning");
                }
            } catch (Exception $e) {
                logMessage("Warning: Could not add foreign key constraint: " . $e->getMessage(), "warning");
            }
        } else {
            throw new Exception("Error creating user_tokens table: " . $conn->error);
        }
    } else {
        logMessage("'user_tokens' table already exists", "success");
    }
    
    // Step 6: Update db_connect.php with working settings
    logMessage("Step 6: Updating database connection settings...");
    $dbConnectPath = __DIR__ . '/backend/db_connect.php';
    $dbConnectSimplePath = __DIR__ . '/backend/db_connect_simple.php';
    
    if (file_exists($dbConnectPath)) {
        // Create backup
        copy($dbConnectPath, $dbConnectPath . '.bak');
        logMessage("Created backup of db_connect.php", "info");
        
        // Replace with simple version
        if (file_exists($dbConnectSimplePath)) {
            $dbConnectContent = file_get_contents($dbConnectSimplePath);
        } else {
            // Create simple version content
            $dbConnectContent = '<?php
// Simple database connection file - automatically generated by fix_database.php
// This file uses minimal code to connect to the database

// Database credentials
$servername = \'' . $servername . '\';
$username   = \'' . $username . '\';
$password   = \'' . $password . '\';
$dbname     = \'' . $dbname . '\';

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists
$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'$dbname\'");
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

// Function to normalize mobile number (kept from original)
function normalizeMobile(string $raw): string
{
    $digits = preg_replace(\'/\\D/\', \'\', $raw);
    if (strlen($digits) === 10) {
        $digits = \'90\' . $digits;
    } elseif (!str_starts_with($digits, \'90\')) {
        $digits = \'90\' . $digits;
    }
    $digits = substr($digits, 0, 12);
    $formatted = \'+\' . substr($digits, 0, 2)
               . \'(\' . substr($digits, 2, 3) . \')\'
               . substr($digits, 5);
    return preg_match(\'/^\\+90\$$\\d{3}\$$\\d{7}$/\', $formatted)
        ? $formatted
        : \'\';
}
?>';
        }
        
        if (file_put_contents($dbConnectPath, $dbConnectContent)) {
            logMessage("Updated db_connect.php with working settings", "success");
        } else {
            logMessage("Warning: Could not update db_connect.php. Please update it manually.", "warning");
        }
    } else {
        logMessage("Warning: db_connect.php not found. Please create it manually.", "warning");
    }
    
    // Step 7: Test connection with new settings
    logMessage("Step 7: Testing connection with new settings...");
    try {
        // Include the updated db_connect.php
        include_once($dbConnectPath);
        
        // Test query
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        logMessage("Connection test successful! Found " . $row['count'] . " users in the database.", "success");
    } catch (Exception $e) {
        logMessage("Warning: Connection test failed: " . $e->getMessage(), "warning");
    }
    
    // Final message
    logMessage("Database setup completed successfully!", "success");
    logMessage("You can now try logging in with these credentials:", "info");
    logMessage("Admin: admin@example.com / admin123", "info");
    logMessage("Test User: test@example.com / password123", "info");
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage(), "error");
    
    // Provide guidance based on the error
    if (strpos($e->getMessage(), "Connection") !== false) {
        logMessage("MySQL server connection failed. Please check:", "info");
        logMessage("1. Is MySQL server running?", "info");
        logMessage("2. Are your username and password correct?", "info");
        logMessage("3. Is MySQL configured to accept connections?", "info");
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            logMessage("For XAMPP/WAMP users:", "info");
            logMessage("1. Open XAMPP/WAMP control panel", "info");
            logMessage("2. Start MySQL service", "info");
            logMessage("3. Try again", "info");
        } else {
            logMessage("For Linux/Mac users:", "info");
            logMessage("1. Run 'sudo service mysql start' or 'sudo systemctl start mysql'", "info");
            logMessage("2. Try again", "info");
        }
    }
}

// Close connection if it exists
if (isset($conn) && $conn) {
    $conn->close();
}

echo "<div style='margin-top: 20px;'>
        <a href='db_troubleshooter.php' class='action-btn'>Run Full Troubleshooter</a>
        <a href='login.php' class='action-btn'>Go to Login Page</a>
        <a href='index.php' class='action-btn'>Go to Home Page</a>
      </div>";

echo "</div></body></html>";
?>
