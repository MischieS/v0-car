<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    exit('Unauthorized');
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db_connect.php';

$uploadDir = __DIR__ . '/../assets/img/cars/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Logging helper - modified to use error_log instead of file_put_contents
function log_debug($msg) {
    error_log($msg);
}

// Image crop & resize
function handleUploadAndResize($tmpPath, $destPath, $crop = true) {
    $maxW = 300;
    $maxH = 200;
    $x = (int)($_POST['crop_x'] ?? 0);
    $y = (int)($_POST['crop_y'] ?? 0);
    $w = (int)($_POST['crop_width'] ?? 0);
    $h = (int)($_POST['crop_height'] ?? 0);

    $info = getimagesize($tmpPath);
    if (!$info) return false;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg': $src = imagecreatefromjpeg($tmpPath); break;
        case 'image/png':  $src = imagecreatefrompng($tmpPath);  break;
        default: return false;
    }

    if ($crop && $w > 0 && $h > 0) {
        $cropped = imagecrop($src, ['x'=>$x, 'y'=>$y, 'width'=>$w, 'height'=>$h]);
        if ($cropped) {
            imagedestroy($src);
            $src = $cropped;
        }
    }

    $origW = imagesx($src);
    $origH = imagesy($src);
    $scale = min($maxW / $origW, $maxH / $origH, 1);

    if ($scale < 1) {
        $newW = (int)($origW * $scale);
        $newH = (int)($origH * $scale);
        $resized = imagecreatetruecolor($newW, $newH);
        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);
        $src = $resized;
    }

    switch ($mime) {
        case 'image/jpeg': imagejpeg($src, $destPath, 90); break;
        case 'image/png':  imagepng($src, $destPath); break;
    }

    imagedestroy($src);
    return true;
}

// Get action
$action = $_POST['action'] ?? $_GET['action'] ?? null;
log_debug("Action: $action");

// Common processing for add/edit
if (in_array($action, ['add', 'edit'])) {
    $type         = trim($_POST['car_type'] ?? '');
    $price        = (float)($_POST['car_price_perday'] ?? 0);
    $year         = (int)($_POST['year'] ?? 0);
    $location_id  = (int)($_POST['location_id'] ?? 0);
    $car_class    = trim($_POST['car_class'] ?? '');
    $brand_id     = (int)($_POST['brand_id'] ?? 0);
    $model_id     = (int)($_POST['model_id'] ?? 0);
    $category_id  = (int)($_POST['category_id'] ?? 0);
    $fuel_type_id = (int)($_POST['fuel_type_id'] ?? 0);
    $gear_type_id = (int)($_POST['gear_type_id'] ?? 0);
    $primaryImage = '';

    if ($location_id <= 0) die('Invalid location');

    // Thumbnail
    if (!empty($_FILES['car_images']['tmp_name'][0])) {
        $tmpPath  = $_FILES['car_images']['tmp_name'][0];
        $baseName = time() . '_' . basename($_FILES['car_images']['name'][0]);
        $destPath = $uploadDir . $baseName;

        if (handleUploadAndResize($tmpPath, $destPath)) {
            $primaryImage = $baseName;
        } else {
            die('Thumbnail processing failed');
        }
    }
}

// === ADD NEW CAR ===
if ($action === 'add') {
    log_debug("Adding car...");

    $stmt = $conn->prepare("
    INSERT INTO cars (
        car_type, car_price_perday, car_image,
        year, location_id, car_class,
        brand_id, model_id, category_id,
        fuel_type_id, gear_type_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die('❌ Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'sdsissiiiii',
        $type,
        $price,
        $primaryImage,
        $year,
        $location_id,
        $car_class,
        $brand_id,
        $model_id,
        $category_id,
        $fuel_type_id,
        $gear_type_id
    );

    if (!$stmt->execute()) {
        log_debug("Insert error: " . $stmt->error);
        die('Database insert failed: ' . $stmt->error);
    }

    $car_id = $stmt->insert_id;
    log_debug("Car added with ID $car_id");

    // Gallery images
    foreach ($_FILES['car_images']['tmp_name'] as $i => $tmp) {
        if (empty($tmp)) continue;
        if ($i === 0 && $primaryImage) continue; // Skip cropped image
    
        $name = uniqid() . '_' . basename($_FILES['car_images']['name'][$i]);
        $path = $uploadDir . $name;
    
        if (move_uploaded_file($tmp, $path)) {
            $imgStmt = $conn->prepare("INSERT INTO car_images (car_id, image_path) VALUES (?, ?)");
            $imgStmt->bind_param('is', $car_id, $name);
            $imgStmt->execute();
        }
    }
}

// === EDIT EXISTING CAR ===
elseif ($action === 'edit') {
    $id = (int)($_POST['car_id'] ?? 0);
    log_debug("Editing car ID $id");

    if ($primaryImage) {
        $stmt = $conn->prepare("
            UPDATE cars SET
            car_type=?, car_price_perday=?, year=?, location_id=?, car_class=?, car_image=?,
            brand_id=?, model_id=?, category_id=?, fuel_type_id=?, gear_type_id=?
            WHERE car_id=?
        ");
        $stmt->bind_param(
            'sdsissiiiiii',
            $type,
            $price,
            $year,
            $location_id,
            $car_class,
            $primaryImage,
            $brand_id,
            $model_id,
            $category_id,
            $fuel_type_id,
            $gear_type_id,
            $id
        );
    } else {
        $stmt = $conn->prepare("
            UPDATE cars SET
            car_type=?, car_price_perday=?, year=?, location_id=?, car_class=?,
            brand_id=?, model_id=?, category_id=?, fuel_type_id=?, gear_type_id=?
            WHERE car_id=?
        ");
        $stmt->bind_param(
            'sdsiiiiiiii',
            $type,
            $price,
            $year,
            $location_id,
            $car_class,
            $brand_id,
            $model_id,
            $category_id,
            $fuel_type_id,
            $gear_type_id,
            $id
        );
    }

    if (!$stmt->execute()) {
        log_debug("Update error: " . $stmt->error);
        die('Update failed: ' . $stmt->error);
    }

    // Optional: new gallery images
    foreach ($_FILES['car_images']['tmp_name'] as $i => $tmp) {
        if (empty($tmp)) continue;
        $name = time() . '_' . basename($_FILES['car_images']['name'][$i]);
        $path = $uploadDir . $name;
        if (move_uploaded_file($tmp, $path)) {
            $imgStmt = $conn->prepare("INSERT INTO car_images (car_id, image_path) VALUES (?, ?)");
            $imgStmt->bind_param('is', $id, $name);
            $imgStmt->execute();
        }
    }
}

// === DELETE CAR ===
elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    log_debug("Deleting car ID $id");

    // Delete image files
    $res = $conn->prepare("SELECT image_path FROM car_images WHERE car_id = ?");
    $res->bind_param('i', $id);
    $res->execute();
    $images = $res->get_result();
    while ($img = $images->fetch_assoc()) {
        $file = $uploadDir . $img['image_path'];
        if (file_exists($file)) unlink($file);
    }
    $stmt = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
    if (!$stmt) {
        die("❌ Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        die("❌ Execute failed: " . $stmt->error);
    }
}

header('Location: ../admin_cars.php');
exit;
