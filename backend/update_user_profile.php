<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$fields = [];
$types = '';
$values = [];

// Clean input
$first     = trim($_POST['first_name'] ?? '');
$last      = trim($_POST['last_name'] ?? '');
$username  = trim($_POST['user_name'] ?? '');
$email     = trim($_POST['user_email'] ?? '');
$phone     = trim($_POST['user_phone'] ?? '');
$address   = trim($_POST['address'] ?? '');
$country   = trim($_POST['country'] ?? '');
$state     = trim($_POST['state'] ?? '');
$city      = trim($_POST['city'] ?? '');
$zip       = trim($_POST['zip_code'] ?? '');

// Required fields (you can expand validation)
if (!$first || !$last || !$email || !$username) {
    die('Missing required fields.');
}

// Append updates
$fields[] = "first_name = ?";     $types .= 's'; $values[] = $first;
$fields[] = "last_name = ?";      $types .= 's'; $values[] = $last;
$fields[] = "user_name = ?";      $types .= 's'; $values[] = $username;
$fields[] = "user_email = ?";     $types .= 's'; $values[] = $email;
$fields[] = "phone_number = ?";   $types .= 's'; $values[] = $phone;
$fields[] = "address = ?";        $types .= 's'; $values[] = $address;
$fields[] = "country = ?";        $types .= 's'; $values[] = $country;
$fields[] = "state = ?";          $types .= 's'; $values[] = $state;
$fields[] = "city = ?";           $types .= 's'; $values[] = $city;
$fields[] = "pincode = ?";        $types .= 's'; $values[] = $zip;

// Handle cropped base64 profile image
$base64Image = $_POST['cropped_profile'] ?? '';
if ($base64Image && preg_match('/^data:image//(/w+);base64,/', $base64Image, $type)) {
    $data = base64_decode(substr($base64Image, strpos($base64Image, ',') + 1));
    $ext  = strtolower($type[1]);
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        die('Invalid image format.');
    }
    $filename = 'profile_' . bin2hex(random_bytes(5)) . '.' . $ext;
    $path = __DIR__ . '/../assets/img/profiles/' . $filename;
    file_put_contents($path, $data);

    $fields[] = "user_profile_image = ?";
    $types   .= 's';
    $values[] = $filename;
}

// Handle password change (optional)
$newPass = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';
if ($newPass || $confirm) {
    if ($newPass !== $confirm) {
        die('Passwords do not match.');
    }
    if (strlen($newPass) < 6) {
        die('Password too short (min 6 chars).');
    }

    $hash = password_hash($newPass, PASSWORD_BCRYPT);
    $fields[] = "password_hash = ?";
    $types   .= 's';
    $values[] = $hash;
}

// Finalize and run query
$types .= 'i';
$values[] = $user_id;

$sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) die('Prepare failed: ' . $conn->error);
$stmt->bind_param($types, ...$values);
if (!$stmt->execute()) {
    die('Update failed: ' . $stmt->error);
}

header('Location: ../user_settings.php?success=1');
exit;
