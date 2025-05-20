<?php
// Start session
session_start();

// Include database connection
require_once 'backend/db_connect.php';

// Function to create a test user
function createTestUser($conn) {
    // Check if test user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $testEmail = 'test@example.com';
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create test user
        $name = 'Test User';
        $email = 'test@example.com';
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $role = 'user';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        
        return "Test user created: test@example.com / password123";
    } else {
        return "Test user already exists: test@example.com / password123";
    }
}

// Create admin user if it doesn't exist
function createAdminUser($conn) {
    // Check if admin user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $adminEmail = 'admin@example.com';
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create admin user
        $name = 'Admin User';
        $email = 'admin@example.com';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        
        return "Admin user created: admin@example.com / admin123";
    } else {
        return "Admin user already exists: admin@example.com / admin123";
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_test_user'])) {
        $message = createTestUser($conn);
    } elseif (isset($_POST['create_admin_user'])) {
        $message = createAdminUser($conn);
    } elseif (isset($_POST['test_login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user["password"])) {
                $message = "Login successful! User: " . $user['name'] . " (Role: " . $user['role'] . ")";
            } else {
                $message = "Login failed: Invalid password";
            }
        } else {
            $message = "Login failed: User not found";
        }
    }
}

// Get all users
$users = [];
$result = $conn->query("SELECT id, name, email, role FROM users");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1, h2 {
            color: #2563eb;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            background: #e0f2fe;
            border-left: 4px solid #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8fafc;
        }
        form {
            margin-bottom: 20px;
        }
        button, input[type="submit"] {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover, input[type="submit"]:hover {
            background: #1d4ed8;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login Test Page</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Create Test Users</h2>
            <form method="post">
                <button type="submit" name="create_test_user">Create Test User</button>
                <button type="submit" name="create_admin_user">Create Admin User</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Test Login</h2>
            <form method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="test@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="password123" required>
                </div>
                <input type="submit" name="test_login" value="Test Login">
            </form>
            
            <p>Or use the regular login page: <a href="login.php">Go to Login Page</a></p>
        </div>
        
        <div class="card">
            <h2>Existing Users</h2>
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found in the database.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Session Information</h2>
            <?php if (count($_SESSION) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION as $key => $value): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($key); ?></td>
                                <td><?php echo is_array($value) ? json_encode($value) : htmlspecialchars($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No session variables set.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
