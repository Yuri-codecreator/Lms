<?php
// Standalone group picture fetcher

// Disable error output to prevent breaking images
ini_set('display_errors', 0);
error_reporting(0);

// Base directories
$dataDir = __DIR__ . '/data/groups';  // replace with your actual group images folder
$defaultImage = __DIR__ . '/pix/g/f1.png'; // fallback image

// Get file from URL: either /pix.php?file=groupid/f1.jpg or /pix.php/groupid/f1.jpg
$relativePath = '';
if (!empty($_GET['file'])) {
    $relativePath = trim($_GET['file'], '/');
} else {
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = basename(__FILE__);
    $relativePath = trim(str_replace("/$scriptName/", '', $requestUri), '/');
}

// Parse arguments
$args = explode('/', $relativePath);

if (count($args) == 2) {
    $groupId = (int)$args[0];
    $image   = basename($args[1]); // prevent path traversal
    $filePath = "$dataDir/$groupId/$image";
} else {
    $filePath = $defaultImage;
    $image = 'f1.png';
}

// Serve the file if exists
if (file_exists($filePath) && !is_dir($filePath)) {
    $mimeType = mime_content_type($filePath);
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    // Serve default image if not found
    if (file_exists($defaultImage)) {
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($defaultImage));
        readfile($defaultImage);
        exit;
    } else {
        // 404 fallback
        header('HTTP/1.0 404 Not Found');
        echo 'File not found';
        exit;
    }
}
