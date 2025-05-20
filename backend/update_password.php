<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?error=Unauthorized');
    exit;
}

$user_id         = $_SESSION['user_id'];
$current         = trim($_POST['current_password'] ?? '');
$newPassword     = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

// Validate inputs
if (!$current || !$newPassword || !$confirmPassword) {
    header('Location: ../user_settings.php?error=All+fields+are+required');
    exit;
}

if ($newPassword !== $confirmPassword) {
    header('Location: ../user_settings.php?error=Passwords+do+not+match');
    exit;
}

// Password complexity validation
if (strlen($newPassword) < 8) {
    header('Location: ../user_settings.php?error=Password+must+be+at+least+8+characters+long');
    exit;
}

$uppercase = preg_match('/[A-Z]/', $newPassword);
$number    = preg_match('/[0-9]/', $newPassword);
$special   = preg_match('/[^a-zA-Z0-9]/', $newPassword);

if (!$uppercase || !$number || !$special) {
    header('Location: ../user_settings.php?error=Password+must+include+uppercase+letters,+numbers,+and+special+characters');
    exit;
}

// Fetch current hash
$stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || !password_verify($current, $user['password_hash'])) {
    header('Location: ../user_settings.php?error=Current+password+is+incorrect');
    exit;
}

// Hash and update
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
$update  = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
$update->bind_param('si', $newHash, $user_id);

if ($update->execute()) {
    // Log the password change activity
    $activityDesc = "Password changed successfully";
    $activityType = "password_change";
    
    $logStmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, activity_description) VALUES (?, ?, ?)");
    $logStmt->bind_param('iss', $user_id, $activityType, $activityDesc);
    $logStmt->execute();
    
    header('Location: ../user_settings.php?password_success=1');
    exit;
} else {
    header('Location: ../user_settings.php?error=Failed+to+update+password');
    exit;
}
