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
                   <img src="assets/img/logo.svg" class="img-fluid" alt="Logo">
               </a>
               <a href="index.php" class="navbar-brand logo-small">
                   <img src="assets/img/logo-small.png" class="img-fluid" alt="Logo">
               </a>					
           </div>
           <div class="main-menu-wrapper">
               <div class="menu-header">
                   <a href="index.php" class="menu-logo">
                       <img src="assets/img/logo.svg" class="img-fluid" alt="Logo">
                   </a>
                   <a id="menu_close" class="menu-close" href="javascript:void(0);"> <i class="fas fa-times"></i></a>
               </div>
               <ul class="main-nav">
                   <li class="has-submenu">
                       <a href="index.php">Home <i class="fas"></i></a>
                   </li>
                   <li class="has-submenu">
                       <a href="booking_list.php">Cars<i class="fas"></i></a>
                   </li>
                   <li class="has-submenu">
                       <a href="about_us.php">About Us <i class="fas"></i></a>
                   </li>
                   <li class="has-submenu">
                       <a href="faq.php">FAQ <i class="fas"></i></a>
                   </li>
                   <li><a href="contact_us.php">Contact Us</a></li>
                   <?php if (!isset($_SESSION["user_id"])): ?>
                   <li class="login-link">
                       <a href="register.php">Sign Up</a>
                   </li>
                   <li class="login-link">
                       <a href="login.php">Sign In</a>
                   </li>
                   <?php endif; ?>
               </ul>
           </div>
           <?php if (isset($_SESSION["user_id"])): ?>
               <ul class="nav header-navbar-rht">
                   <li class="nav-item dropdown has-arrow logged-item">
                       <a href="#" class="dropdown-toggle nav-link user-dropdown" data-bs-toggle="dropdown">
                           <?= htmlspecialchars($_SESSION["user_name"]) ?> <i class="fas fa-chevron-down ms-2"></i>
                       </a>
                       <div class="dropdown-menu dropdown-menu-end">
                           <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin"): ?>
                               <a class="dropdown-item" href="admin_dashboard.php">
                                   <i class="feather-shield me-2"></i> Admin Dashboard
                               </a>
                               <a class="dropdown-item" href="admin_users.php">
                                   <i class="feather-users me-2"></i> Manage Users
                               </a>
                               <a class="dropdown-item" href="admin_cars.php">
                                   <i class="feather-truck me-2"></i> Manage Cars
                               </a>
                               <a class="dropdown-item" href="admin_bookings.php">
                                   <i class="feather-calendar me-2"></i> Manage Bookings
                               </a>
                           <?php else: ?>
                               <a class="dropdown-item" href="user_dashboard.php">
                                   <i class="feather-user-check me-2"></i> Dashboard
                               </a>
                               <a class="dropdown-item" href="user_bookings.php">
                                   <i class="feather-calendar me-2"></i> My Bookings
                               </a>
                           <?php endif; ?>
                           <a class="dropdown-item" href="user_settings.php">
                               <i class="feather-settings me-2"></i> Settings
                           </a>
                           <a class="dropdown-item" href="backend/logout.php">
                               <i class="feather-power me-2"></i> Logout
                           </a>
                       </div>
                   </li>
               </ul>
           <?php else: ?>
               <ul class="nav header-navbar-rht">
                   <li class="nav-item">
                       <a class="nav-link header-login" href="login.php"><span><i class="fa-regular fa-user"></i></span>Sign In</a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link header-reg" href="register.php"><span><i class="fa-solid fa-lock"></i></span>Sign Up</a>
                   </li>
               </ul>
           <?php endif; ?>
       </nav>
   </div>
</header>

<style>
/* User dropdown styling to match the screenshot */
.user-dropdown {
    background-color: #4e73df;
    color: white !important;
    padding: 8px 16px !important;
    border-radius: 4px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-dropdown:hover, 
.user-dropdown:focus {
    background-color: #3a5ccc;
    color: white !important;
}

.dropdown-toggle::after {
    display: none;
}

.dropdown-menu {
    min-width: 200px;
    padding: 0;
    margin-top: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    padding: 10px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
    color: #4e73df;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #4e73df;
}

/* Fix for dropdown on mobile */
@media (max-width: 991.98px) {
    .header-navbar-rht {
        margin-right: 50px;
    }
}
</style>
