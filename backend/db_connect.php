<?php
// db_connect.php
mysqli_report(MYSQLI_REPORT_OFF);
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'car_rental_db';

function connectDatabase(): mysqli
{
    global $servername, $username, $password, $dbname;
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_errno) {
        die('Connection failed: ' . $conn->connect_error);
    }

    if (!$conn->select_db($dbname)) {
        require __DIR__ . '/db_setup.php';
        if (!$conn->select_db($dbname)) {
            die('Select DB failed: ' . $conn->error);
        }
    }

    $tables = ['users', 'locations', 'cars', 'reservations'];
    foreach ($tables as $t) {
        $res = $conn->query("SHOW TABLES LIKE '{$t}'");
        if (!$res || $res->num_rows === 0) {
            require __DIR__ . '/db_setup.php';
            if (!$conn->select_db($dbname)) {
                die('Select DB failed: ' . $conn->error);
            }
            break;
        }
    }

    return $conn;
}

$conn = connectDatabase();

function normalizeMobile(string $raw): string
{
    $digits = preg_replace('//D/', '', $raw);
    if (strlen($digits) === 10) {
        $digits = '90' . $digits;
    } elseif (!str_starts_with($digits, '90')) {
        $digits = '90' . $digits;
    }
    $digits = substr($digits, 0, 12);
    $formatted = '+' . substr($digits, 0, 2)
               . '(' . substr($digits, 2, 3) . ')'
               . substr($digits, 5);
    return preg_match('/^/+90/(/d{3}/)/d{7}$/', $formatted)
        ? $formatted
        : '';
}
