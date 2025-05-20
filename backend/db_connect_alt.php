<?php
// Alternative database connection file with multiple connection methods
// This file tries different connection approaches to help diagnose issues

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log errors
function logDbError($message) {
    $logFile = __DIR__ . '/db_connection_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Database credentials - MODIFY THESE AS NEEDED
$servername = 'localhost'; // Try '127.0.0.1' if 'localhost' doesn't work
$username   = 'root';      // Default MySQL username
$password   = '';          // Default empty password for XAMPP/WAMP
$dbname     = 'car_rental_db';

// Connection function that tries multiple methods
function getDbConnection() {
    global $servername, $username, $password, $dbname;
    
    // Method 1: Standard mysqli connection
    try {
        $conn = new mysqli($servername, $username, $password);
        if (!$conn->connect_error) {
            if ($conn->select_db($dbname)) {
                return $conn;
            } else {
                logDbError("Method 1: Database selection failed: " . $conn->error);
            }
        } else {
            logDbError("Method 1: Connection failed: " . $conn->connect_error);
        }
    } catch (Exception $e) {
        logDbError("Method 1 Exception: " . $e->getMessage());
    }
    
    // Method 2: Try with 127.0.0.1 instead of localhost
    try {
        $conn = new mysqli('127.0.0.1', $username, $password);
        if (!$conn->connect_error) {
            if ($conn->select_db($dbname)) {
                logDbError("Method 2 successful: Using 127.0.0.1 instead of localhost");
                return $conn;
            } else {
                logDbError("Method 2: Database selection failed: " . $conn->error);
            }
        } else {
            logDbError("Method 2: Connection failed: " . $conn->connect_error);
        }
    } catch (Exception $e) {
        logDbError("Method 2 Exception: " . $e->getMessage());
    }
    
    // Method 3: Try with PDO
    try {
        $dsn = "mysql:host=$servername;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Check if database exists, create if not
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        if (!$stmt->fetch()) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        }
        
        // Connect to the specific database
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
        logDbError("Method 3 successful: Using PDO connection");
        
        // Return a mysqli connection for compatibility
        $conn = new mysqli($servername, $username, $password, $dbname);
        return $conn;
    } catch (PDOException $e) {
        logDbError("Method 3 Exception: " . $e->getMessage());
    }
    
    // Method 4: Try with no password
    if ($password !== '') {
        try {
            $conn = new mysqli($servername, $username, '');
            if (!$conn->connect_error) {
                if ($conn->select_db($dbname)) {
                    logDbError("Method 4 successful: Connection with empty password");
                    return $conn;
                } else {
                    logDbError("Method 4: Database selection failed: " . $conn->error);
                }
            } else {
                logDbError("Method 4: Connection failed: " . $conn->connect_error);
            }
        } catch (Exception $e) {
            logDbError("Method 4 Exception: " . $e->getMessage());
        }
    }
    
    // All methods failed
    logDbError("All connection methods failed");
    return null;
}

// Get database connection
$conn = getDbConnection();

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
