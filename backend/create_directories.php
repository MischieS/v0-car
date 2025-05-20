<?php
// Create necessary directories for the application

// Profile images directory
$profileDir = __DIR__ . '/../assets/img/profiles';
if (!file_exists($profileDir)) {
    mkdir($profileDir, 0755, true);
    echo "Created profiles directory: $profileDir\n";
}

// Default profile image
$defaultImg = $profileDir . '/default.png';
if (!file_exists($defaultImg)) {
    // Copy a default avatar if available, or create a simple one
    $sourceAvatar = __DIR__ . '/../assets/img/avatar.jpg';
    if (file_exists($sourceAvatar)) {
        copy($sourceAvatar, $defaultImg);
        echo "Copied default avatar to: $defaultImg\n";
    } else {
        // Create a simple default avatar
        $img = imagecreatetruecolor(200, 200);
        $bgColor = imagecolorallocate($img, 240, 240, 240);
        $textColor = imagecolorallocate($img, 100, 100, 100);
        
        imagefill($img, 0, 0, $bgColor);
        imagestring($img, 5, 70, 90, 'User', $textColor);
        
        imagepng($img, $defaultImg);
        imagedestroy($img);
        echo "Created default avatar at: $defaultImg\n";
    }
}

echo "Directory setup complete!\n";
