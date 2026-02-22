<?php
session_start();
$userid = $_SESSION['userid'] ?? 1;
$defaults = [
    'timeformat' => '24-hour',
    'startwday' => 'monday',
    'maxevents' => 10,
    'lookahead' => 30,
    'persistflt' => 0,
];

// Save preferences when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prefs = [
        'timeformat' => $_POST['timeformat'] ?? $defaults['timeformat'],
        'startwday'  => $_POST['startwday'] ?? $defaults['startwday'],
        'maxevents'  => (int)($_POST['maxevents'] ?? $defaults['maxevents']),
        'lookahead'  => (int)($_POST['lookahead'] ?? $defaults['lookahead']),
        'persistflt' => isset($_POST['persistflt']) ? 1 : 0,
    ];

    // Save to a JSON file (simulate a database)
    file_put_contents("userprefs_$userid.json", json_encode($prefs));

    $message = "Changes saved successfully!";
} else {
    // Load preferences if file exists
    if (file_exists("userprefs_$userid.json")) {
        $prefs = json_decode(file_get_contents("userprefs_$userid.json"), true);
    } else {
        $prefs = $defaults;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar Preferences</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f9f9f9; }
        form { background: #fff; padding: 20px; border-radius: 8px; width: 400px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px 15px; border: none; background: #007bff; color: white; border-radius: 4px; cursor: pointer; }
        .message { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Calendar Preferences</h2>
    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Time Format:</label>
        <select name="timeformat">
            <option value="12-hour" <?= $prefs['timeformat'] === '12-hour' ? 'selected' : '' ?>>12-hour</option>
            <option value="24-hour" <?= $prefs['timeformat'] === '24-hour' ? 'selected' : '' ?>>24-hour</option>
        </select>

        <label>Start of Week:</label>
        <select name="startwday">
            <option value="sunday" <?= $prefs['startwday'] === 'sunday' ? 'selected' : '' ?>>Sunday</option>
            <option value="monday" <?= $prefs['startwday'] === 'monday' ? 'selected' : '' ?>>Monday</option>
        </select>

        <label>Max Events:</label>
        <input type="number" name="maxevents" value="<?= htmlspecialchars($prefs['maxevents']) ?>">

        <label>Lookahead (days):</label>
        <input type="number" name="lookahead" value="<?= htmlspecialchars($prefs['lookahead']) ?>">

        <label><input type="checkbox" name="persistflt" <?= $prefs['persistflt'] ? 'checked' : '' ?>> Remember Filters</label>

        <button type="submit">Save Preferences</button>
    </form>
</body>
</html>
