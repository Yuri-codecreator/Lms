<?php
// contentbank.php
// Standalone version (no Moodle needed)

// Start session (to simulate login)
session_start();

// Example fake login for testing.
// In a real project, you’d check credentials or a database.
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = "DemoUser";
}

// File to store user preference (you can switch this to a database later)
$prefsFile = 'contentbank_prefs.json';

// Load saved preferences (if file exists)
if (file_exists($prefsFile)) {
    $prefs = json_decode(file_get_contents($prefsFile), true);
} else {
    $prefs = [
        'username' => $_SESSION['username'],
        'contentvisibility' => 'public'
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visibility = $_POST['contentvisibility'] ?? 'public';
    $prefs['contentvisibility'] = $visibility;
    file_put_contents($prefsFile, json_encode($prefs, JSON_PRETTY_PRINT));
    $message = "✅ Content bank preference saved successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content Bank Preferences</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 50px auto;
    max-width: 500px;
    background-color: #f9f9f9;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #333;
}
label {
    display: block;
    font-weight: bold;
    margin-top: 20px;
}
select, button {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}
button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    margin-top: 20px;
}
button:hover {
    background-color: #0056b3;
}
.message {
    text-align: center;
    margin-top: 15px;
    color: green;
}
</style>
</head>
<body>

<h2>Content Bank Preferences</h2>

<?php if (!empty($message)): ?>
    <p class="message"><?= $message ?></p>
<?php endif; ?>

<form method="post" action="">
    <label for="contentvisibility">Choose your content visibility:</label>
    <select name="contentvisibility" id="contentvisibility">
        <option value="public" <?= $prefs['contentvisibility'] === 'public' ? 'selected' : '' ?>>Public (everyone can see)</option>
        <option value="private" <?= $prefs['contentvisibility'] === 'private' ? 'selected' : '' ?>>Private (only you can see)</option>
    </select>

    <button type="submit">Save Preference</button>
</form>

</body>
</html>
