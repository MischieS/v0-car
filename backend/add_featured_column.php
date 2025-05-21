<?php
// Include database connection
require_once 'db_connect.php';

// Check if the 'featured' column already exists
$checkColumn = $conn->query("SHOW COLUMNS FROM cars LIKE 'featured'");

if ($checkColumn && $checkColumn->num_rows == 0) {
    // 'featured' column doesn't exist, add it
    $sql = "ALTER TABLE cars ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0";
    
    if ($conn->query($sql)) {
        echo "Successfully added 'featured' column to cars table.<br>";
        
        // Mark some cars as featured
        $sql = "UPDATE cars SET featured = 1 ORDER BY car_id DESC LIMIT 6";
        
        if ($conn->query($sql)) {
            echo "Successfully marked 6 cars as featured.<br>";
        } else {
            echo "Error marking cars as featured: " . $conn->error . "<br>";
        }
    } else {
        echo "Error adding 'featured' column: " . $conn->error . "<br>";
    }
} else {
    echo "'featured' column already exists in cars table.<br>";
}

echo "<p><a href='../index.php'>Return to homepage</a></p>";
?>
