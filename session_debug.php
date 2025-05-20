<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Output session information
echo "<h1>Session Debug Information</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Not Active") . "\n\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

// Check for specific session variables
echo "<h2>Login Status Check</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green'>User is logged in with ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Username: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    echo "<p>Role: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";
} else {
    echo "<p style='color:red'>User is NOT logged in</p>";
}

// Cookie information
echo "<h2>Cookie Information</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Server information
echo "<h2>Server Information</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Cookie Parameters:\n";
print_r(session_get_cookie_params());
echo "</pre>";

// Add a link to clear session
echo "<p><a href='?clear_session=1' style='color:red'>Clear Session</a></p>";

// Clear session if requested
if (isset($_GET['clear_session'])) {
    session_unset();
    session_destroy();
    echo "<p>Session cleared. <a href='session_debug.php'>Refresh</a> to see changes.</p>";
}

// Add a link to go back to login
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
