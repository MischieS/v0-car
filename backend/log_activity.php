<?php
/**
 * Helper function to log user activity
 * 
 * @param int $user_id User ID
 * @param string $activity_type Type of activity (login, logout, profile_update, etc.)
 * @param string $activity_description Description of the activity
 * @param string $ip_address IP address (optional, defaults to current user IP)
 * @return bool True on success, false on failure
 */
function log_user_activity($conn, $user_id, $activity_type, $activity_description, $ip_address = null) {
    if (!$ip_address) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("
        INSERT INTO user_activity 
            (user_id, activity_type, activity_description, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param('issss', $user_id, $activity_type, $activity_description, $ip_address, $user_agent);
    
    return $stmt->execute();
}
?>
