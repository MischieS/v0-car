<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Log the activity
$activityDesc = "Logged out from all devices";
$activityType = "security";

$logStmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, activity_description) VALUES (?, ?, ?)");
$logStmt->bind_param('iss', $user_id, $activityType, $activityDesc);
$logStmt->execute();

// Update session token in database to invalidate all sessions
$token = bin2hex(random_bytes(32));
$stmt = $conn->prepare("UPDATE users SET session_token = ? WHERE user_id = ?");
$stmt->bind_param('si', $token, $user_id);
$stmt->execute();

// Destroy the current session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../login.php?message=Logged+out+from+all+devices');
exit;
