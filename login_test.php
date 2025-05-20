<?php
// Start session
session_start();

// Include database connection
require_once 'backend/db_connect.php';

// Function to check if a user exists
function userExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to create a test user
function createTestUser() {
    global $conn;
    
    $name = 'Test User';
    $email = 'test@example.com';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $role = 'user';
    
    // Check if user already exists
    if (userExists($email)) {
        // Update existing user
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        return $stmt->execute();
    } else {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        return $stmt->execute();
    }
}

// Function to create an admin user
function createAdminUser() {
    global $conn;
    
    $name = 'Admin User';
    $email = 'admin@example.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'admin';
    
    // Check if user already exists
    if (userExists($email)) {
        // Update existing user
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        return $stmt->execute();
    } else {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        return $stmt->execute();
    }
}

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
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
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #e2e8f0; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Login Test</h1>";

// Check database connection
echo "<h2>Database Connection</h2>";
if ($conn) {
    echo "<div class='message success'>Database connection successful</div>";
} else {
    echo "<div class='message error'>Database connection failed</div>";
    exit;
}

// Check users table
echo "<h2>Users Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<div class='message success'>Users table exists</div>";
    
    // Check users count
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<div class='message info'>Users in database: " . $row['count'] . "</div>";
    
    // Show sample users
    $result = $conn->query("SELECT id, name, email, role FROM users LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<h3>Sample Users</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='message error'>Users table does not exist</div>";
}

// Create test users if requested
if (isset($_GET['create_users'])) {
    echo "<h2>Creating Test Users</h2>";
    
    if (createTestUser()) {
        echo "<div class='message success'>Test user created/updated successfully</div>";
        echo "<div class='message info'>Test user credentials: test@example.com / password123</div>";
    } else {
        echo "<div class='message error'>Failed to create test user</div>";
    }
    
    if (createAdminUser()) {
        echo "<div class='message success'>Admin user created/updated successfully</div>";
        echo "<div class='message info'>Admin credentials: admin@example.com / admin123</div>";
    } else {
        echo "<div class='message error'>Failed to create admin user</div>";
    }
}

// Test login form
echo "<h2>Login Form Test</h2>";
echo "<form action='backend/process_login.php' method='post' style='background: #f8fafc; padding: 20px; border-radius: 8px;'>
        <div style='margin-bottom: 15px;'>
            <label style='display: block; margin-bottom: 5px;'>Email:</label>
            <input type='email' name='email' value='test@example.com' style='width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display: block; margin-bottom: 5px;'>Password:</label>
            <input type='password' name='password' value='password123' style='width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label>
                <input type='checkbox' name='remember'> Remember me
            </label>
        </div>
        <button type='submit' style='padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;'>Test Login</button>
      </form>";

echo "<div style='margin-top: 20px;'>
        <a href='?create_users=1' class='action-btn'>Create/Reset Test Users</a>
        <a href='login.php' class='action-btn'>Go to Login Page</a>
        <a href='index.php' class='action-btn'>Go to Home Page</a>
      </div>";

echo "</div></body></html>";
?>
