<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo "Access denied. Admin access required.";
    exit();
}

// Function to check table structure
function checkTableStructure($conn, $tableName) {
    $result = $conn->query("DESCRIBE $tableName");
    if (!$result) {
        return "Error: " . $conn->error;
    }
    
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    
    return $columns;
}

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to create test user
function createTestUser($conn) {
    // Check if test user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $testEmail = 'test@example.com';
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return "Test user already exists";
    }
    
    // Create test user
    $name = 'Test User';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $role = 'user';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $testEmail, $password, $role);
    
    if ($stmt->execute()) {
        return "Test user created successfully. Email: test@example.com, Password: password123";
    } else {
        return "Error creating test user: " . $stmt->error;
    }
}

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1, h2 { color: #2563eb; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .success { color: #16a34a; }
        .error { color: #dc2626; }
        .action-btn { display: inline-block; margin: 10px 0; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; }
        .action-btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Check</h1>";

// Check database connection
echo "<h2>Database Connection</h2>";
if ($conn->connect_error) {
    echo "<p class='error'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p class='success'>Connection successful</p>";
    
    // Check if users table exists
    echo "<h2>Users Table</h2>";
    if (tableExists($conn, 'users')) {
        echo "<p class='success'>Users table exists</p>";
        
        // Check users table structure
        $usersColumns = checkTableStructure($conn, 'users');
        echo "<h3>Table Structure</h3>";
        echo "<table>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Key</th>
                    <th>Default</th>
                    <th>Extra</th>
                </tr>";
        
        foreach ($usersColumns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for sample users
        echo "<h3>Sample Users</h3>";
        $result = $conn->query("SELECT id, name, email, role FROM users LIMIT 5");
        
        if ($result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>No users found in the database</p>";
            echo "<a href='?action=create_test_user' class='action-btn'>Create Test User</a>";
        }
    } else {
        echo "<p class='error'>Users table does not exist</p>";
    }
}

// Create test user if requested
if (isset($_GET['action']) && $_GET['action'] === 'create_test_user') {
    echo "<h2>Create Test User</h2>";
    echo "<p>" . createTestUser($conn) . "</p>";
}

echo "<p><a href='../admin_dashboard.php' class='action-btn'>Back to Admin Dashboard</a></p>";
echo "</div></body></html>";
?>
