<?php
session_start();
require_once 'db_connect.php';
require_once 'log_activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['user_email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($email) || empty($password)) {
        header('Location: ../login.php?error=Please fill in all fields');
        exit;
    }
    
    // Get user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['user_role'] = $user['user_role'];
            
            // Generate a unique session ID
            $session_id = bin2hex(random_bytes(16));
            $_SESSION['session_id'] = $session_id;
            
            // Store session in database if remember me is checked
            if ($remember) {
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('issss', $user['user_id'], $session_id, $ip_address, $user_agent, $expiry);
                $stmt->execute();
                
                // Set cookie for 30 days
                setcookie('remember_token', $session_id, time() + (86400 * 30), '/');
            }
            
            // Log the login activity
            logUserActivity($user['user_id'], 'login', 'User logged in successfully');
            
            // Redirect based on role
            if ($user['user_role'] === 'admin') {
                header('Location: ../admin_dashboard.php');
            } else {
                header('Location: ../user_dashboard.php');
            }
            exit;
        } else {
            // Log failed login attempt
            if (isset($user['user_id'])) {
                logUserActivity($user['user_id'], 'login_failed', 'Failed login attempt - incorrect password');
            }
            header('Location: ../login.php?error=Invalid email or password');
            exit;
        }
    } else {
        // Log failed login attempt for non-existent user
        logUserActivity(0, 'login_failed', 'Failed login attempt - user not found: ' . $email);
        header('Location: ../login.php?error=Invalid email or password');
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
?>
