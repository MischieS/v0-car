<?php
session_start();
session_unset();
session_destroy();

// Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/'); // Set expiration to past time
}

header("Location: ../index.php");
exit;
?>
