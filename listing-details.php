<?php
session_start();
require_once __DIR__ . '/backend/db_connect.php';

// Get car ID
$car_id = (int)($_GET['car_id'] ?? 0);
if (!$car_id) {
    die('Car ID is missing.');
}

// Fetch car details
$stmt = $conn->prepare("
    SELECT c.*, l.location_name, cb.brand_name, cm.model_name, 
           cc.category_name, ft.fuel_name, gt.gear_name
    FROM cars c
    LEFT JOIN locations l ON c.location_id = l.location_id
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    LEFT JOIN car_models cm ON c.model_id = cm.id
    LEFT JOIN car_categories cc ON c.category_id = cc.id
    LEFT JOIN fuel_types ft ON c.fuel_type_id = ft.id
    LEFT JOIN gear_types gt ON c.gear_type_id = gt.id
    WHERE c.car_id = ?
");
$stmt->bind_param('i', $car_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    die('Car not found.');
}

// Fetch all images for this car
$imgStmt = $conn->prepare("
    SELECT image_path FROM car_images 
    WHERE car_id = ? 
    ORDER BY image_id ASC
");
$imgStmt->bind_param('i', $car_id);
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

// Get unavailable dates
$dates = [];
$dateStmt = $conn->prepare("
    SELECT start_date, end_date 
    FROM reservations 
    WHERE car_id = ? AND status = 'active'
");
$dateStmt->bind_param('i', $car_id);
$dateStmt->execute();
$dateRes = $dateStmt->get_result();

while ($r = $dateRes->fetch_assoc()) {
    $sd = new DateTime($r['start_date']);
    $ed = new DateTime($r['end_date']);
    for ($d = clone $sd; $d <= $ed; $d->modify('+1 day')) {
        $dates[] = $d->format('Y-m-d');
    }
}
$datesJson = json_encode(array_values(array_unique($dates)));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['car_type']) ?> - Car Details</title>
    <?php include('assets/includes/header_link.php') ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .main-image {
            height: 400px;
            overflow: hidden;
            border-radius: 10px;
        }
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .thumbnail {
            height: 80px;
            cursor: pointer;
            border-radius: 5px;
            overflow: hidden;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        .thumbnail.active {
            opacity: 1;
            border: 2px solid #ff9a00;
        }
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .car-features li {
            margin-bottom: 10px;
        }
        .car-features i {
            width: 25px;
            color: #ff9a00;
        }
        .date-picker-container {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include('assets/includes/header.php') ?>

        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <div class="container">
                <div class="row align-items-center text-center">
                    <div class="col-md-12 col-12">
                        <h2 class="breadcrumb-title"><?= htmlspecialchars($car['car_type']) ?></h2>
                        <nav aria-label="breadcrumb" class="page-breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="booking_list.php">Cars</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Details</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <!-- Car Details -->
        <div class="container py-5">
            <div class="row">
                <!-- Car Images -->
                <div class="col-lg-8 mb-4">
                    <div class="main-image mb-3" id="mainImage">
                        <img src="assets/img/cars/<?= htmlspecialchars($allImages[0]) ?>" alt="<?= htmlspecialchars($car['car_type']) ?>">
                    </div>
                    
                    <?php if (count($allImages) > 1): ?>
                    <div class="row g-2">
                        <?php foreach ($allImages as $index => $img): ?>
                        <div class="col-2">
                            <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="changeMainImage(this, '<?= htmlspecialchars($img) ?>')">
                                <img src="assets/img/cars/<?= htmlspecialchars($img) ?>" 
                                     alt="Thumbnail <?= $index + 1 ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Car Info & Booking -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($car['car_type']) ?></h3>
                            <h4 class="text-primary mb-3">$<?= number_format($car['car_price_perday'], 2) ?> <small class="text-muted">/day</small></h4>
                            
                            <ul class="list-unstyled car-features">
                                <li><i class="fas fa-car"></i> <?= htmlspecialchars($car['brand_name'] ?? 'N/A') ?> <?= htmlspecialchars($car['model_name'] ?? '') ?></li>
                                <li><i class="fas fa-tag"></i> <?= htmlspecialchars($car['category_name'] ?? 'N/A') ?></li>
                                <li><i class="fas fa-gas-pump"></i> <?= htmlspecialchars($car['fuel_name'] ?? 'N/A') ?></li>
                                <li><i class="fas fa-cogs"></i> <?= htmlspecialchars($car['gear_name'] ?? 'N/A') ?></li>
                                <li><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($car['year']) ?></li>
                                <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($car['location_name']) ?></li>
                            </ul>
                            
                            <hr>
                            
                            <div class="date-picker-container bg-light">
                                <h5 class="mb-3">Book This Car</h5>
                                <div id="datePicker" class="mb-3"></div>
                                <div class="d-grid">
                                    <button id="bookNowBtn" class="btn btn-primary" disabled>
                                        Select Dates to Book
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Car Description -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title">About This Car</h4>
                            <p>Experience the ultimate driving pleasure with the <?= htmlspecialchars($car['car_type']) ?>. 
                            This <?= htmlspecialchars($car['year']) ?> model offers exceptional performance and comfort for your journey.</p>
                            
                            <p>Whether you're planning a business trip or a family vacation, this 
                            <?= htmlspecialchars($car['category_name'] ?? 'vehicle') ?> provides the perfect balance of style, 
                            efficiency, and reliability.</p>
                            
                            <h5 class="mt-4">Key Features:</h5>
                            <ul>
                                <li>Brand: <?= htmlspecialchars($car['brand_name'] ?? 'N/A') ?></li>
                                <li>Model: <?= htmlspecialchars($car['model_name'] ?? 'N/A') ?></li>
                                <li>Fuel Type: <?= htmlspecialchars($car['fuel_name'] ?? 'N/A') ?></li>
                                <li>Transmission: <?= htmlspecialchars($car['gear_name'] ?? 'N/A') ?></li>
                                <li>Class: <?= htmlspecialchars($car['car_class']) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Car Details -->

        <?php include('assets/includes/footer.php') ?>
    </div>
    
    <?php include('assets/includes/footer_link.php') ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        // Image gallery
        function changeMainImage(thumbnail, imagePath) {
            // Update main image
            document.getElementById('mainImage').innerHTML = 
                `<img src="assets/img/cars/${imagePath}" alt="<?= htmlspecialchars($car['car_type']) ?>">`;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
        
        // Date picker
        document.addEventListener('DOMContentLoaded', function() {
            let startDate, endDate;
            const unavailableDates = <?= $datesJson ?>;
            
            const fp = flatpickr("#datePicker", {
                mode: "range",
                minDate: "today",
                disable: unavailableDates,
                inline: true,
                onChange: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        startDate = selectedDates[0];
                        endDate = selectedDates[1];
                        
                        const btn = document.getElementById('bookNowBtn');
                        btn.textContent = 'Book Now';
                        btn.disabled = false;
                    }
                }
            });
            
            document.getElementById('bookNowBtn').addEventListener('click', function() {
                if (!startDate || !endDate) return;
                
                const formatDate = date => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };
                
                window.location.href = `booking_checkout.php?car_id=<?= $car_id ?>&pickup_date=${formatDate(startDate)}&return_date=${formatDate(endDate)}`;
            });
        });
    </script>
</body>
</html>
