<?php
/**
 * Standalone User Editor Preference Form
 * Converted from Moodle's user_edit_editor_form.php
 *
 * Author: Ivan (Clean PHP Conversion)
 * License: Free to use and modify
 */

// Define available text editors
$available_editors = [
    'tinymce' => 'TinyMCE',
    'ckeditor' => 'CKEditor',
    'simple' => 'Simple Textarea',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_editor = $_POST['editor'] ?? '';
    $message = '';
    
    if (array_key_exists($selected_editor, $available_editors)) {
        // Save to file (simulate user preference save)
        file_put_contents('editor_preference.json', json_encode(['editor' => $selected_editor]));
        $message = "✅ Editor preference saved successfully: <b>{$available_editors[$selected_editor]}</b>";
    } else {
        $message = "⚠️ Invalid editor selected.";
    }
}

// Load previous selection if available
$stored_pref = file_exists('editor_preference.json')
    ? json_decode(file_get_contents('editor_preference.json'), true)
    : [];
$current_editor = $stored_pref['editor'] ?? 'simple';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Editor Preference</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            padding: 30px;
        }
        form {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            max-width: 500px;
            margin: 40px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 8px;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            background: #d4ffd4;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #6c6;
            color: #060;
            text-align: center;
        }
    </style>
</head>
<body>

<h2>Choose Your Preferred Editor</h2>

<form method="POST">
    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <label for="editor">Text Editing Tool</label>
    <select name="editor" id="editor">
        <?php foreach ($available_editors as $key => $label): ?>
            <option value="<?= $key ?>" <?= $key === $current_editor ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Save Preference</button>
</form>

</body>
</html>
