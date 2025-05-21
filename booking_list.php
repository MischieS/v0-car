<?php
// booking_list.php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Check if the reservations table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'reservations'");
if ($tableCheck->num_rows == 0) {
    // Table doesn't exist, redirect to database setup
    header("Location: backend/db_setup.php");
    exit;
}

// Filters
$q         = trim($_GET['q'] ?? '');
$class     = trim($_GET['class'] ?? '');
$trans     = trim($_GET['transmission'] ?? '');
$year      = trim($_GET['year'] ?? '');

// Build dynamic SQL with a join to locations
$sql    = "
    SELECT 
      c.*, 
      l.location_name
    FROM cars c
    LEFT JOIN locations l 
      ON c.location_id = l.location_id
    WHERE 1
";
$params = [];
$types  = '';

// Search by model or location_name
if ($q !== '') {
    $sql      .= " AND (c.car_type LIKE ? OR l.location_name LIKE ?)";
    $params[]  = "%$q%";
    $params[]  = "%$q%";
    $types    .= 'ss';
}

// Class filter
if ($class) {
    $sql      .= " AND c.car_class = ?";
    $params[]  = $class;
    $types    .= 's';
}

//  filter
if ($trans) {
    $sql      .= " AND c.transmission = ?";
    $params[]  = $trans;
    $types    .= 's';
}

// Year filter
if ($year) {
    $sql      .= " AND c.year = ?";
    $params[]  = $year;
    $types    .= 'i';
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cars = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Car Listings - Rent a Car</title>
    <?php include 'assets/includes/header_link.php'; ?>
    <style>
    .carousel-item {
        height: 200px;
        overflow: hidden;
    }
    .carousel-item img {
        object-fit: cover;
        height: 100%;
        width: 100%;
    }
    .carousel-indicators {
        margin-bottom: 0;
    }
    .carousel-indicators button {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: rgba(255,255,255,0.5);
    }
    .carousel-indicators button.active {
        background-color: white;
    }
    .carousel-control-prev, .carousel-control-next {
        width: 10%;
        opacity: 0.7;
    }
    .carousel-control-prev:hover, .carousel-control-next:hover {
        opacity: 1;
    }
</style>
</head>

<body>
    <?php include 'assets/includes/header.php'; ?>

 <!-- Breadscrumb Section -->
 <div class="breadcrumb-bar">
			<div class="container">
				<div class="row align-items-center text-center">
					<div class="col-md-12 col-12">
						<h2 class="breadcrumb-title">Booking</h2>
						<nav aria-label="breadcrumb" class="page-breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Booking</li>
							</ol>
						</nav>
					</div>
				</div>
			</div>
		</div>
		<!-- /Breadscrumb Section -->

    <div class="container py-5">
        <h2 class="text-center mb-4">Car Listings</h2>
        <div class="row">
            <!-- Filter & Search -->
            <aside class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Filter &amp; Search</h5>
                        <form method="get" action="booking_list.php">
                            <div class="mb-3">
                                <input
                                    type="search"
                                    name="q"
                                    class="form-control form-control-sm"
                                    placeholder="Search model or location"
                                    value="<?= htmlspecialchars($q) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Class</label>
                                <select name="class" class="form-select form-select-sm">
                                    <option value="">Any</option>
                                    <option value="Economy" <?= $class === 'Economy'  ? 'selected' : '' ?>>Economy</option>
                                    <option value="Standard" <?= $class === 'Standard' ? 'selected' : '' ?>>Standard</option>
                                    <option value="Premium" <?= $class === 'Premium'  ? 'selected' : '' ?>>Premium</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Transmission</label>
                                <select name="transmission" class="form-select form-select-sm">
                                    <option value="">Any</option>
                                    <option value="manual" <?= $trans === 'manual'    ? 'selected' : '' ?>>Manual</option>
                                    <option value="automatic" <?= $trans === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year</label>
                                <input
                                    type="number"
                                    name="year"
                                    class="form-control form-control-sm"
                                    placeholder="e.g. 2022"
                                    value="<?= htmlspecialchars($year) ?>">
                            </div>
                            <button class="btn btn-sm btn-primary w-100" type="submit">
                                Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
            <!-- /Filter & Search -->

            <!-- Listings -->
            <div class="col-lg-9">
                <?php if ($cars->num_rows === 0): ?>
                    <div class="alert alert-info text-center">
                        No cars match your criteria.
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php while ($car = $cars->fetch_assoc()): ?>
                            
<div class="col-xxl-4 col-lg-6 col-md-6 col-12">
    <div class="listing-item">
        <div class="listing-img">
            <?php
            // Fetch all images for this car
            $imgStmt = $conn->prepare("
                SELECT image_path FROM car_images 
                WHERE car_id = ? 
                ORDER BY image_id ASC
            ");
            $imgStmt->bind_param('i', $car['car_id']);
            $imgStmt->execute();
            $images = $imgStmt->get_result();
            
            // Create array of all images (main + gallery)
            $allImages = [];
            if ($car['car_image']) {
                $allImages[] = $car['car_image'];
            }
            while ($img = $images->fetch_assoc()) {
                if (!empty($img['image_path'])) {
                    $allImages[] = $img['image_path'];
                }
            }
            
            // If no images, use default
            if (empty($allImages)) {
                $allImages[] = 'default-car.jpg';
            }
            ?>
            
            <!-- Image Carousel -->
            <div id="carCarousel<?= $car['car_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                <!-- Indicators -->
                <?php if (count($allImages) > 1): ?>
                <div class="carousel-indicators">
                    <?php foreach ($allImages as $index => $img): ?>
                    <button type="button" data-bs-target="#carCarousel<?= $car['car_id'] ?>" 
                            data-bs-slide-to="<?= $index ?>" 
                            <?= $index === 0 ? 'class="active"' : '' ?>></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Slides -->
                <div class="carousel-inner">
                    <?php foreach ($allImages as $index => $img): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="assets/img/cars/<?= htmlspecialchars($img) ?>" 
                             class="d-block w-100" 
                             alt="<?= htmlspecialchars($car['car_type']) ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Controls -->
                <?php if (count($allImages) > 1): ?>
                <button class="carousel-control-prev" type="button" 
                        data-bs-target="#carCarousel<?= $car['car_id'] ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" 
                        data-bs-target="#carCarousel<?= $car['car_id'] ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="listing-content">
            <h5 class="listing-features d-flex align-items-end justify-content-between">
                <?= htmlspecialchars($car['car_type']) ?>
            </h5>
            <ul class="listing-details-group">
                <li>
                    <i class="fas fa-cogs me-1"></i>
                    <?= ucfirst($car['gear_name'] ?? 'N/A') ?>
                </li>
                <li>
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?= htmlspecialchars($car['year']) ?>
                </li>
            </ul>
            <div class="listing-location-details d-flex justify-content-between align-items-center">
                <small class="listing-location">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    <?= htmlspecialchars($car['location_name']) ?>
                </small>
                <div class="listing-price">
                    $<?= number_format($car['car_price_perday'], 2) ?>
                    <small>/day</small>
                </div>
            </div>

            <?php
            $dates = [];
            $stmt2 = $conn->prepare("
                SELECT start_date,end_date 
                FROM reservations 
                WHERE car_id=? 
                AND status='active'
            ");
            $stmt2->bind_param('i', $car['car_id']);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($r = $res2->fetch_assoc()) {
                $sd = new DateTime($r['start_date']);
                $ed = new DateTime($r['end_date']);
                for ($d = clone $sd; $d <= $ed; $d->modify('+1 day')) {
                    $dates[] = $d->format('Y-m-d');
                }
            }
            $datesJson = htmlspecialchars(json_encode(array_unique($dates)), ENT_QUOTES);
            ?>

            <button 
                class="btn btn-sm btn-primary w-100 book-now-btn"
                data-bs-toggle="modal"
                data-bs-target="#dateModal"
                data-car-id="<?= $car['car_id'] ?>"
                data-reserved='<?= json_encode(array_values(array_unique($dates))) ?>'
            >
                Book Now
            </button>
        </div>
    </div>
</div>

                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- /Listings -->
        </div>
    </div>

    <?php include 'assets/includes/footer.php'; ?>
    <?php include 'assets/includes/footer_link.php'; ?>

    <!-- jQuery & Flatpickr -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Date Range Modal -->
    <div class="modal fade" id="dateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Pickup & Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="inlineCalendar"></div>
                </div>
                <div class="modal-footer">
                    <button id="confirmDates" class="btn btn-primary" disabled>
                        Confirm Dates
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
$(function() {
  let startDate, endDate, currentCarId = null;
  let fp = null; // flatpickr instance

  $('#dateModal').on('show.bs.modal', function(e) {
    const button = e.relatedTarget; // the button that triggered the modal
    if (!button) return;

    // Get car ID
    currentCarId = button.getAttribute('data-car-id') || '';
    console.log('Selected car ID:', currentCarId);

    // Reserved dates
    const reservedRaw = button.getAttribute('data-reserved') || '[]';
    const reservedJson = reservedRaw.replace(/&quot;/g, '"');
    let reservedDates = [];

    try {
      reservedDates = JSON.parse(reservedJson);
    } catch (err) {
      console.warn('Invalid reserved dates:', err);
    }

    // Destroy previous calendar
    if (fp && typeof fp.destroy === 'function') {
      fp.destroy();
    }

    // Build new calendar
    fp = flatpickr('#inlineCalendar', {
      mode: 'range',
      inline: true,
      disable: reservedDates,
      minDate: 'today',
      onChange(selectedDates) {
        if (selectedDates.length === 2) {
          startDate = selectedDates[0];
          endDate = selectedDates[1];
          $('#confirmDates').prop('disabled', false);
        } else {
          $('#confirmDates').prop('disabled', true);
        }
      }
    });

    // Disable confirm button initially
    $('#confirmDates').prop('disabled', true);
  });

  $('#confirmDates').click(function() {
    if (!startDate || !endDate || !currentCarId) {
      alert('Please select dates and car properly!');
      return;
    }
    const fmt = d => d.toISOString().slice(0,10);
    window.location.href =
      `booking_checkout.php?car_id=${currentCarId}` +
      `&pickup_date=${fmt(startDate)}` +
      `&return_date=${fmt(endDate)}`;
  });
});
</script>

</body>

</html>
