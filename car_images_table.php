<?php
// Include database connection
require_once 'backend/db_connection.php';

// Create car_images table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS car_images (
    id INT(11) NOT NULL AUTO_INCREMENT,
    car_id INT(11) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table car_images created successfully or already exists";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
