<?php
/**
 * Standalone Editor Preference Page
 * Converted from Moodle's editor.php
 *
 * Author: Ivan (Clean PHP Conversion)
 * License: Free to use and modify
 */

session_start();

// Simulated "logged-in" user (replace with real login logic)
if (!isset($_SESSION['userid'])) {
    $_SESSION['userid'] = 1;
}
$userid = $_SESSION['userid'];

// Simple data file to store user preferences
$dataFile = __DIR__ . '/user_preferences.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT));
}
$allPrefs = json_decode(file_get_contents($dataFile), true);

// Load current user’s preference
$currentEditor = $allPrefs[$userid]['editor'] ?? 'simple';

// Available editors
$editors = [
    'tinymce' => 'TinyMCE',
    'ckeditor' => 'CKEditor',
    'simple' => 'Simple Textarea'
];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['editor'] ?? '';
    if (array_key_exists($selected, $editors)) {
        $allPrefs[$userid]['editor'] = $selected;
        file_put_contents($dataFile, json_encode($allPrefs, JSON_PRETTY_PRINT));
        $message = "✅ Editor preference saved: <b>{$editors[$selected]}</b>";
        $currentEditor = $selected;
    } else {
        $message = "⚠️ Invalid editor selected.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Editor Preference</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fb;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            padding: 10px;
            background: #dfffdc;
            border: 1px solid #8c8;
            color: #060;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Editor Preferences</h1>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="editor">Choose your preferred editor:</label>
        <select name="editor" id="editor">
            <?php foreach ($editors as $key => $label): ?>
                <option value="<?= $key ?>" <?= $key === $currentEditor ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
