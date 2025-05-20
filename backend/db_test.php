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

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #2563eb; }
        .test-item { margin-bottom: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #dcfce7; color: #166534; }
        .error { background-color: #fee2e2; color: #991b1b; }
        .code { font-family: monospace; background: #f1f5f9; padding: 10px; border-radius: 5px; overflow: auto; }
        .action-btn { display: inline-block; margin: 10px 0; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; }
        .action-btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Connection Test</h1>";

// Test 1: Basic Connection
echo "<h2>Test 1: Basic Connection</h2>";
try {
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<div class='test-item success'>Successfully connected to MySQL server</div>";
} catch (Exception $e) {
    echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='test-item'>
            <p>Possible solutions:</p>
            <ul>
                <li>Make sure MySQL server is running</li>
                <li>Check username and password</li>
                <li>Verify server hostname</li>
            </ul>
          </div>";
    goto end_tests;
}

// Test 2: Database Selection
echo "<h2>Test 2: Database Selection</h2>";
try {
    if (!$conn->select_db($dbname)) {
        throw new Exception("Database selection failed: " . $conn->error);
    }
    echo "<div class='test-item success'>Successfully selected database '$dbname'</div>";
} catch (Exception $e) {
    echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='test-item'>
            <p>Possible solutions:</p>
            <ul>
                <li>Create the database using: <code>CREATE DATABASE $dbname;</code></li>
                <li>Run the database setup script: <a href='db_setup_fix.php' class='action-btn'>Run Setup</a></li>
            </ul>
          </div>";
    goto end_tests;
}

// Test 3: Table Check
echo "<h2>Test 3: Table Check</h2>";
$tables = ['users', 'user_tokens'];
foreach ($tables as $table) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            throw new Exception("Table '$table' does not exist");
        }
        echo "<div class='test-item success'>Table '$table' exists</div>";
    } catch (Exception $e) {
        echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
        echo "<div class='test-item'>
                <p>Possible solutions:</p>
                <ul>
                    <li>Run the database setup script: <a href='db_setup_fix.php' class='action-btn'>Run Setup</a></li>
                </ul>
              </div>";
    }
}

// Test 4: Query Test
echo "<h2>Test 4: Query Test</h2>";
try {
    $result = $conn->query("SELECT * FROM users LIMIT 1");
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    if ($result->num_rows == 0) {
        echo "<div class='test-item error'>No users found in the database</div>";
        echo "<div class='test-item'>
                <p>Possible solutions:</p>
                <ul>
                    <li>Run the database setup script to create sample users: <a href='db_setup_fix.php' class='action-btn'>Run Setup</a></li>
                </ul>
              </div>";
    } else {
        $user = $result->fetch_assoc();
        echo "<div class='test-item success'>Successfully queried users table</div>";
        echo "<div class='test-item'>Sample user: ID: " . $user['id'] . ", Name: " . $user['name'] . ", Email: " . $user['email'] . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='test-item'>
            <p>Possible solutions:</p>
            <ul>
                <li>Check table structure</li>
                <li>Run the database setup script: <a href='db_setup_fix.php' class='action-btn'>Run Setup</a></li>
            </ul>
          </div>";
}

// Test 5: Login Test
echo "<h2>Test 5: Login Test</h2>";
try {
    $testEmail = 'test@example.com';
    $testPassword = 'password123';
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $testEmail);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "<div class='test-item error'>Test user not found</div>";
        echo "<div class='test-item'>
                <p>Possible solutions:</p>
                <ul>
                    <li>Run the database setup script to create test user: <a href='db_setup_fix.php' class='action-btn'>Run Setup</a></li>
                </ul>
              </div>";
    } else {
        $user = $result->fetch_assoc();
        if (password_verify($testPassword, $user['password'])) {
            echo "<div class='test-item success'>Password verification successful</div>";
            echo "<div class='test-item'>You can login with: test@example.com / password123</div>";
        } else {
            echo "<div class='test-item error'>Password verification failed</div>";
            echo "<div class='test-item'>
                    <p>Possible solutions:</p>
                    <ul>
                        <li>Run the database setup script to reset test user: <a href='db_setup_fix.php' class='action-btn'>Run Setup</a></li>
                    </ul>
                  </div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
}

end_tests:
// Close connection if it exists
if (isset($conn) && $conn) {
    $conn->close();
}

echo "<div style='margin-top: 20px;'>
        <a href='db_setup_fix.php' class='action-btn'>Run Database Setup</a>
        <a href='../login.php' class='action-btn'>Go to Login Page</a>
        <a href='../index.php' class='action-btn'>Go to Home Page</a>
      </div>";

echo "</div></body></html>";
?>
