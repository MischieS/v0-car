<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="booking_list.php">Cars</a></li>
                    <li><a href="about_us.php">About Us</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="contact_us.php">Contact Us</a></li>
                    <?php if ($isLoggedIn && $isAdmin): ?>
                    <li class="has-submenu">
                        <a href="admin_dashboard.php">Admin <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu">
                            <li><a href="admin_dashboard.php">Dashboard</a></li>
                            <li><a href="admin_bookings.php">Bookings</a></li>
                            <li><a href="admin_cars.php">Cars</a></li>
                            <li><a href="admin_users.php">Users</a></li>
                            <li><a href="admin_settings.php">Settings</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <ul class="nav header-navbar-rht">
                <?php if ($isLoggedIn): ?>
                <li class="nav-item dropdown has-arrow logged-item">
                    <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="user-img">
                            <img src="assets/img/profiles/avatar-01.jpg" alt="<?php echo htmlspecialchars($userName); ?>" width="31" class="rounded-circle">
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="user-header">
                            <div class="avatar avatar-sm">
                                <img src="assets/img/profiles/avatar-01.jpg" alt="<?php echo htmlspecialchars($userName); ?>" class="avatar-img rounded-circle">
                            </div>
                            <div class="user-text">
                                <h6><?php echo htmlspecialchars($userName); ?></h6>
                                <p class="text-muted mb-0"><?php echo $isAdmin ? 'Administrator' : 'User'; ?></p>
                            </div>
                        </div>
                        <?php if ($isAdmin): ?>
                        <a class="dropdown-item" href="admin_dashboard.php">Dashboard</a>
                        <a class="dropdown-item" href="admin_settings.php">Settings</a>
                        <?php else: ?>
                        <a class="dropdown-item" href="user_dashboard.php">Dashboard</a>
                        <a class="dropdown-item" href="user_settings.php">Settings</a>
                        <?php endif; ?>
                        <a class="dropdown-item" href="backend/logout.php">Logout</a>
                    </div>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link header-login" href="login.php"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
