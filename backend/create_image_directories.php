<?php
// Define directories to create
$directories = [
    'assets/img/cars',
    'assets/img/users',
    'assets/img/cars/gallery'
];

// Create directories if they don't exist
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Created directory: $dir<br>";
        } else {
            echo "Failed to create directory: $dir<br>";
        }
    } else {
        echo "Directory already exists: $dir<br>";
    }
}

// Create placeholder images if they don't exist
$placeholderImages = [
    'assets/img/cars/mercedes-e-class.jpg' => 'https://images.unsplash.com/photo-1563720223185-11003d516935',
    'assets/img/cars/bmw-5-series.jpg' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e',
    'assets/img/cars/audi-q7.jpg' => 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6',
    'assets/img/cars/gallery-1.jpg' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d',
    'assets/img/cars/gallery-2.jpg' => 'https://images.unsplash.com/photo-1542362567-b07e54358753',
    'assets/img/cars/default-car.jpg' => 'https://images.unsplash.com/photo-1550355291-bbee04a92027',
    'assets/img/car-hero-minimal.png' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf'
];

foreach ($placeholderImages as $path => $url) {
    if (!file_exists($path)) {
        // Try to download the image
        $imageData = @file_get_contents($url);
        if ($imageData) {
            if (file_put_contents($path, $imageData)) {
                echo "Created image: $path<br>";
            } else {
                echo "Failed to create image: $path<br>";
            }
        } else {
            echo "Failed to download image from: $url<br>";
            
            // Create a simple placeholder image
            $image = imagecreatetruecolor(800, 600);
            $bgColor = imagecolorallocate($image, 240, 240, 240);
            $textColor = imagecolorallocate($image, 100, 100, 100);
            imagefill($image, 0, 0, $bgColor);
            $text = basename($path);
            imagestring($image, 5, 300, 280, $text, $textColor);
            
            // Save the image
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension == 'jpg' || $extension == 'jpeg') {
                imagejpeg($image, $path, 90);
                echo "Created placeholder image: $path<br>";
            } elseif ($extension == 'png') {
                imagepng($image, $path);
                echo "Created placeholder image: $path<br>";
            }
            
            imagedestroy($image);
        }
    } else {
        echo "Image already exists: $path<br>";
    }
}

echo "✅ Image directories and placeholder images setup complete.";
?>
