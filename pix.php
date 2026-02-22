<?php
// pix.php - Standalone user image handler for your LMS (no Moodle dependencies)

declare(strict_types=1);

// Example configuration (adjust paths as needed)
$uploadDir = __DIR__ . '/../uploads/user_images/';  // Folder where user images are stored
$defaultImage = __DIR__ . '/../assets/default_user.png'; // Fallback image if not found

// Enable simple error suppression for clean image output
ini_set('display_errors', '0');
error_reporting(0);

// Parse the request path (e.g., pix.php/123/f1.jpg)
$relativePath = $_SERVER['PATH_INFO'] ?? '';
$args = explode('/', trim($relativePath, '/'));

if (count($args) === 2) {
    $userId = (int)$args[0];
    $imageName = $args[1];

    // Sanitize
    if (!in_array($imageName, ['f1.jpg', 'f2.jpg'], true)) {
        $imageName = 'f1.jpg';
    }

    // Build path to the user’s uploaded photo
    $imagePath = $uploadDir . $userId . '_' . $imageName;

    if (file_exists($imagePath)) {
        $mime = mime_content_type($imagePath);
        header('Content-Type: ' . $mime);
        readfile($imagePath);
        exit;
    }
}

// If not found, output the default image
if (file_exists($defaultImage)) {
    $mime = mime_content_type($defaultImage);
    header('Content-Type: ' . $mime);
    readfile($defaultImage);
    exit;
}

// If still not found, return a 404
http_response_code(404);
echo 'User image not found.';
