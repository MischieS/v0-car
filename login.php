<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to appropriate dashboard
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}

// Get error message if any
$error_message = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
// Clear error message after displaying
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>Rent a Car</title>
<?php include('assets/includes/header_link.php') ?>
</head>
<body>

	<!-- Main Wrapper -->
	<div class="main-wrapper login-body">
		<!-- Header -->
		<?php include('assets/includes/header.php') ?>
		<!-- /Header -->

		<div class="login-wrapper">
			<div class="loginbox">						
				<div class="login-auth">
					<div class="login-auth-wrap">
						<div class="sign-group">
							<a href="index.php" class="btn sign-up"><span><i class="fe feather-corner-down-left" aria-hidden="true"></i></span> Back To Home</a>
						</div>
						<h1>Sign In</h1>
						<p class="account-subtitle">Enter your email and password to continue</p>
						<?php if (isset($_GET['error'])): ?>
       						<p style="color: red;">Invalid email or password</p>
   						<?php endif; ?>
                        <?php if (!empty($error_message)): ?>
                            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                        <?php endif; ?>
						<form action="backend/process_login.php" method="post">
							<div class="input-block">
								<label for="email" class="form-label">Email <span class="text-danger">*</span></label>
								<input type="email" id="email" name="email" class="form-control" required>
							</div>
							<div class="input-block">
								<label for="password" class="form-label">Password <span class="text-danger">*</span></label>
								<div class="pass-group">
									<input type="password" name="password" id="password" class="form-control pass-input" required>
									<span class="fas fa-eye-slash toggle-password"></span>
								</div>
							</div>
							<div class="input-block">
								<a class="forgot-link" href="forgot-password.html">Forgot Password ?</a>
							</div>
							<div class="input-block m-0">
								<label class="custom_check d-inline-flex"><span>Remember me</span>
									<input type="checkbox" name="remember">
									<span class="checkmark"></span>
								</label>
							</div>
							<button class="btn btn-outline-light w-100 btn-size mt-1">Sign In</button>
							<div class="text-center dont-have">Don't have an account yet? <a href="register.php">Register</a></div>
						</form>							
					</div>
				</div>
			</div>
		</div>
		
	</div>
	<!-- /Main Wrapper -->
	<?php include('assets/includes/footer_link.php') ?>
</body>
</html>
