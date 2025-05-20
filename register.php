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
							<h1>Sign Up</h1>
							<form action="backend/process_signup.php" method="post" id="register">
								<div class="input-block">
									<label for="username" class="form-label">Username <span class="text-danger">*</span></label>
									<input type="text" id="user_name" name="user_name" class="form-control"  placeholder="">
								</div>
								<div class="input-block">
									<label for="email" class="form-label">Email <span class="text-danger">*</span></label>
									<input type="email" id="user_email" name="user_email" class="form-control"  placeholder="">
								</div>
								<div class="input-block">
									<label for="password" class="form-label">Password <span class="text-danger">*</span></label>
									<div class="pass-group">
										<input type="password" id="password" name="password" class="form-control pass-input" placeholder="">
										<span class="fas fa-eye-slash toggle-password"></span>
									</div>
								</div>	
								<div class="input-block">
									<label for="password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
									<div class="pass-group">
										<input type="password" id="password_confirm" name="password_confirm" class="form-control pass-input" placeholder="">
										<span class="fas fa-eye-slash toggle-password"></span>
									</div>
								</div>	
								<button type="submit" class="btn btn-outline-light w-100 btn-size mt-1">Sign Up</button>
								<div class="text-center dont-have">Already have an Account? <a href="login.php">Sign In</a></div>
							</form>							
						</div>
					</div>
				</div>
			</div>

		</div>
		<?php include('assets/includes/footer_link.php') ?>
	</body>
</html>
