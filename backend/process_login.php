<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = trim($_POST['user_email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, user_name, password_hash, user_role FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_role'] = $user['user_role'];

            if ($user['user_role'] == 'admin') {
                header("Location: ../admin_dashboard.php");
            } else {
                header("Location: ../user_dashboard.php");
            }
            exit;
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "User not found!";
    }
}
