<?php
// get_models.php - Returns car models for a specific brand as JSON

require_once __DIR__ . '/db_connect.php';
if (!($conn instanceof mysqli)) die(json_encode(['error' => 'Database connection failed']));

// Check if brand_id is provided
if (!isset($_GET['brand_id']) || empty($_GET['brand_id'])) {
    echo json_encode([]);
    exit;
}

$brandId = (int) $_GET['brand_id'];

// Check if car_models table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'car_models'")->num_rows > 0;
if (!$tableExists) {
    // Redirect to setup if table doesn't exist
    header('Location: setup_database.php');
    exit;
}

// Prepare and execute query
$stmt = $conn->prepare("SELECT id, model_name FROM car_models WHERE brand_id = ? ORDER BY model_name");
$stmt->bind_param("i", $brandId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all models
$models = [];
while ($row = $result->fetch_assoc()) {
    $models[] = $row;
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($models);
?>
