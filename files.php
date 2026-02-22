<?php
session_start();

// Simple authentication check
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to access this page.');
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Define user private folder
$baseDir = __DIR__ . '/private_files';
$userDir = $baseDir . '/user_' . $userId;

// Create folder if it doesn't exist
if (!is_dir($userDir)) {
    mkdir($userDir, 0755, true);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $target = $userDir . '/' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $message = 'File uploaded successfully.';
        } else {
            $message = 'Failed to move uploaded file.';
        }
    } else {
        $message = 'Upload error: ' . $file['error'];
    }
}

// List files
$files = array_diff(scandir($userDir), ['.', '..']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Private Files - <?php echo htmlspecialchars($userName); ?></title>
</head>
<body>
    <h1>Private Files for <?php echo htmlspecialchars($userName); ?></h1>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>

    <h2>Your Files:</h2>
    <ul>
        <?php foreach ($files as $f): ?>
            <li>
                <a href="<?php echo 'private_files/user_' . $userId . '/' . urlencode($f); ?>" target="_blank">
                    <?php echo htmlspecialchars($f); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
