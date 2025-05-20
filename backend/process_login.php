<?php
// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Simple file-based logging function (no database)
function logLoginActivity($message) {
    $logFile = __DIR__ . '/login_activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logMessage = "[$timestamp] [$ip] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data and sanitize
        $email = isset($_POST["email"]) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : '';
        $password = isset($_POST["password"]) ? $_POST["password"] : '';
        $remember = isset($_POST["remember"]) ? true : false;
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["login_error"] = "Invalid email format";
            header("Location: ../login.php");
            exit();
        }
        
        // Validate password
        if (empty($password)) {
            $_SESSION["login_error"] = "Password is required";
            header("Location: ../login.php");
            exit();
        }
        
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            logLoginActivity("Prepare failed: " . $conn->error);
            $_SESSION["login_error"] = "Database error. Please try again later.";
            header("Location: ../login.php");
            exit();
        }
        
        $stmt->bind_param("s", $email);
        
        if (!$stmt->execute()) {
            logLoginActivity("Execute failed: " . $stmt->error);
            $_SESSION["login_error"] = "Database error. Please try again later.";
            header("Location: ../login.php");
            exit();
        }
        
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user["password"])) {
                // Set session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];
                
                // Log successful login (file-based, not database)
                logLoginActivity("Successful login: " . $user["email"]);
                
                // Set remember me cookie if checked (cookie-only approach, no database)
                if ($remember) {
                    // Create a simple token with user ID and a hash
                    $token = $user["id"] . ':' . hash('sha256', $user["id"] . $user["email"] . $user["password"] . $_SERVER['HTTP_USER_AGENT']);
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Set cookie
                    setcookie("remember_me", $token, $expires, "/", "", false, true);
                }
                
                // Redirect based on role
                if ($user["role"] === "admin") {
                    header("Location: ../admin_dashboard.php");
                } else {
                    header("Location: ../user_dashboard.php");
                }
                exit();
            } else {
                logLoginActivity("Failed login (wrong password): " . $email);
                $_SESSION["login_error"] = "Invalid email or password";
                header("Location: ../login.php");
                exit();
            }
        } else {
            logLoginActivity("Failed login (user not found): " . $email);
            $_SESSION["login_error"] = "Invalid email or password";
            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        logLoginActivity("Exception: " . $e->getMessage());
        $_SESSION["login_error"] = "An error occurred. Please try again later.";
        header("Location: ../login.php");
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header("Location: ../login.php");
    exit();
}
?>
