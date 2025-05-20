<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];

// 1. Update profile fields
$first  = trim($_POST['first_name']);
$last   = trim($_POST['last_name']);
$email  = trim($_POST['email']);
$phone  = trim($_POST['phone_number']);
$addr   = trim($_POST['address']);
$city   = trim($_POST['city']);
$zip    = trim($_POST['pincode']);
$country= trim($_POST['country']);

$stmt = $conn->prepare("
    UPDATE users SET
      first_name = ?, last_name = ?, user_email = ?, phone_number = ?,
      address = ?, city = ?, pincode = ?, country = ?
    WHERE user_id = ?
");
$stmt->bind_param('ssssssssi',
  $first, $last, $email, $phone,
  $addr, $city, $zip, $country,
  $user_id
);
$stmt->execute();

// 2. Handle password change
if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
    $cur = $_POST['current_password'];
    $new = $_POST['new_password'];
    $check = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $check->bind_param('i', $user_id);
    $check->execute();
    $res = $check->get_result();
    $row = $res->fetch_assoc();

    if (!password_verify($cur, $row['password_hash'])) {
        die('❌ Current password is incorrect.');
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $updPass = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $updPass->bind_param('si', $newHash, $user_id);
    $updPass->execute();
}

header('Location: ../user_settings.php?success=1');
exit;
