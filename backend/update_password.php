<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id         = $_SESSION['user_id'];
$current         = trim($_POST['current_password'] ?? '');
$newPassword     = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

if (!$current || !$newPassword || !$confirmPassword) {
    die('All fields are required.');
}

if ($newPassword !== $confirmPassword) {
    die('Passwords do not match.');
}

// Fetch current hash
$stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || !password_verify($current, $user['password_hash'])) {
    die('Current password is incorrect.');
}

// Hash and update
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
$update  = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
$update->bind_param('si', $newHash, $user_id);

if ($update->execute()) {
    header('Location: ../user_dashboard.php?message=Password+updated+successfully');
    exit;
} else {
    die('Failed to update password.');
}
