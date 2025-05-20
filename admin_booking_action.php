<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// 1) Get and validate inputs
$action = $_GET['action'] ?? '';
$res_id = (int)($_GET['res_id'] ?? 0);

if (!$action || !$res_id) {
    die('Invalid request.');
}

$validActions = ['cancel', 'complete'];
if (!in_array($action, $validActions)) {
    die('Invalid action.');
}

// 2) Check if reservation exists
$stmt = $conn->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
$stmt->bind_param('i', $res_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res->num_rows) {
    die('Reservation not found.');
}

// 3) Update reservation status
$newStatus = $action === 'cancel' ? 'cancelled' : 'completed';
$update = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
$update->bind_param('si', $newStatus, $res_id);

if ($update->execute()) {
    // 4) Redirect back with success message
    header("Location: admin_bookings.php?success=" . urlencode("Booking has been " . ucfirst($newStatus) . "."));
    exit;
} else {
    die('Failed to update booking.');
}
?>
