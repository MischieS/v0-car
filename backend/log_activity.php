<?php
/**
 * Log user activity to the database
 * 
 * @param int $user_id User ID (0 for system or unknown users)
 * @param string $activity_type Type of activity (login, logout, profile_update, etc.)
 * @param string $activity_description Description of the activity
 * @return bool True if logged successfully, false otherwise
 */
function logUserActivity($user_id, $activity_type, $activity_description) {
    global $conn;
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Prepare and execute query
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('isss', $user_id, $activity_type, $activity_description, $ip_address);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to log activity: " . $stmt->error);
    }
    
    return $result;
}
?>
