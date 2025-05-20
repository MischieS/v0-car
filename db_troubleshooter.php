<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Troubleshooter</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1, h2, h3 { color: #2563eb; }
        .section { margin-bottom: 30px; padding: 20px; border-radius: 8px; background-color: #f8fafc; }
        .test-item { margin-bottom: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #dcfce7; color: #166534; }
        .error { background-color: #fee2e2; color: #991b1b; }
        .warning { background-color: #fef3c7; color: #92400e; }
        .info { background-color: #dbeafe; color: #1e40af; }
        .code { font-family: monospace; background: #f1f5f9; padding: 10px; border-radius: 5px; overflow: auto; }
        .action-btn { display: inline-block; margin: 10px 5px 10px 0; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; }
        .action-btn:hover { background: #1d4ed8; }
        .config-form { margin: 20px 0; padding: 15px; background: #f1f5f9; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; }
        .submit-btn { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .submit-btn:hover { background: #1d4ed8; }
        .step { margin-bottom: 10px; padding: 10px; background: #f8fafc; border-left: 4px solid #2563eb; }
        .step-number { font-weight: bold; color: #2563eb; }
        .step-content { margin-left: 25px; }
        .step-content img { max-width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Connection Troubleshooter</h1>
        <p>This tool will help you diagnose and fix database connection issues.</p>";

// Function to check if a program is running
function isProcessRunning($processName) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        $output = [];
        exec("tasklist | findstr /i \"$processName\"", $output);
        return count($output) > 0;
    } else {
        // Linux/Unix/Mac
        $output = [];
        exec("ps aux | grep -i \"$processName\" | grep -v grep", $output);
        return count($output) > 0;
    }
}

// Function to get MySQL service status on Windows
function getWindowsMySQLStatus() {
    $output = [];
    exec('sc query mysql', $output);
    $status = 'UNKNOWN';
    
    foreach ($output as $line) {
        if (strpos($line, 'STATE') !== false) {
            if (strpos($line, 'RUNNING') !== false) {
                $status = 'RUNNING';
            } else if (strpos($line, 'STOPPED') !== false) {
                $status = 'STOPPED';
            }
            break;
        }
    }
    
    return $status;
}

// Function to check if port is in use
function isPortInUse($port) {
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}

// Section 1: System Information
echo "<div class='section'>
        <h2>1. System Information</h2>";

// PHP Version
echo "<div class='test-item info'>PHP Version: " . phpversion() . "</div>";

// Operating System
echo "<div class='test-item info'>Operating System: " . PHP_OS . "</div>";

// Check MySQL extensions
if (extension_loaded('mysqli')) {
    echo "<div class='test-item success'>MySQLi extension is loaded</div>";
} else {
    echo "<div class='test-item error'>MySQLi extension is not loaded</div>";
    echo "<div class='test-item'>
            <p>You need to enable the MySQLi extension in your PHP configuration.</p>
            <p>Edit your php.ini file and uncomment or add this line:</p>
            <div class='code'>extension=mysqli</div>
          </div>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<div class='test-item success'>PDO MySQL extension is loaded</div>";
} else {
    echo "<div class='test-item warning'>PDO MySQL extension is not loaded (not required but recommended)</div>";
}

// Check MySQL server
$mysqlRunning = false;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Windows
    $mysqlStatus = getWindowsMySQLStatus();
    $mysqlProcessRunning = isProcessRunning('mysqld.exe') || isProcessRunning('mysql.exe');
    
    if ($mysqlStatus === 'RUNNING' || $mysqlProcessRunning) {
        echo "<div class='test-item success'>MySQL service appears to be running</div>";
        $mysqlRunning = true;
    } else {
        echo "<div class='test-item error'>MySQL service does not appear to be running</div>";
        echo "<div class='test-item'>
                <p>Start MySQL service with one of these methods:</p>
                <ol>
                    <li>Open Command Prompt as Administrator and run: <div class='code'>net start mysql</div></li>
                    <li>Or start MySQL from XAMPP/WAMP control panel</li>
                </ol>
              </div>";
    }
} else {
    // Linux/Unix/Mac
    $mysqlProcessRunning = isProcessRunning('mysqld');
    
    if ($mysqlProcessRunning) {
        echo "<div class='test-item success'>MySQL process appears to be running</div>";
        $mysqlRunning = true;
    } else {
        echo "<div class='test-item error'>MySQL process does not appear to be running</div>";
        echo "<div class='test-item'>
                <p>Start MySQL service with one of these commands:</p>
                <div class='code'>sudo service mysql start</div>
                <div class='code'>sudo systemctl start mysql</div>
              </div>";
    }
}

// Check MySQL port
if (isPortInUse(3306)) {
    echo "<div class='test-item success'>Port 3306 (MySQL default) is in use</div>";
} else {
    echo "<div class='test-item warning'>Port 3306 (MySQL default) is not in use</div>";
    echo "<div class='test-item'>
            <p>This could mean:</p>
            <ul>
                <li>MySQL is not running</li>
                <li>MySQL is using a different port</li>
            </ul>
          </div>";
}

echo "</div>";

// Section 2: Connection Test
echo "<div class='section'>
        <h2>2. Connection Test</h2>";

// Get current database settings
$currentServername = 'localhost';
$currentUsername = 'root';
$currentPassword = '';
$currentDbname = 'car_rental_db';

// Check if form was submitted to update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $currentServername = $_POST['servername'];
    $currentUsername = $_POST['username'];
    $currentPassword = $_POST['password'];
    $currentDbname = $_POST['dbname'];
    
    // Save settings to a temporary file
    $settings = [
        'servername' => $currentServername,
        'username' => $currentUsername,
        'password' => $currentPassword,
        'dbname' => $currentDbname
    ];
    
    file_put_contents(__DIR__ . '/temp_db_settings.php', '<?php return ' . var_export($settings, true) . ';');
    
    echo "<div class='test-item success'>Database settings updated</div>";
}

// Display current settings and form to update
echo "<div class='config-form'>
        <h3>Database Configuration</h3>
        <form method='post' action=''>
            <div class='form-group'>
                <label for='servername'>Server Name:</label>
                <input type='text' id='servername' name='servername' value='$currentServername' required>
            </div>
            <div class='form-group'>
                <label for='username'>Username:</label>
                <input type='text' id='username' name='username' value='$currentUsername' required>
            </div>
            <div class='form-group'>
                <label for='password'>Password:</label>
                <input type='password' id='password' name='password' value='$currentPassword'>
            </div>
            <div class='form-group'>
                <label for='dbname'>Database Name:</label>
                <input type='text' id='dbname' name='dbname' value='$currentDbname' required>
            </div>
            <button type='submit' name='update_settings' class='submit-btn'>Update Settings</button>
        </form>
      </div>";

// Test connection with current settings
echo "<h3>Connection Test Results</h3>";

// Test 1: Basic Connection
echo "<div class='test-item'>Testing connection to MySQL server...</div>";
try {
    $conn = new mysqli($currentServername, $currentUsername, $currentPassword);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<div class='test-item success'>Successfully connected to MySQL server</div>";
    
    // Test 2: Database Selection
    echo "<div class='test-item'>Testing database selection...</div>";
    if (!$conn->select_db($currentDbname)) {
        echo "<div class='test-item error'>Database selection failed: " . $conn->error . "</div>";
        echo "<div class='test-item'>
                <p>The database '$currentDbname' does not exist. Would you like to create it?</p>
                <form method='post' action=''>
                    <input type='hidden' name='create_db' value='1'>
                    <input type='hidden' name='servername' value='$currentServername'>
                    <input type='hidden' name='username' value='$currentUsername'>
                    <input type='hidden' name='password' value='$currentPassword'>
                    <input type='hidden' name='dbname' value='$currentDbname'>
                    <button type='submit' class='action-btn'>Create Database</button>
                </form>
              </div>";
    } else {
        echo "<div class='test-item success'>Successfully selected database '$currentDbname'</div>";
        
        // Test 3: Table Check
        echo "<div class='test-item'>Checking for required tables...</div>";
        $tables = ['users', 'user_tokens'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $missingTables[] = $table;
            }
        }
        
        if (count($missingTables) > 0) {
            echo "<div class='test-item error'>Missing tables: " . implode(', ', $missingTables) . "</div>";
            echo "<div class='test-item'>
                    <p>Would you like to create the missing tables?</p>
                    <form method='post' action=''>
                        <input type='hidden' name='create_tables' value='1'>
                        <input type='hidden' name='servername' value='$currentServername'>
                        <input type='hidden' name='username' value='$currentUsername'>
                        <input type='hidden' name='password' value='$currentPassword'>
                        <input type='hidden' name='dbname' value='$currentDbname'>
                        <button type='submit' class='action-btn'>Create Missing Tables</button>
                    </form>
                  </div>";
        } else {
            echo "<div class='test-item success'>All required tables exist</div>";
        }
    }
    
    // Close connection
    $conn->close();
} catch (Exception $e) {
    echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
    
    // Provide specific guidance based on error message
    if (strpos($e->getMessage(), "Access denied") !== false) {
        echo "<div class='test-item'>
                <p>Access denied error indicates incorrect username or password.</p>
                <p>Common solutions:</p>
                <ul>
                    <li>Double-check your username and password</li>
                    <li>For XAMPP/WAMP default installation, try username 'root' with empty password</li>
                    <li>Make sure the user has privileges to access the database</li>
                </ul>
              </div>";
    } else if (strpos($e->getMessage(), "Unknown host") !== false) {
        echo "<div class='test-item'>
                <p>Unknown host error indicates the server name is incorrect.</p>
                <p>Common solutions:</p>
                <ul>
                    <li>Try using '127.0.0.1' instead of 'localhost'</li>
                    <li>Check if MySQL is running on a different host or port</li>
                </ul>
              </div>";
    } else if (strpos($e->getMessage(), "Connection refused") !== false) {
        echo "<div class='test-item'>
                <p>Connection refused error indicates MySQL server is not running or not accepting connections.</p>
                <p>Common solutions:</p>
                <ul>
                    <li>Start MySQL server</li>
                    <li>Check firewall settings</li>
                    <li>Verify MySQL is configured to accept connections</li>
                </ul>
              </div>";
    }
}

// Process create database request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_db'])) {
    try {
        $conn = new mysqli($_POST['servername'], $_POST['username'], $_POST['password']);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        if ($conn->query("CREATE DATABASE IF NOT EXISTS `{$_POST['dbname']}`")) {
            echo "<div class='test-item success'>Database '{$_POST['dbname']}' created successfully</div>";
            echo "<div class='test-item'>
                    <p>Would you like to create the required tables?</p>
                    <form method='post' action=''>
                        <input type='hidden' name='create_tables' value='1'>
                        <input type='hidden' name='servername' value='{$_POST['servername']}'>
                        <input type='hidden' name='username' value='{$_POST['username']}'>
                        <input type='hidden' name='password' value='{$_POST['password']}'>
                        <input type='hidden' name='dbname' value='{$_POST['dbname']}'>
                        <button type='submit' class='action-btn'>Create Tables</button>
                    </form>
                  </div>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        $conn->close();
    } catch (Exception $e) {
        echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Process create tables request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables'])) {
    try {
        $conn = new mysqli($_POST['servername'], $_POST['username'], $_POST['password'], $_POST['dbname']);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(20) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<div class='test-item success'>Users table created successfully</div>";
            
            // Create default admin user
            $adminName = 'Admin User';
            $adminEmail = 'admin@example.com';
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $adminRole = 'admin';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $adminName, $adminEmail, $adminPassword, $adminRole);
            
            if ($stmt->execute()) {
                echo "<div class='test-item success'>Default admin user created successfully</div>";
                echo "<div class='test-item info'>Admin credentials: admin@example.com / admin123</div>";
            } else {
                echo "<div class='test-item warning'>Could not create admin user: " . $stmt->error . "</div>";
            }
            
            // Create test user
            $testName = 'Test User';
            $testEmail = 'test@example.com';
            $testPassword = password_hash('password123', PASSWORD_DEFAULT);
            $testRole = 'user';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $testName, $testEmail, $testPassword, $testRole);
            
            if ($stmt->execute()) {
                echo "<div class='test-item success'>Test user created successfully</div>";
                echo "<div class='test-item info'>Test user credentials: test@example.com / password123</div>";
            } else {
                echo "<div class='test-item warning'>Could not create test user: " . $stmt->error . "</div>";
            }
        } else {
            echo "<div class='test-item error'>Error creating users table: " . $conn->error . "</div>";
        }
        
        // Create user_tokens table
        $sql = "CREATE TABLE IF NOT EXISTS `user_tokens` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `expires` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<div class='test-item success'>User tokens table created successfully</div>";
        } else {
            echo "<div class='test-item error'>Error creating user_tokens table: " . $conn->error . "</div>";
        }
        
        // Update db_connect.php with new settings
        $dbConnectPath = __DIR__ . '/backend/db_connect.php';
        if (file_exists($dbConnectPath)) {
            $dbConnectContent = file_get_contents($dbConnectPath);
            
            // Update server name
            $dbConnectContent = preg_replace(
                "/\\\$servername\s*=\s*['\"](.*?)['\"]/",
                "\$servername = '{$_POST['servername']}'",
                $dbConnectContent
            );
            
            // Update username
            $dbConnectContent = preg_replace(
                "/\\\$username\s*=\s*['\"](.*?)['\"]/",
                "\$username = '{$_POST['username']}'",
                $dbConnectContent
            );
            
            // Update password
            $dbConnectContent = preg_replace(
                "/\\\$password\s*=\s*['\"](.*?)['\"]/",
                "\$password = '{$_POST['password']}'",
                $dbConnectContent
            );
            
            // Update database name
            $dbConnectContent = preg_replace(
                "/\\\$dbname\s*=\s*['\"](.*?)['\"]/",
                "\$dbname = '{$_POST['dbname']}'",
                $dbConnectContent
            );
            
            if (file_put_contents($dbConnectPath, $dbConnectContent)) {
                echo "<div class='test-item success'>Updated database connection settings in db_connect.php</div>";
            } else {
                echo "<div class='test-item error'>Could not update db_connect.php. Please update it manually.</div>";
            }
        }
        
        $conn->close();
    } catch (Exception $e) {
        echo "<div class='test-item error'>Error: " . $e->getMessage() . "</div>";
    }
}

echo "</div>";

// Section 3: Common Solutions
echo "<div class='section'>
        <h2>3. Common Solutions</h2>";

// XAMPP/WAMP specific solutions
echo "<h3>If you're using XAMPP/WAMP:</h3>
      <div class='step'>
        <span class='step-number'>Step 1:</span> Make sure XAMPP/WAMP is running
        <div class='step-content'>
            <p>Open the XAMPP/WAMP control panel and ensure that MySQL service is started.</p>
        </div>
      </div>
      <div class='step'>
        <span class='step-number'>Step 2:</span> Check MySQL credentials
        <div class='step-content'>
            <p>Default credentials are usually:</p>
            <ul>
                <li>Server: localhost</li>
                <li>Username: root</li>
                <li>Password: (empty)</li>
            </ul>
        </div>
      </div>
      <div class='step'>
        <span class='step-number'>Step 3:</span> Check phpMyAdmin
        <div class='step-content'>
            <p>Try accessing phpMyAdmin to verify MySQL is working:</p>
            <ul>
                <li>XAMPP: <a href='http://localhost/phpmyadmin/' target='_blank'>http://localhost/phpmyadmin/</a></li>
                <li>WAMP: <a href='http://localhost/phpmyadmin/' target='_blank'>http://localhost/phpmyadmin/</a></li>
            </ul>
        </div>
      </div>";

// General solutions
echo "<h3>General Solutions:</h3>
      <div class='step'>
        <span class='step-number'>Step 1:</span> Try alternative hostname
        <div class='step-content'>
            <p>Instead of 'localhost', try using '127.0.0.1' as the server name.</p>
        </div>
      </div>
      <div class='step'>
        <span class='step-number'>Step 2:</span> Check MySQL service
        <div class='step-content'>
            <p>Make sure MySQL service is running:</p>
            <ul>
                <li>Windows: Open Services (services.msc) and check if MySQL service is running</li>
                <li>Linux: Run <code>sudo systemctl status mysql</code> or <code>sudo service mysql status</code></li>
                <li>Mac: Check System Preferences > MySQL</li>
            </ul>
        </div>
      </div>
      <div class='step'>
        <span class='step-number'>Step 3:</span> Verify database exists
        <div class='step-content'>
            <p>Make sure the database 'car_rental_db' exists. You can create it using phpMyAdmin or MySQL command line:</p>
            <div class='code'>CREATE DATABASE car_rental_db;</div>
        </div>
      </div>
      <div class='step'>
        <span class='step-number'>Step 4:</span> Check user privileges
        <div class='step-content'>
            <p>Ensure the MySQL user has proper privileges:</p>
            <div class='code'>GRANT ALL PRIVILEGES ON car_rental_db.* TO 'root'@'localhost';</div>
            <div class='code'>FLUSH PRIVILEGES;</div>
        </div>
      </div>";

echo "</div>";

// Section 4: Next Steps
echo "<div class='section'>
        <h2>4. Next Steps</h2>
        <div class='test-item info'>
            <p>After fixing the database connection issues:</p>
            <ol>
                <li>Try logging in with the test user: test@example.com / password123</li>
                <li>Or the admin user: admin@example.com / admin123</li>
                <li>Check the database error logs for additional information</li>
            </ol>
        </div>
        <div style='margin-top: 20px;'>
            <a href='backend/db_setup_fix.php' class='action-btn'>Run Database Setup</a>
            <a href='login.php' class='action-btn'>Go to Login Page</a>
            <a href='index.php' class='action-btn'>Go to Home Page</a>
        </div>
      </div>";

echo "</div></body></html>";
?>
