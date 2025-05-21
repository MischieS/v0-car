<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for remember_me cookie if user is not logged in
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    // Include database connection
    require_once __DIR__ . '/../../backend/db_connect.php';
    
    // Parse cookie value
    list($user_id, $token) = explode(':', $_COOKIE['remember_me']);
    
    // Verify user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify token
        $expected_token = hash('sha256', $user["id"] . $user["email"] . $user["password"] . $_SERVER['HTTP_USER_AGENT']);
        
        if (hash_equals($expected_token, $token)) {
            // Set session variables
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];
            
            // Renew the cookie
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            setcookie("remember_me", $_COOKIE['remember_me'], $expires, "/", "", false, true);
        }
    }
}
?>
<header class="header">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg header-nav">
            <div class="navbar-header">
                <a id="mobile_btn" href="javascript:void(0);">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>
                <a href="index.php" class="navbar-brand logo">
                    <img src="assets/img/logo.png" class="img-fluid" alt="Logo">
                </a>
            </div>
            <div class="main-menu-wrapper">
                <div class="menu-header">
                    <a href="index.php" class="menu-logo">
                        <img src="assets/img/logo.png" class="img-fluid" alt="Logo">
                    </a>
                    <a id="menu_close" class="menu-close" href="javascript:void(0);">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                <ul class="main-nav">
                    <li class="active">
                        <a href="index.php">Home</a>
                    </li>
                    <li>
                        <a href="booking_list.php">Cars</a>
                    </li>
                    <li>
                        <a href="about_us.php">About Us</a>
                    </li>
                    <li>
                        <a href="faq.php">FAQ</a>
                    </li>
                    <li>
                        <a href="contact_us.php">Contact Us</a>
                    </li>
                </ul>
            </div>
            <ul class="nav header-navbar-rht">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <li class="nav-item dropdown has-arrow logged-item">
                        <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <?php if ($_SESSION['user_role'] === 'admin') { ?>
                                <a class="dropdown-item" href="admin_dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                <a class="dropdown-item" href="admin_users.php">
                                    <i class="fas fa-users"></i> Users
                                </a>
                                <a class="dropdown-item" href="admin_cars.php">
                                    <i class="fas fa-car"></i> Cars
                                </a>
                                <a class="dropdown-item" href="admin_bookings.php">
                                    <i class="fas fa-calendar-check"></i> Bookings
                                </a>
                                <a class="dropdown-item" href="admin_settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            <?php } else { ?>
                                <a class="dropdown-item" href="user_dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                <a class="dropdown-item" href="user_bookings.php">
                                    <i class="fas fa-calendar-check"></i> My Bookings
                                </a>
                                <a class="dropdown-item" href="user_settings.php">
                                    <i class="fas fa-cog"></i> Profile Settings
                                </a>
                            <?php } ?>
                            <a class="dropdown-item" href="backend/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link header-login" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</header>
