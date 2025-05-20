<?php
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
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
								<a href="booking_list.php">Booking<i class="fas"></i></a>
							</li>
							<li class="has-submenu">
								<a href="about_us.php">About Us <i class="fas"></i></a>
							</li>
							<li class="has-submenu">
								<a href="faq.php">FAQ <i class="fas"></i></a>
							</li>
							<li><a href="contact_us.php">Contact</a></li>
							<li class="login-link">
								<a href="register.php">Sign Up</a>
							</li>
							<li class="login-link">
								<a href="login.php">Sign In</a>
							</li>
						</ul>
					</div>
					<?php if (isset($_SESSION["user_id"])): ?>
						<li class="nav-item dropdown has-arrow logged-item">
							<a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
								<span class="user-text">Welcome, <?= htmlspecialchars($_SESSION["user_name"]) ?></span>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<?php if ($_SESSION["user_role"] === "admin"): ?>
									<a class="dropdown-item" href="admin_dashboard.php">
										<i class="feather-shield"></i> Admin Dashboard
									</a>
								<?php else: ?>
									<a class="dropdown-item" href="user_dashboard.php">
										<i class="feather-user-check"></i> User Dashboard
									</a>
								<?php endif; ?>
								<a class="dropdown-item" href="user_settings.php">
									<i class="feather-settings"></i> Settings
								</a>
								<a class="dropdown-item" href="backend/logout.php">
									<i class="feather-power"></i> Logout
								</a>
							</div>
						</li>
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
