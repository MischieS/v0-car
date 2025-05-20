<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Database Error - Car Rental</title>
    <?php include('assets/includes/header_link.php'); ?>
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            padding: 20px;
        }
        .error-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 600px;
            padding: 40px;
            text-align: center;
        }
        .error-icon {
            font-size: 60px;
            color: #ef4444;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        .error-message {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border: none;
            height: 48px;
            border-radius: 8px;
            font-weight: 600;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #1e293b;
            border: none;
            height: 48px;
            border-radius: 8px;
            font-weight: 600;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }
        .debug-info {
            margin-top: 30px;
            text-align: left;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
        }
        .debug-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .debug-info pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-database"></i>
            </div>
            <h1 class="error-title">Database Connection Error</h1>
            <p class="error-message">
                We're having trouble connecting to our database. This could be due to maintenance or a temporary issue.
                Please try again later or contact support if the problem persists.
            </p>
            
            <div class="error-actions">
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-home mr-2"></i> Return to Home
                </a>
                <a href="javascript:window.location.reload()" class="btn-secondary">
                    <i class="fas fa-sync-alt mr-2"></i> Try Again
                </a>
            </div>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <div class="debug-info">
                <h3>Debug Information (Admin Only)</h3>
                <p>Check the database error log at: <code>backend/database_errors.log</code></p>
                <p>Common issues:</p>
                <ul>
                    <li>Database server is not running</li>
                    <li>Incorrect database credentials</li>
                    <li>Missing database or tables</li>
                    <li>Insufficient permissions</li>
                </ul>
                <p>
                    <a href="backend/db_setup.php" style="color: #3b82f6;">Run Database Setup</a> | 
                    <a href="backend/db_check.php" style="color: #3b82f6;">Check Database Status</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include('assets/includes/footer_link.php'); ?>
</body>
</html>
