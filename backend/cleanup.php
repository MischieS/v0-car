<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied. Only administrators can run this script.");
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Project Cleanup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .file-list { max-height: 400px; overflow-y: auto; }
        .deleted { text-decoration: line-through; color: #dc3545; }
        .kept { color: #198754; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Project Cleanup</h1>";

// Function to log messages
function logMessage($message, $type = 'info') {
    $class = match($type) {
        'success' => 'text-success',
        'error' => 'text-danger',
        'warning' => 'text-warning',
        default => 'text-info'
    };
    echo "<div class='$class'>$message</div>";
}

// List of unnecessary files that can be safely removed
$unnecessaryFiles = [
    // Duplicate setup files
    'backend/db_setup_additional.php' => 'Replaced by db_setup_combined.php',
    'backend/db_setup_fix.php' => 'Replaced by db_setup_combined.php',
    'backend/add_featured_column.php' => 'Functionality included in db_setup_combined.php',
    'backend/db_test.php' => 'Replaced by db_check.php',
    'backend/db_connect_alt.php' => 'No longer needed',
    'backend/db_connect_simple.php' => 'No longer needed',
    
    // Temporary fix files
    'fix_database.php' => 'No longer needed after database setup',
    'db_troubleshooter.php' => 'Replaced by db_check.php',
    'db_error.php' => 'Error handling improved in main files',
    
    // Debug/test files
    'login_test.php' => 'Testing file not needed in production',
    'test_login.php' => 'Testing file not needed in production',
    'session_debug.php' => 'Debug file not needed in production',
    'header_check.php' => 'Debug file not needed in production',
    
    // Old versions or backups
    'backend/db_connect.php.bak' => 'Backup file',
    'backend/process_login.php.bak' => 'Backup file'
];

// Check if we should actually delete files
$deleteFiles = isset($_POST['delete']) && $_POST['delete'] === 'yes';

echo "<div class='card mb-4'>
        <div class='card-header bg-warning text-dark'>
            <h3 class='card-title mb-0'>Files to be Removed</h3>
        </div>
        <div class='card-body file-list'>";

// Check each file
foreach ($unnecessaryFiles as $file => $reason) {
    if (file_exists($file)) {
        if ($deleteFiles) {
            if (unlink($file)) {
                echo "<div class='deleted'><strong>$file</strong> - $reason - <span class='text-danger'>DELETED</span></div>";
            } else {
                echo "<div><strong>$file</strong> - $reason - <span class='text-danger'>FAILED TO DELETE</span></div>";
            }
        } else {
            echo "<div><strong>$file</strong> - $reason</div>";
        }
    } else {
        echo "<div class='text-muted'><strong>$file</strong> - Not found</div>";
    }
}

echo "</div></div>";

// List of files to keep
$filesToKeep = [
    'backend/db_setup_combined.php' => 'Combined setup file',
    'backend/db_check.php' => 'Database structure checker',
    'backend/db_connect.php' => 'Main database connection file',
    'backend/process_login.php' => 'Main login processing file',
    'backend/log_activity.php' => 'Activity logging functionality',
    'backend/logout.php' => 'Logout functionality'
];

echo "<div class='card mb-4'>
        <div class='card-header bg-success text-white'>
            <h3 class='card-title mb-0'>Essential Files to Keep</h3>
        </div>
        <div class='card-body file-list'>";

// Check each file to keep
foreach ($filesToKeep as $file => $reason) {
    if (file_exists($file)) {
        echo "<div class='kept'><strong>$file</strong> - $reason</div>";
    } else {
        echo "<div class='text-danger'><strong>$file</strong> - MISSING! - $reason</div>";
    }
}

echo "</div></div>";

// Form for actual deletion
if (!$deleteFiles) {
    echo "<div class='alert alert-warning'>
            <h4>Warning!</h4>
            <p>This script will delete the files listed above. Make sure you have a backup before proceeding.</p>
            <form method='post'>
                <input type='hidden' name='delete' value='yes'>
                <button type='submit' class='btn btn-danger'>Delete Unnecessary Files</button>
                <a href='../index.php' class='btn btn-secondary ms-2'>Cancel</a>
            </form>
          </div>";
} else {
    echo "<div class='alert alert-success'>
            <h4>Cleanup Complete!</h4>
            <p>Unnecessary files have been removed from the project.</p>
            <a href='../index.php' class='btn btn-primary'>Return to Homepage</a>
          </div>";
}

// Check for duplicate code sections
echo "<h2 class='mt-4'>Code Duplication Check</h2>";

// Function to check for similar code
function checkCodeDuplication($directory, $extensions = ['php', 'js', 'css']) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    $codeHashes = [];
    $duplicates = [];
    
    foreach ($files as $file) {
        if ($file->isFile()) {
            $extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if (in_array($extension, $extensions)) {
                $content = file_get_contents($file->getPathname());
                $functions = extractFunctions($content, $extension);
                
                foreach ($functions as $function) {
                    $hash = md5(trim($function));
                    if (strlen(trim($function)) > 100) { // Only check substantial functions
                        if (isset($codeHashes[$hash])) {
                            $duplicates[] = [
                                'original' => $codeHashes[$hash],
                                'duplicate' => $file->getPathname(),
                                'snippet' => substr($function, 0, 100) . '...'
                            ];
                        } else {
                            $codeHashes[$hash] = $file->getPathname();
                        }
                    }
                }
            }
        }
    }
    
    return $duplicates;
}

// Simple function to extract functions/methods from code
function extractFunctions($content, $extension) {
    $functions = [];
    
    if ($extension === 'php') {
        // Extract PHP functions
        preg_match_all('/function\s+[\w_]+\s*$$[^)]*$$\s*{(?:[^{}]|(?R))*}/s', $content, $matches);
        $functions = array_merge($functions, $matches[0]);
        
        // Extract PHP methods
        preg_match_all('/public|private|protected\s+function\s+[\w_]+\s*$$[^)]*$$\s*{(?:[^{}]|(?R))*}/s', $content, $matches);
        $functions = array_merge($functions, $matches[0]);
    } elseif ($extension === 'js') {
        // Extract JS functions
        preg_match_all('/function\s+[\w_]+\s*$$[^)]*$$\s*{(?:[^{}]|(?R))*}/s', $content, $matches);
        $functions = array_merge($functions, $matches[0]);
        
        // Extract JS arrow functions
        preg_match_all('/const\s+[\w_]+\s*=\s*$$[^)]*$$\s*=>\s*{(?:[^{}]|(?R))*}/s', $content, $matches);
        $functions = array_merge($functions, $matches[0]);
    }
    
    return $functions;
}

// Check for code duplication
$duplicates = checkCodeDuplication('..');

if (count($duplicates) > 0) {
    echo "<div class='alert alert-info'>
            <h4>Potential Code Duplication Found</h4>
            <p>The following files may contain duplicate code:</p>
            <ul class='list-group'>";
    
    foreach ($duplicates as $duplicate) {
        echo "<li class='list-group-item'>
                <div><strong>Original:</strong> {$duplicate['original']}</div>
                <div><strong>Duplicate:</strong> {$duplicate['duplicate']}</div>
                <div class='text-muted'><small>Code snippet: {$duplicate['snippet']}</small></div>
              </li>";
    }
    
    echo "</ul>
            <p class='mt-3'>Consider refactoring these sections into reusable functions or classes.</p>
          </div>";
} else {
    echo "<div class='alert alert-success'>No significant code duplication found.</div>";
}

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
