<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>Rent a Car</title>
	<?php include('assets/includes/header_link.php') ?>
</head>
<body>
	
	<div class="main-wrapper">
		
		<!-- Header -->
		<?php include('assets/includes/header.php') ?>
		<!-- /Header -->

		<!-- Banner -->
		<section class="banner-section banner-slider">		
			<div class="container">
			   	<div class="home-banner">		
				   <div class="row align-items-center">					    
					   	<div class="col-lg-6" >
							<p class="explore-text"> <span><i class="fa-solid fa-thumbs-up me-2"></i></span>Whenever, wherever you need</p>
							<h1><span>Rent a Car</span> <br>									
							in Seconds</h1>
							<p>Experience the ultimate in comfort, performance, and sophistication with our car rentals. From sleek sedans and stylish coupes to spacious SUVs and elegant convertibles, we offer a range of premium vehicles to suit your preferences and lifestyle. </p>
							<div class="view-all">
								<a href="booking_list.php" class="btn btn-view d-inline-flex align-items-center">View all Cars <span><i class="feather-arrow-right ms-2"></i></span></a>
							</div>
					   	</div>
				   	</div>
			   	</div>	
		   	</div>
		</section>
	   	<!-- /Banner -->
		
		<!-- Search -->	
		<div class="section-search"> 
			<div class="container">	  
				<div class="search-box-banner">
					<form action="booking_list.php">
						<ul class="align-items-center">
							<li class="column-group-main">
								<div class="input-block">
									<label>Pickup Location</label>												
									<div class="group-img">
										<input type="text" class="form-control" placeholder="Enter City, Airport, or Address">
										<span><i class="feather-map-pin"></i></span>
									</div>
								</div>
							</li>
							<li class="column-group-main">						
								<div class="input-block">																	
									<label>Pickup Date</label>
								</div>
								<div class="input-block-wrapp">
									<div class="input-block date-widget">												
										<div class="group-img">
										<input type="text" class="form-control datetimepicker" placeholder="04/11/2023">
										<span><i class="feather-calendar"></i></span>
										</div>
									</div>
									<div class="input-block time-widge">											
										<div class="group-img">
										<input type="text" class="form-control timepicker" placeholder="11:00 AM">
										<span><i class="feather-clock"></i></span>
										</div>
									</div>
								</div>	
							</li>
							<li class="column-group-main">						
								<div class="input-block">																	
									<label>Return Date</label>
								</div>
								<div class="input-block-wrapp">
									<div class="input-block date-widge">												
										<div class="group-img">
										<input type="text" class="form-control datetimepicker" placeholder="04/11/2023">
										<span><i class="feather-calendar"></i></span>
										</div>
									</div>
									<div class="input-block time-widge">											
										<div class="group-img">
										<input type="text" class="form-control timepicker" placeholder="11:00 AM">
										<span><i class="feather-clock"></i></span>
										</div>
									</div>
								</div>	
							</li>
							<li class="column-group-last">
								<div class="input-block">
									<div class="search-btn">
										<button class="btn search-button" type="submit"> <i class="fa fa-search" aria-hidden="true"></i>Search</button>
									</div>
								</div>
							</li>
						</ul>
					</form>	
				</div>
			</div>	
		</div>	
		<!-- /Search -->

	</div>
	<?php include('assets/includes/footer_link.php') ?>
</body>
</html>
