<?php
// Start session
session_start();

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Session Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #2563eb; }
        .session-info { background: #f8fafc; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .session-var { margin-bottom: 10px; }
        .key { font-weight: bold; color: #1e293b; }
        .value { color: #64748b; }
        .status { padding: 10px; border-radius: 5px; margin-top: 20px; }
        .logged-in { background: #dcfce7; color: #166534; }
        .not-logged-in { background: #fee2e2; color: #991b1b; }
        .action-links { margin-top: 20px; }
        .action-links a { display: inline-block; margin-right: 10px; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; }
        .action-links a:hover { background: #1d4ed8; }
        .action-links a.danger { background: #ef4444; }
        .action-links a.danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Session Check</h1>";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Display session status
echo "<div class='status " . ($isLoggedIn ? "logged-in" : "not-logged-in") . "'>";
echo $isLoggedIn ? "✅ User is logged in (user_id: {$_SESSION['user_id']})" : "❌ User is not logged in";
echo "</div>";

// Display all session variables
echo "<h2>Session Variables</h2>";
echo "<div class='session-info'>";
if (empty($_SESSION)) {
    echo "<p>No session variables set.</p>";
} else {
    foreach ($_SESSION as $key => $value) {
        echo "<div class='session-var'><span class='key'>{$key}:</span> <span class='value'>" . (is_array($value) ? json_encode($value) : htmlspecialchars($value)) . "</span></div>";
    }
}
echo "</div>";

// Display cookie information
echo "<h2>Cookies</h2>";
echo "<div class='session-info'>";
if (empty($_COOKIE)) {
    echo "<p>No cookies set.</p>";
} else {
    foreach ($_COOKIE as $key => $value) {
        // Don't show the actual value for sensitive cookies
        $isSensitive = (stripos($key, 'sess') !== false || stripos($key, 'auth') !== false || stripos($key, 'token') !== false);
        echo "<div class='session-var'><span class='key'>{$key}:</span> <span class='value'>" . 
            ($isSensitive ? "[Hidden for security]" : htmlspecialchars($value)) . 
            "</span></div>";
    }
}
echo "</div>";

// Session configuration
echo "<h2>Session Configuration</h2>";
echo "<div class='session-info'>";
echo "<div class='session-var'><span class='key'>session.save_path:</span> <span class='value'>" . ini_get('session.save_path') . "</span></div>";
echo "<div class='session-var'><span class='key'>session.name:</span> <span class='value'>" . session_name() . "</span></div>";
echo "<div class='session-var'><span class='key'>session.cookie_lifetime:</span> <span class='value'>" . ini_get('session.cookie_lifetime') . "</span></div>";
echo "<div class='session-var'><span class='key'>session.cookie_path:</span> <span class='value'>" . ini_get('session.cookie_path') . "</span></div>";
echo "<div class='session-var'><span class='key'>session.cookie_domain:</span> <span class='value'>" . ini_get('session.cookie_domain') . "</span></div>";
echo "<div class='session-var'><span class='key'>session.cookie_secure:</span> <span class='value'>" . ini_get('session.cookie_secure') . "</span></div>";
echo "<div class='session-var'><span class='key'>session.cookie_httponly:</span> <span class='value'>" . ini_get('session.cookie_httponly') . "</span></div>";
echo "<div class='session-var'><span class='key'>session.use_strict_mode:</span> <span class='value'>" . ini_get('session.use_strict_mode') . "</span></div>";
echo "</div>";

// Action links
echo "<div class='action-links'>";
echo "<a href='index.php'>Go to Homepage</a>";
if ($isLoggedIn) {
    echo "<a href='backend/logout.php'>Logout</a>";
} else {
    echo "<a href='login.php'>Login</a>";
}
echo "<a href='session_check.php?clear=1' class='danger'>Clear Session</a>";
echo "</div>";

// Clear session if requested
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session cleared!'); window.location.href = 'session_check.php';</script>";
}

echo "</div></body></html>";
?>
