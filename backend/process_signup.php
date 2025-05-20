<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Simple validation
    if(empty($user_name) || empty($user_email) || empty($password)){
        die("Please fill in all required fields!");
    }

    if ($password !== $password_confirm) {
        die("Passwords do not match!");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        die("Email already registered!");
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user_name, $user_email, $password_hash);

    if ($stmt->execute()) {
        // On successful registration, redirect to login page.
        header("Location: ../login.php?success=registered");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
}
