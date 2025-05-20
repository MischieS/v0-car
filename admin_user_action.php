<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied.');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Add new user
if ($action === 'add') {
    // Validate required fields
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $user_role = trim($_POST['user_role'] ?? 'user');
    
    // Additional fields
    $phone_number = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    
    // Validation
    if (empty($user_name) || empty($user_email) || empty($password)) {
        die('Required fields are missing.');
    }
    
    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }
    
    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
    $check->bind_param('s', $user_email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        die('Email already exists.');
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (
            user_name, user_email, password_hash, user_role,
            phone_number, address, country, city, pincode
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        'sssssssss',
        $user_name,
        $user_email,
        $password_hash,
        $user_role,
        $phone_number,
        $address,
        $country,
        $city,
        $pincode
    );
    
    if ($stmt->execute()) {
        header("Location: admin_users.php?success=User created successfully.");
        exit;
    } else {
        die('Failed to create user: ' . $conn->error);
    }
}

// Delete user
if ($action === 'delete') {
    $user_id = (int)($_GET['user_id'] ?? 0);
    
    if (!$user_id) {
        die('Invalid request.');
    }
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        die('You cannot delete your own account.');
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        header("Location: admin_users.php?success=User deleted successfully.");
        exit;
    } else {
        die('Failed to delete user: ' . $conn->error);
    }
}

// If we get here, no valid action was specified
header("Location: admin_users.php");
exit;
?>
