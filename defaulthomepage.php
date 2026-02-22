<?php
// defaulthomepage.php — Standalone version (no Moodle dependencies)
// © 2025 Free to use and modify

session_start();

// Simulated logged-in user (replace with your own auth logic)
if (!isset($_SESSION['userid'])) {
    $_SESSION['userid'] = 1;
    $_SESSION['username'] = "John Doe";
}

// Available homepage options
$homepages = [
    'dashboard' => 'Dashboard',
    'site' => 'Main Site',
    'profile' => 'Profile Page',
    'custom' => 'Custom Page'
];

// Default value if none is saved yet
if (!isset($_SESSION['default_homepage'])) {
    $_SESSION['default_homepage'] = 'dashboard';
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['defaulthomepage'] ?? 'dashboard';

    if (array_key_exists($selected, $homepages)) {
        $_SESSION['default_homepage'] = $selected;
        $message = "✅ Default homepage updated to: " . htmlspecialchars($homepages[$selected]);
    } else {
        $message = "⚠️ Invalid selection.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Default Homepage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f8fa;
            padding: 40px;
            color: #333;
        }
        .container {
            max-width: 480px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #444;
        }
        label, select, button {
            display: block;
            width: 100%;
            margin-top: 10px;
            font-size: 16px;
        }
        select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 15px;
            background-color: #0073e6;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #005bb5;
        }
        .message {
            background: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .footer {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Set Your Default Homepage</h2>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong></p>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="defaulthomepage">Choose your homepage:</label>
        <select id="defaulthomepage" name="defaulthomepage" required>
            <?php foreach ($homepages as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>" 
                    <?= ($_SESSION['default_homepage'] === $key) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Save Preference</button>
    </form>

    <p style="margin-top:20px;">
        Current homepage: <strong><?= htmlspecialchars($homepages[$_SESSION['default_homepage']]); ?></strong>
    </p>
</div>

<div class="footer">
    &copy; 2025 Example Web System
</div>
</body>
</html>
