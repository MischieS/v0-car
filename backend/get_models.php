<?php
require_once 'db_connect.php';

if (!isset($_GET['brand_id'])) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

$brand_id = (int) $_GET['brand_id'];

$stmt = $conn->prepare("SELECT id, model_name FROM car_models WHERE brand_id = ?");
$stmt->bind_param("i", $brand_id);
$stmt->execute();
$result = $stmt->get_result();

$models = [];
while ($row = $result->fetch_assoc()) {
    $models[] = $row;
}

header('Content-Type: application/json');
echo json_encode($models);
