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
                   <li class="nav-item dropdown">
                       <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                           <?= htmlspecialchars($_SESSION["user_name"]) ?>
                       </button>
                       <ul class="dropdown-menu dropdown-menu-end">
                           <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin"): ?>
                               <li><a class="dropdown-item" href="admin_dashboard.php">
                                   <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                               </a></li>
                               <li><a class="dropdown-item" href="admin_users.php">
                                   <i class="fas fa-users me-2"></i> Manage Users
                               </a></li>
                               <li><a class="dropdown-item" href="admin_cars.php">
                                   <i class="fas fa-car me-2"></i> Manage Cars
                               </a></li>
                               <li><a class="dropdown-item" href="admin_bookings.php">
                                   <i class="fas fa-calendar-check me-2"></i> Manage Bookings
                               </a></li>
                           <?php else: ?>
                               <li><a class="dropdown-item" href="user_dashboard.php">
                                   <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                               </a></li>
                               <li><a class="dropdown-item" href="user_bookings.php">
                                   <i class="fas fa-calendar-check me-2"></i> My Bookings
                               </a></li>
                           <?php endif; ?>
                           <li><hr class="dropdown-divider"></li>
                           <li><a class="dropdown-item" href="user_settings.php">
                               <i class="fas fa-cog me-2"></i> Settings
                           </a></li>
                           <li><a class="dropdown-item" href="backend/logout.php">
                               <i class="fas fa-sign-out-alt me-2"></i> Logout
                           </a></li>
                       </ul>
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
