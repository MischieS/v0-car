<?php
// Include necessary files and establish database connection
include 'config.php'; // Contains database credentials and connection details

// Start session (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get booking ID from the URL
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // Fetch booking details from the database
    $stmt = $conn->prepare("SELECT bookings.*, cars.make, cars.model, cars.year, cars.price_per_day, users.first_name, users.last_name
                            FROM bookings
                            INNER JOIN cars ON bookings.car_id = cars.car_id
                            INNER JOIN users ON bookings.user_id = users.user_id
                            WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $car_id = $booking['car_id']; // Store car_id for image retrieval

        // Calculate total price
        $start_date = new DateTime($booking['start_date']);
        $end_date = new DateTime($booking['end_date']);
        $interval = $start_date->diff($end_date);
        $days = $interval->days + 1; // Include both start and end dates
        $total_price = $days * $booking['price_per_day'];
    } else {
        echo "Booking not found.";
        exit();
    }
} else {
    echo "Booking ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <h1>Booking Details</h1>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></h5>
                <p class="card-text"><strong>User:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                <p class="card-text"><strong>Car:</strong> <?php echo htmlspecialchars($booking['year'] . ' ' . $booking['make'] . ' ' . $booking['model']); ?></p>
                <p class="card-text"><strong>Start Date:</strong> <?php echo htmlspecialchars($booking['start_date']); ?></p>
                <p class="card-text"><strong>End Date:</strong> <?php echo htmlspecialchars($booking['end_date']); ?></p>
                <p class="card-text"><strong>Total Days:</strong> <?php echo htmlspecialchars($days); ?></p>
                <p class="card-text"><strong>Price per Day:</strong> $<?php echo htmlspecialchars($booking['price_per_day']); ?></p>
                <p class="card-text"><strong>Total Price:</strong> $<?php echo htmlspecialchars($total_price); ?></p>

                <?php
                // Get all images for this car
                $stmt = $conn->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_primary DESC, created_at ASC");
                $stmt->bind_param("i", $car_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $images = $result->fetch_all(MYSQLI_ASSOC);

                // If no images, use a default image
                if (empty($images)) {
                    $images[] = ['image_path' => 'assets/images/default-car.jpg'];
                }
                ?>

                <div id="carImageCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php for ($i = 0; $i < count($images); $i++): ?>
                            <button type="button" data-bs-target="#carImageCarousel" data-bs-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active"' : ''; ?> aria-label="Slide <?php echo $i + 1 ?>"></button>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="d-block w-100" alt="Car Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
