<?php
require_once 'db_connect.php';

// Create user_activity table if it doesn't exist
$conn->query("
CREATE TABLE IF NOT EXISTS user_activity (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_description TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// Create site_settings table if it doesn't exist
$conn->query("
CREATE TABLE IF NOT EXISTS site_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_group VARCHAR(50) NOT NULL,
    setting_name VARCHAR(100) NOT NULL,
    setting_value TEXT,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (setting_group, setting_name)
)
");

// Create user_sessions table if it doesn't exist
$conn->query("
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(64) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    is_active TINYINT(1) DEFAULT 1,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL
)
");

// Insert default site settings if they don't exist
$defaultSettings = [
    // General settings
    ['general', 'site_name', 'Car Rental System'],
    ['general', 'site_description', 'Rent your dream car today'],
    ['system', 'maintenance_mode', '0'],
    
    // Contact settings
    ['contact', 'contact_email', 'contact@carrentalsystem.com'],
    ['contact', 'contact_phone', '+1 (555) 123-4567'],
    ['contact', 'address', '123 Main Street, City, Country'],
    
    // Feature toggles
    ['features', 'enable_bookings', '1'],
    
    // Booking settings
    ['booking', 'min_rental_days', '1'],
    ['booking', 'max_rental_days', '30'],
    
    // Payment settings
    ['payment', 'currency_symbol', '$'],
    ['payment', 'tax_rate', '10'],
    ['payment', 'booking_fee', '5'],
    
    // Social media
    ['social', 'facebook_url', 'https://facebook.com/carrentalsystem'],
    ['social', 'twitter_url', 'https://twitter.com/carrentalsystem'],
    ['social', 'instagram_url', 'https://instagram.com/carrentalsystem']
];

// Insert default settings
$stmt = $conn->prepare("INSERT IGNORE INTO site_settings (setting_group, setting_name, setting_value) VALUES (?, ?, ?)");
foreach ($defaultSettings as $setting) {
    $stmt->bind_param('sss', $setting[0], $setting[1], $setting[2]);
    $stmt->execute();
}

echo "Additional database tables and settings created successfully!";
?>
