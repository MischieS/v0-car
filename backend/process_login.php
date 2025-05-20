<?php
// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Include activity logging function if available
if (file_exists('log_activity.php')) {
    require_once 'log_activity.php';
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]) ? true : false;
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["login_error"] = "Invalid email format";
        header("Location: ../login.php");
        exit();
    }
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user["password"])) {
            // Set session variables
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];
            
            // Log the successful login activity if function exists
            if (function_exists('logActivity')) {
                logActivity($user["id"], 'login', 'User logged in successfully');
            }
            
            // Set remember me cookie if checked
            if ($remember) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store token in database
                $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user["id"], $token, date('Y-m-d H:i:s', $expires));
                $stmt->execute();
                
                // Set cookie
                setcookie("remember_token", $token, $expires, "/", "", false, true);
            }
            
            // Debug session
            // echo "<!-- Session set: " . print_r($_SESSION, true) . " -->";
            
            // Redirect based on role
            if ($user["role"] === "admin") {
                header("Location: ../admin_dashboard.php");
            } else {
                header("Location: ../user_dashboard.php");
            }
            exit();
        } else {
            $_SESSION["login_error"] = "Invalid password";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION["login_error"] = "Email not found";
        header("Location: ../login.php");
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header("Location: ../login.php");
    exit();
}
?>
