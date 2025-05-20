<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    // Collect POST data safely
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $user_name  = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $user_phone = trim($_POST['user_phone'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? null);
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($user_name) || empty($user_email)) {
        header('Location: ../user_settings.php?error=Required+fields+cannot+be+empty');
        exit;
    }
    
    // Validate email format
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../user_settings.php?error=Invalid+email+format');
        exit;
    }
    
    // Check if username or email already exists (excluding current user)
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE (user_name = ? OR user_email = ?) AND user_id != ?");
    $checkStmt->bind_param('ssi', $user_name, $user_email, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        header('Location: ../user_settings.php?error=Username+or+email+already+exists');
        exit;
    }
    
    // Start building SQL query
    $sql = "UPDATE users SET 
      first_name = ?, 
      last_name = ?, 
      user_name = ?, 
      user_email = ?, 
      phone_number = ?";
    
    $params = [$first_name, $last_name, $user_name, $user_email, $user_phone];
    $types = 'sssss';
    
    // Add date of birth if provided
    if (!empty($date_of_birth)) {
        $sql .= ", date_of_birth = ?";
        $params[] = $date_of_birth;
        $types .= 's';
    }
    
    $sql .= " WHERE user_id = ?";
    $params[] = $user_id;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    // Handle profile photo upload if exists
    if (!empty($_POST['cropped_profile'])) {
        $img = $_POST['cropped_profile'];
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        
        $fileName = 'user_' . $user_id . '_' . time() . '.jpg';
        $filePath = __DIR__ . '/../assets/img/profiles/' . $fileName;
        
        if (file_put_contents($filePath, $data)) {
            // Update profile_photo field
            $photoStmt = $conn->prepare("UPDATE users SET user_profile_image = ? WHERE user_id = ?");
            $photoStmt->bind_param('si', $fileName, $user_id);
            $photoStmt->execute();
        }
    }
    
    // Log the activity
    $activityDesc = "Profile information updated";
    $activityType = "profile_update";
    
    $logStmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, activity_description) VALUES (?, ?, ?)");
    $logStmt->bind_param('iss', $user_id, $activityType, $activityDesc);
    $logStmt->execute();
    
    // Update session variables if username changed
    if ($_SESSION['user_name'] !== $user_name) {
        $_SESSION['user_name'] = $user_name;
    }
    
    header('Location: ../user_settings.php?success=1');
    exit;
}

// Handle address update
if (isset($_POST['update_address'])) {
    $address  = trim($_POST['address'] ?? '');
    $country  = trim($_POST['country'] ?? '');
    $state    = trim($_POST['state'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    
    $stmt = $conn->prepare("
        UPDATE users SET
          address = ?, 
          country = ?, 
          state = ?, 
          city = ?, 
          pincode = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param('sssssi', $address, $country, $state, $city, $zip_code, $user_id);
    
    if ($stmt->execute()) {
        // Log the activity
        $activityDesc = "Address information updated";
        $activityType = "profile_update";
        
        $logStmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, activity_description) VALUES (?, ?, ?)");
        $logStmt->bind_param('iss', $user_id, $activityType, $activityDesc);
        $logStmt->execute();
        
        header('Location: ../user_settings.php?success=1');
        exit;
    } else {
        header('Location: ../user_settings.php?error=Failed+to+update+address');
        exit;
    }
}

// If we get here, something went wrong
header('Location: ../user_settings.php?error=Invalid+request');
exit;
