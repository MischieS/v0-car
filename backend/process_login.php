<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (comment out in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Function to log errors to a file
function logLoginError($message) {
    $logFile = __DIR__ . '/login_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Include database connection
require_once 'db_connect.php';

// Check if database connection failed
if ($conn === null) {
    logLoginError("Database connection failed");
    $_SESSION["login_error"] = "Database error. Please try again later.";
    header("Location: ../login.php");
    exit();
}

// Include activity logging function if available
if (file_exists(__DIR__ . '/log_activity.php')) {
    require_once __DIR__ . '/log_activity.php';
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
        $password = isset($_POST["password"]) ? $_POST["password"] : '';
        $remember = isset($_POST["remember"]) ? true : false;
        
        // Log attempt (for debugging)
        logLoginError("Login attempt for email: $email");
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $_SESSION["login_error"] = "Please fill in all fields";
            header("Location: ../login.php");
            exit();
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["login_error"] = "Invalid email format";
            header("Location: ../login.php");
            exit();
        }
        
        // Check if users table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
        if ($tableCheck->num_rows == 0) {
            logLoginError("Users table does not exist");
            $_SESSION["login_error"] = "Database configuration error. Please contact support.";
            header("Location: ../login.php");
            exit();
        }
        
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            logLoginError("Prepare failed: " . $conn->error);
            $_SESSION["login_error"] = "Database error. Please try again later.";
            header("Location: ../login.php");
            exit();
        }
        
        $stmt->bind_param("s", $email);
        
        if (!$stmt->execute()) {
            logLoginError("Execute failed: " . $stmt->error);
            $_SESSION["login_error"] = "Database error. Please try again later.";
            header("Location: ../login.php");
            exit();
        }
        
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Log user data (for debugging)
            logLoginError("User found: " . json_encode($user));
            
            // Check if password field exists in the database
            if (!isset($user["password"])) {
                logLoginError("Password field not found in user record");
                $_SESSION["login_error"] = "Account configuration error. Please contact support.";
                header("Location: ../login.php");
                exit();
            }
            
            // Verify password
            if (password_verify($password, $user["password"])) {
                // Set session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];
                
                // Log successful login
                logLoginError("Login successful for user ID: " . $user["id"]);
                
                // Log the successful login activity if function exists
                if (function_exists('logActivity')) {
                    logActivity($user["id"], 'login', 'User logged in successfully');
                }
                
                // Set remember me cookie if checked
                if ($remember) {
                    // Check if user_tokens table exists
                    $tableCheck = $conn->query("SHOW TABLES LIKE 'user_tokens'");
                    if ($tableCheck->num_rows == 0) {
                        // Create user_tokens table
                        $sql = "CREATE TABLE IF NOT EXISTS `user_tokens` (
                            `id` INT(11) NOT NULL AUTO_INCREMENT,
                            `user_id` INT(11) NOT NULL,
                            `token` VARCHAR(255) NOT NULL,
                            `expires` DATETIME NOT NULL,
                            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`),
                            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                        
                        if (!$conn->query($sql)) {
                            logLoginError("Failed to create user_tokens table: " . $conn->error);
                        }
                    }
                    
                    // Generate a secure token
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                    if ($stmt) {
                        $expiryDate = date('Y-m-d H:i:s', $expires);
                        $stmt->bind_param("iss", $user["id"], $token, $expiryDate);
                        $stmt->execute();
                        
                        // Set cookie
                        setcookie("remember_token", $token, $expires, "/", "", false, true);
                    }
                }
                
                // Redirect based on role
                if ($user["role"] === "admin") {
                    header("Location: ../admin_dashboard.php");
                } else {
                    header("Location: ../user_dashboard.php");
                }
                exit();
            } else {
                logLoginError("Password verification failed for email: $email");
                $_SESSION["login_error"] = "Invalid email or password";
                header("Location: ../login.php");
                exit();
            }
        } else {
            logLoginError("User not found for email: $email");
            $_SESSION["login_error"] = "Invalid email or password";
            header("Location: ../login.php");
            exit();
        }
    } catch (Exception $e) {
        logLoginError("Exception: " . $e->getMessage());
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
