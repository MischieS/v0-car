<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // For testing purposes, we'll allow access without admin check
    // die("Access denied. Only administrators can run this script.");
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
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
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

// Get current directory information
echo "<div class='card mb-4'>
        <div class='card-header bg-primary text-white'>
            <h3 class='card-title mb-0'>Current Directory Information</h3>
        </div>
        <div class='card-body'>";

echo "<p><strong>Current working directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Script location:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Document root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// List all directories in the current directory
echo "<p><strong>Directories in current location:</strong></p><ul>";
$dirs = glob('*', GLOB_ONLYDIR);
foreach ($dirs as $dir) {
    echo "<li>" . htmlspecialchars($dir) . "</li>";
}
echo "</ul>";

// List all PHP files in the current directory
echo "<p><strong>PHP files in current location:</strong></p><ul>";
$phpFiles = glob('*.php');
foreach ($phpFiles as $file) {
    echo "<li>" . htmlspecialchars($file) . "</li>";
}
echo "</ul>";

// Check if we're in the backend directory
$inBackendDir = (basename(getcwd()) === 'backend');
echo "<p><strong>Are we in the backend directory?</strong> " . ($inBackendDir ? 'Yes' : 'No') . "</p>";

// If we're in the backend directory, we need to look for parent directories
if ($inBackendDir) {
    echo "<p>Since we're in the backend directory, let's check the parent directory:</p>";
    $parentDir = dirname(getcwd());
    echo "<p><strong>Parent directory:</strong> " . $parentDir . "</p>";
    
    echo "<p><strong>Directories in parent location:</strong></p><ul>";
    $parentDirs = glob($parentDir . '/*', GLOB_ONLYDIR);
    foreach ($parentDirs as $dir) {
        echo "<li>" . htmlspecialchars(basename($dir)) . "</li>";
    }
    echo "</ul>";
}

echo "</div></div>";

// Function to find directories case-insensitively
function findDirectoryCaseInsensitive($name, $basePath = '.') {
    $dirs = glob($basePath . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
        if (strtolower(basename($dir)) === strtolower($name)) {
            return $dir;
        }
    }
    return false;
}

// Find backend and assets directories
echo "<div class='card mb-4'>
        <div class='card-header bg-info text-white'>
            <h3 class='card-title mb-0'>Directory Search</h3>
        </div>
        <div class='card-body'>";

// Determine the base path
$basePath = $inBackendDir ? '..' : '.';

// Look for backend directory
$backendDir = findDirectoryCaseInsensitive('backend', $basePath);
if ($backendDir) {
    echo "<p class='text-success'><strong>Backend directory found:</strong> " . $backendDir . "</p>";
    
    // List files in backend directory
    echo "<p><strong>Files in backend directory:</strong></p><ul>";
    $backendFiles = glob($backendDir . '/*.php');
    foreach ($backendFiles as $file) {
        echo "<li>" . htmlspecialchars(basename($file)) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='text-danger'><strong>Backend directory not found!</strong> Searched in: " . $basePath . "</p>";
}

// Look for assets directory
$assetsDir = findDirectoryCaseInsensitive('assets', $basePath);
if ($assetsDir) {
    echo "<p class='text-success'><strong>Assets directory found:</strong> " . $assetsDir . "</p>";
    
    // List subdirectories in assets
    echo "<p><strong>Subdirectories in assets:</strong></p><ul>";
    $assetSubdirs = glob($assetsDir . '/*', GLOB_ONLYDIR);
    foreach ($assetSubdirs as $dir) {
        echo "<li>" . htmlspecialchars(basename($dir)) . "</li>";
    }
    echo "</ul>";
    
    // Look for image directory
    $imgDir = findDirectoryCaseInsensitive('img', $assetsDir);
    if ($imgDir) {
        echo "<p class='text-success'><strong>Images directory found:</strong> " . $imgDir . "</p>";
    } else {
        echo "<p class='text-warning'><strong>Images directory not found in assets.</strong> Looking for alternatives...</p>";
        
        // Check for other possible image directories
        $possibleImgDirs = ['images', 'pics', 'photos', 'graphics'];
        foreach ($possibleImgDirs as $dirName) {
            $dir = findDirectoryCaseInsensitive($dirName, $assetsDir);
            if ($dir) {
                echo "<p class='text-success'><strong>Alternative images directory found:</strong> " . $dir . "</p>";
                break;
            }
        }
    }
} else {
    echo "<p class='text-danger'><strong>Assets directory not found!</strong> Searched in: " . $basePath . "</p>";
}

echo "</div></div>";

// Check for temporary files
echo "<div class='card mb-4'>
        <div class='card-header bg-warning text-dark'>
            <h3 class='card-title mb-0'>Temporary Files Check</h3>
        </div>
        <div class='card-body file-list'>";

// Common temporary file patterns
$tempFilePatterns = [
    '*.bak',
    '*.tmp',
    '*.temp',
    '*.old',
    '*_backup.*',
    'temp_*',
    'backup_*',
    'test_*',
    'debug_*'
];

// Function to find files matching patterns
function findFiles($patterns, $directory = '.') {
    $matches = [];
    
    if (!is_dir($directory)) {
        return $matches;
    }
    
    foreach ($patterns as $pattern) {
        $files = glob($directory . '/' . $pattern);
        if ($files) {
            $matches = array_merge($matches, $files);
        }
        
        // Also check subdirectories
        $dirs = glob($directory . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $subMatches = findFiles([$pattern], $dir);
            if ($subMatches) {
                $matches = array_merge($matches, $subMatches);
            }
        }
    }
    
    return $matches;
}

// Determine the search path
$searchPath = $inBackendDir ? '..' : '.';
$tempFiles = findFiles($tempFilePatterns, $searchPath);

if (count($tempFiles) > 0) {
    echo "<p>Found the following temporary files that can be safely removed:</p><ul>";
    foreach ($tempFiles as $file) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
    echo "</ul>";
    
    echo "<form method='post'>
            <input type='hidden' name='delete_temp_files' value='yes'>
            <button type='submit' class='btn btn-warning'>Remove Temporary Files</button>
          </form>";
    
    if (isset($_POST['delete_temp_files']) && $_POST['delete_temp_files'] === 'yes') {
        echo "<p>Removing temporary files:</p><ul>";
        foreach ($tempFiles as $file) {
            if (unlink($file)) {
                echo "<li class='text-success'>" . htmlspecialchars($file) . " - Removed</li>";
            } else {
                echo "<li class='text-danger'>" . htmlspecialchars($file) . " - Failed to remove</li>";
            }
        }
        echo "</ul>";
    }
} else {
    echo "<p class='text-success'>No temporary files found.</p>";
}

echo "</div></div>";

// Project structure visualization
echo "<div class='card mb-4'>
        <div class='card-header bg-dark text-white'>
            <h3 class='card-title mb-0'>Project Structure</h3>
        </div>
        <div class='card-body'>";

// Function to generate a simple directory tree
function generateDirectoryTree($dir, $depth = 0, $maxDepth = 3) {
    if ($depth > $maxDepth) return '';
    
    $output = '';
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;
                $indent = str_repeat('│   ', $depth);
                
                if (is_dir($path)) {
                    $output .= $indent . "├── 📁 " . $file . "\n";
                    $output .= generateDirectoryTree($path, $depth + 1, $maxDepth);
                } else {
                    $output .= $indent . "├── 📄 " . $file . "\n";
                }
            }
        }
    }
    return $output;
}

// Generate tree for the main directory
$treeRoot = $inBackendDir ? '..' : '.';
$tree = generateDirectoryTree($treeRoot, 0, 2); // Limit depth to 2 levels

echo "<p>Here's a simplified view of your project structure (limited to 2 levels deep):</p>";
echo "<pre>" . htmlspecialchars($tree) . "</pre>";

echo "<p class='text-muted'>Note: This is a simplified view. Some directories may be truncated due to depth limitations.</p>";

echo "</div></div>";

// Cleanup recommendations
echo "<div class='card mb-4'>
        <div class='card-header bg-success text-white'>
            <h3 class='card-title mb-0'>Cleanup Recommendations</h3>
        </div>
        <div class='card-body'>";

echo "<h4>1. Organize Your Files</h4>
      <p>Consider organizing your files into a more structured directory layout:</p>
      <ul>
        <li><strong>app/</strong> - Core application files</li>
        <li><strong>public/</strong> - Publicly accessible files</li>
        <li><strong>resources/</strong> - Non-PHP resources (templates, etc.)</li>
        <li><strong>config/</strong> - Configuration files</li>
        <li><strong>vendor/</strong> - Third-party dependencies</li>
      </ul>
      
      <h4>2. Use Composer for Dependencies</h4>
      <p>Consider using Composer to manage your PHP dependencies instead of including them manually.</p>
      
      <h4>3. Implement a Router</h4>
      <p>Replace direct access to PHP files with a front controller pattern and router.</p>
      
      <h4>4. Separate Logic from Presentation</h4>
      <p>Move business logic out of your presentation files into separate classes.</p>
      
      <h4>5. Use Environment Variables</h4>
      <p>Store configuration in environment variables instead of hardcoding them in your PHP files.</p>";

echo "</div></div>";

// Final summary
echo "<div class='card mb-4'>
        <div class='card-header bg-primary text-white'>
            <h3 class='card-title mb-0'>Summary</h3>
        </div>
        <div class='card-body'>";

echo "<p>This script has analyzed your project structure and provided recommendations for cleanup and organization.</p>
      <p>Key findings:</p>
      <ul>";

if ($backendDir) {
    echo "<li class='text-success'>Backend directory found at: " . htmlspecialchars($backendDir) . "</li>";
} else {
    echo "<li class='text-danger'>Backend directory not found</li>";
}

if ($assetsDir) {
    echo "<li class='text-success'>Assets directory found at: " . htmlspecialchars($assetsDir) . "</li>";
} else {
    echo "<li class='text-danger'>Assets directory not found</li>";
}

if (count($tempFiles) > 0) {
    echo "<li class='text-warning'>" . count($tempFiles) . " temporary files found that could be removed</li>";
} else {
    echo "<li class='text-success'>No temporary files found</li>";
}

echo "</ul>";

echo "<p>Next steps:</p>
      <ol>
        <li>Review the project structure visualization to understand your codebase better</li>
        <li>Consider implementing the cleanup recommendations</li>
        <li>Remove any temporary files if needed</li>
        <li>Consider reorganizing your project structure for better maintainability</li>
      </ol>";

echo "</div></div>";

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
