<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

$action = $_GET['action'] ?? '';
$user_id = (int)($_GET['user_id'] ?? 0);

if (!$action || !$user_id) {
    die('Invalid request.');
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        header("Location: admin_users.php?success=User deleted successfully.");
        exit;
    } else {
        die('Failed to delete user.');
    }
}
?>
