<?php
// Simple database connection file
// This file checks if the database exists and creates it if needed

// Database credentials
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'car_rental_db';

// Connect to MySQL server
try {
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if database exists, create if not
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($result->num_rows == 0) {
        // Database doesn't exist, create it
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
            die("Failed to create database: " . $conn->error);
        }
    }
    
    // Select database
    if (!$conn->select_db($dbname)) {
        die("Failed to select database: " . $conn->error);
    }
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Function to normalize mobile number (kept from original)
function normalizeMobile(string $raw): string
{
   $digits = preg_replace('/\D/', '', $raw);
   if (strlen($digits) === 10) {
       $digits = '90' . $digits;
   } elseif (!str_starts_with($digits, '90')) {
       $digits = '90' . $digits;
   }
   $digits = substr($digits, 0, 12);
   $formatted = '+' . substr($digits, 0, 2)
              . '(' . substr($digits, 2, 3) . ')'
              . substr($digits, 5);
   return preg_match('/^\+90$$\d{3}$$\d{7}$/', $formatted)
       ? $formatted
       : '';
}
?>
