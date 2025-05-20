<?php
// Include database connection
require_once 'backend/db_connection.php';

// Check if car ID is provided
if (!isset($_GET['car_id']) || empty($_GET['car_id'])) {
    header('Location: admin_cars.php');
    exit;
}

$car_id = $_GET['car_id'];

// Get car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    header('Location: admin_cars.php');
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    // Create directory if it doesn't exist
    $upload_dir = "assets/images/cars/" . $car_id . "/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $uploaded_files = [];
    $errors = [];
    
    // Process each uploaded file
    foreach ($_FILES['car_images']['name'] as $key => $name) {
        if ($_FILES['car_images']['error'][$key] === 0) {
            $tmp_name = $_FILES['car_images']['tmp_name'][$key];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $destination = $upload_dir . $filename;
            
            // Check if it's an image
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($ext), $allowed_types)) {
                $errors[] = "$name is not a valid image file";
                continue;
            }
            
            // Move the uploaded file
            if (move_uploaded_file($tmp_name, $destination)) {
                // Save to database
                $stmt = $conn->prepare("INSERT INTO car_images (car_id, image_path, created_at) VALUES (?, ?, NOW())");
                $image_path = $destination;
                $stmt->bind_param("is", $car_id, $image_path);
                $stmt->execute();
                
                $uploaded_files[] = $name;
            } else {
                $errors[] = "Failed to upload $name";
            }
        } else {
            $errors[] = "Error uploading $name";
        }
    }
    
    // Set messages for user feedback
    if (!empty($uploaded_files)) {
        $_SESSION['success'] = count($uploaded_files) . " images uploaded successfully";
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    
    // Redirect to refresh the page
    header("Location: admin_car_images.php?car_id=$car_id");
    exit;
}

// Get existing images
$stmt = $conn->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $image_id = $_POST['image_id'];
    
    // Get image path
    $stmt = $conn->prepare("SELECT image_path FROM car_images WHERE id = ? AND car_id = ?");
    $stmt->bind_param("ii", $image_id, $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    
    if ($image) {
        // Delete file
        if (file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM car_images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Image deleted successfully";
        header("Location: admin_car_images.php?car_id=$car_id");
        exit;
    }
}

// Include header
include 'admin_header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Manage Car Images</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="admin_cars.php">Cars</a></li>
        <li class="breadcrumb-item active">Images for <?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></li>
    </ol>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
            <?php 
                foreach ($_SESSION['errors'] as $error) {
                    echo "<li>" . htmlspecialchars($error) . "</li>";
                }
                unset($_SESSION['errors']);
            ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-image me-1"></i>
            Upload New Images
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="car_images" class="form-label">Select Multiple Images</label>
                    <input class="form-control" type="file" id="car_images" name="car_images[]" multiple accept="image/*" required>
                    <div class="form-text">You can select multiple images by holding Ctrl (or Cmd on Mac) while selecting files.</div>
                </div>
                <button type="submit" name="upload_images" class="btn btn-primary">Upload Images</button>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-images me-1"></i>
            Existing Images
        </div>
        <div class="card-body">
            <?php if (empty($images)): ?>
                <p>No images uploaded yet.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($images as $image): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="card-img-top" alt="Car Image" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" name="delete_image" class="btn btn-danger btn-sm w-100">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'admin_footer.php';
?>
