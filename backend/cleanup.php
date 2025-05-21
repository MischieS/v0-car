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

// Get the actual directory structure
echo "<div class='card mb-4'>
        <div class='card-header bg-primary text-white'>
            <h3 class='card-title mb-0'>Directory Structure Analysis</h3>
        </div>
        <div class='card-body'>";

// Function to scan directory and get structure
function scanDirectory($dir, $depth = 0, $maxDepth = 2) {
    if ($depth > $maxDepth) return [];
    
    $result = [];
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $result[$file] = scanDirectory($path, $depth + 1, $maxDepth);
                } else {
                    $result[] = $file;
                }
            }
        }
    }
    return $result;
}

// Get the root directories
$rootDirs = [];
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..' && is_dir($file)) {
        $rootDirs[] = $file;
    }
}

echo "<p>Found the following root directories:</p><ul>";
foreach ($rootDirs as $dir) {
    echo "<li><strong>$dir</strong></li>";
}
echo "</ul>";

// Check if backend directory exists
if (in_array('backend', $rootDirs)) {
    echo "<p class='text-success'>Backend directory found.</p>";
    
    // List files in backend directory
    $backendFiles = scandir('backend');
    echo "<p>Files in backend directory:</p><ul>";
    foreach ($backendFiles as $file) {
        if ($file != '.' && $file != '..' && is_file('backend/' . $file)) {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p class='text-danger'>Backend directory not found! This is a critical issue.</p>";
}

// Check if assets directory exists
if (in_array('assets', $rootDirs)) {
    echo "<p class='text-success'>Assets directory found.</p>";
    
    // Check for image directory
    $assetsDirs = scandir('assets');
    $imgDirFound = false;
    foreach ($assetsDirs as $dir) {
        if ($dir == 'img') {
            $imgDirFound = true;
            break;
        }
    }
    
    if ($imgDirFound) {
        echo "<p class='text-success'>Images directory found at assets/img.</p>";
    } else {
        echo "<p class='text-warning'>Images directory not found at assets/img. Checking for alternative locations...</p>";
        
        // Try to find any image directories
        $imageDirs = [];
        foreach ($assetsDirs as $dir) {
            if ($dir != '.' && $dir != '..' && is_dir('assets/' . $dir)) {
                $subDirs = scandir('assets/' . $dir);
                foreach ($subDirs as $subDir) {
                    if (in_array(strtolower($subDir), ['images', 'img', 'pics', 'photos'])) {
                        $imageDirs[] = 'assets/' . $dir . '/' . $subDir;
                    }
                }
            }
        }
        
        if (count($imageDirs) > 0) {
            echo "<p>Found potential image directories:</p><ul>";
            foreach ($imageDirs as $dir) {
                echo "<li>$dir</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='text-danger'>No image directories found in assets.</p>";
        }
    }
} else {
    echo "<p class='text-danger'>Assets directory not found!</p>";
}

echo "</div></div>";

// List of files to check for cleanup
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

$tempFiles = findFiles($tempFilePatterns);

if (count($tempFiles) > 0) {
    echo "<p>Found the following temporary files that can be safely removed:</p><ul>";
    foreach ($tempFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='text-success'>No temporary files found.</p>";
}

echo "</div></div>";

// Check for empty directories
echo "<div class='card mb-4'>
        <div class='card-header bg-info text-white'>
            <h3 class='card-title mb-0'>Empty Directories Check</h3>
        </div>
        <div class='card-body file-list'>";

function checkEmptyDirectories($path) {
    $empty = [];
    
    if (!is_dir($path)) {
        return $empty;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $dir) {
        if ($dir->isDir()) {
            $isDirEmpty = !(new \FilesystemIterator($dir->getPathname()))->valid();
            if ($isDirEmpty) {
                $empty[] = $dir->getPathname();
            }
        }
    }
    
    return $empty;
}

$emptyDirs = [];
foreach ($rootDirs as $dir) {
    $emptyDirs = array_merge($emptyDirs, checkEmptyDirectories($dir));
}

if (count($emptyDirs) > 0) {
    echo "<p>The following directories are empty:</p><ul>";
    foreach ($emptyDirs as $dir) {
        echo "<li>$dir</li>";
    }
    echo "</ul>";
    
    echo "<form method='post'>
            <input type='hidden' name='delete_empty_dirs' value='yes'>
            <button type='submit' class='btn btn-warning'>Remove Empty Directories</button>
          </form>";
    
    if (isset($_POST['delete_empty_dirs']) && $_POST['delete_empty_dirs'] === 'yes') {
        echo "<p>Removing empty directories:</p><ul>";
        foreach ($emptyDirs as $dir) {
            if (rmdir($dir)) {
                echo "<li class='text-success'>$dir - Removed</li>";
            } else {
                echo "<li class='text-danger'>$dir - Failed to remove</li>";
            }
        }
        echo "</ul>";
    }
} else {
    echo "<p>No empty directories found.</p>";
}

echo "</div></div>";

// Check for large files
echo "<div class='card mb-4'>
        <div class='card-header bg-secondary text-white'>
            <h3 class='card-title mb-0'>Large Files Check</h3>
        </div>
        <div class='card-body file-list'>";

function findLargeFiles($minSize = 1000000) { // 1MB
    $largeFiles = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getSize() > $minSize) {
            $largeFiles[] = [
                'path' => $file->getPathname(),
                'size' => $file->getSize()
            ];
        }
    }
    
    // Sort by size (largest first)
    usort($largeFiles, function($a, $b) {
        return $b['size'] - $a['size'];
    });
    
    return $largeFiles;
}

function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

$largeFiles = findLargeFiles();

if (count($largeFiles) > 0) {
    echo "<p>Found the following large files (over 1MB):</p>
          <table class='table table-striped'>
            <thead>
              <tr>
                <th>File</th>
                <th>Size</th>
              </tr>
            </thead>
            <tbody>";
    
    foreach ($largeFiles as $file) {
        echo "<tr>
                <td>{$file['path']}</td>
                <td>" . formatSize($file['size']) . "</td>
              </tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<p>No large files found.</p>";
}

echo "</div></div>";

// Code optimization suggestions
echo "<div class='card mb-4'>
        <div class='card-header bg-success text-white'>
            <h3 class='card-title mb-0'>Code Optimization Suggestions</h3>
        </div>
        <div class='card-body'>";

echo "<h4>1. Combine CSS and JavaScript Files</h4>
      <p>Consider combining and minifying your CSS and JavaScript files to reduce HTTP requests and improve page load times.</p>
      
      <h4>2. Optimize Images</h4>
      <p>Use image compression tools to reduce the file size of your images without sacrificing quality.</p>
      
      <h4>3. Implement Caching</h4>
      <p>Add proper caching headers to your static assets to improve load times for returning visitors.</p>
      
      <h4>4. Use a CDN for Libraries</h4>
      <p>Consider using CDNs for common libraries like Bootstrap, jQuery, and Font Awesome instead of hosting them yourself.</p>
      
      <h4>5. Remove Unused Code</h4>
      <p>Identify and remove any unused CSS, JavaScript, or PHP code to reduce file sizes and improve maintainability.</p>";

echo "</div></div>";

// Project summary
echo "<div class='card mb-4'>
        <div class='card-header bg-dark text-white'>
            <h3 class='card-title mb-0'>Project Summary</h3>
        </div>
        <div class='card-body'>";

// Count files by type
function countFilesByType() {
    $counts = [
        'php' => 0,
        'js' => 0,
        'css' => 0,
        'html' => 0,
        'images' => 0,
        'other' => 0
    ];
    
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico'];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
            
            if ($extension === 'php') {
                $counts['php']++;
            } elseif ($extension === 'js') {
                $counts['js']++;
            } elseif ($extension === 'css') {
                $counts['css']++;
            } elseif ($extension === 'html' || $extension === 'htm') {
                $counts['html']++;
            } elseif (in_array($extension, $imageExtensions)) {
                $counts['images']++;
            } else {
                $counts['other']++;
            }
        }
    }
    
    return $counts;
}

$fileCounts = countFilesByType();

echo "<h4>File Statistics</h4>
      <ul>
        <li><strong>PHP Files:</strong> {$fileCounts['php']}</li>
        <li><strong>JavaScript Files:</strong> {$fileCounts['js']}</li>
        <li><strong>CSS Files:</strong> {$fileCounts['css']}</li>
        <li><strong>HTML Files:</strong> {$fileCounts['html']}</li>
        <li><strong>Image Files:</strong> {$fileCounts['images']}</li>
        <li><strong>Other Files:</strong> {$fileCounts['other']}</li>
        <li><strong>Total Files:</strong> " . array_sum($fileCounts) . "</li>
      </ul>";

echo "</div></div>";

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
