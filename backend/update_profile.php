<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Collect POST data safely
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$user_name  = trim($_POST['user_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$email      = trim($_POST['email'] ?? '');
$address    = trim($_POST['address'] ?? '');
$country    = trim($_POST['country'] ?? '');
$state      = trim($_POST['state'] ?? '');
$city       = trim($_POST['city'] ?? '');
$zipcode    = trim($_POST['zipcode'] ?? '');

// Start building SQL query
$sql = "UPDATE users SET 
  first_name = ?, 
  last_name  = ?, 
  user_name  = ?, 
  user_phone = ?, 
  user_email = ?, 
  address    = ?, 
  country    = ?, 
  state      = ?, 
  city       = ?, 
  zipcode    = ?
WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'ssssssssssi', 
    $first_name, $last_name, $user_name, $phone, $email,
    $address, $country, $state, $city, $zipcode,
    $user_id
);
$stmt->execute();

// Handle profile photo upload + crop if exists
if (!empty($_FILES['profile_photo']['tmp_name'])) {
    $cropX = (int)($_POST['crop_x'] ?? 0);
    $cropY = (int)($_POST['crop_y'] ?? 0);
    $cropW = (int)($_POST['crop_width'] ?? 0);
    $cropH = (int)($_POST['crop_height'] ?? 0);

    $imageTmp = $_FILES['profile_photo']['tmp_name'];
    $imageExt = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
    
    // Only allow jpg, jpeg, png
    if (in_array($imageExt, ['jpg', 'jpeg', 'png'])) {
        $imgResource = match($imageExt) {
            'jpg', 'jpeg' => imagecreatefromjpeg($imageTmp),
            'png'         => imagecreatefrompng($imageTmp),
        };

        if ($imgResource) {
            $cropped = imagecrop($imgResource, [
                'x' => $cropX, 
                'y' => $cropY, 
                'width'  => $cropW, 
                'height' => $cropH
            ]);

            if ($cropped) {
                $fileName = 'user_' . $user_id . '_' . time() . '.jpg';
                $savePath = __DIR__ . '/../assets/img/profiles/' . $fileName;
                imagejpeg($cropped, $savePath, 90);

                // Update profile_photo field
                $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
                $stmt->bind_param('si', $fileName, $user_id);
                $stmt->execute();

                imagedestroy($cropped);
            }
            imagedestroy($imgResource);
        }
    }
}

// Done, go back to settings
header('Location: ../user_settings.php?success=1');
exit;
