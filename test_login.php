<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any existing session
session_unset();
session_destroy();
session_start();

// Include database connection
require_once 'backend/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create test user if it doesn't exist
function ensureTestUser($conn) {
    // Check if test user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $testEmail = 'test@example.com';
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Create test user
    $name = 'Test User';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $role = 'user';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $testEmail, $password, $role);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        return [
            'id' => $userId,
            'name' => $name,
            'email' => $testEmail,
            'role' => $role
        ];
    } else {
        return null;
    }
}

// Function to test login
function testLogin($conn, $email, $password) {
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user["password"])) {
            // Set session variables
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Password verification failed'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
}

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #2563eb; }
        .card { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .success { color: #16a34a; }
        .error { color: #dc2626; }
        pre { background: #f1f5f9; padding: 10px; border-radius: 5px; overflow: auto; }
        .action-btn { display: inline-block; margin: 10px 0; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; }
        .action-btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Test Login</h1>";

// Ensure test user exists
$testUser = ensureTestUser($conn);

if ($testUser) {
    echo "<div class='card'>
            <h2>Test User</h2>
            <p>A test user has been created with the following credentials:</p>
            <ul>
                <li><strong>Email:</strong> test@example.com</li>
                <li><strong>Password:</strong> password123</li>
            </ul>
          </div>";
    
    // Test login
    echo "<div class='card'>
            <h2>Login Test</h2>";
    
    $loginResult = testLogin($conn, 'test@example.com', 'password123');
    
    if ($loginResult['success']) {
        echo "<p class='success'>" . $loginResult['message'] . "</p>";
        echo "<h3>Session Variables</h3>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    } else {
        echo "<p class='error'>" . $loginResult['message'] . "</p>";
    }
    
    echo "</div>";
    
    // Password hash check
    echo "<div class='card'>
            <h2>Password Hash Check</h2>";
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $email = 'test@example.com';
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo "<p><strong>Stored Hash:</strong> " . $user['password'] . "</p>";
    echo "<p><strong>Verification Result:</strong> " . (password_verify('password123', $user['password']) ? "<span class='success'>Valid</span>" : "<span class='error'>Invalid</span>") . "</p>";
    
    echo "</div>";
} else {
    echo "<div class='card'>
            <p class='error'>Failed to create test user</p>
          </div>";
}

echo "<p><a href='login.php' class='action-btn'>Go to Login Page</a></p>";
echo "</div></body></html>";
?>
