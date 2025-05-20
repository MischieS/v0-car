<?php
// admin_cars.php

// Include necessary files (e.g., database connection)
require_once 'db_connect.php';

// Fetch all cars from the database
$sql = "SELECT * FROM cars";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cars</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <h1>Admin - Cars</h1>
        <a href="admin_add_car.php" class="btn btn-primary">Add New Car</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($car = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $car['id'] . "</td>";
                        echo "<td>" . $car['make'] . "</td>";
                        echo "<td>" . $car['model'] . "</td>";
                        echo "<td>" . $car['year'] . "</td>";
                        echo "<td>" . $car['price'] . "</td>";
                        echo "<td>
                            <a href='admin_edit_car.php?id=" . $car['id'] . "' class='btn btn-primary btn-sm'>
                                <i class='fas fa-edit'></i> Edit
                            </a>
                            <a href='admin_delete_car.php?id=" . $car['id'] . "' class='btn btn-danger btn-sm'>
                                <i class='fas fa-trash'></i> Delete
                            </a>
                            <a href=\"admin_car_images.php?car_id=" . $car['id'] . "\" class=\"btn btn-info btn-sm\">
                                <i class=\"fas fa-images\"></i> Manage Images
                            </a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No cars found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
