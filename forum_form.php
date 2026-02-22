<?php
session_start();

// Simple authentication check
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in.');
}

$userId = $_SESSION['user_id'];

// Dummy defaults (replace with DB values if needed)
$defaults = [
    'maildigest' => 0,
    'autosubscribe' => 1,
    'useexperimentalui' => 0,
    'trackforums' => 1,
    'markasreadonnotification' => 1
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maildigest = (int)($_POST['maildigest'] ?? 0);
    $autosubscribe = (int)($_POST['autosubscribe'] ?? 0);
    $useexperimentalui = (int)($_POST['useexperimentalui'] ?? 0);
    $trackforums = (int)($_POST['trackforums'] ?? 0);
    $markasreadonnotification = (int)($_POST['markasreadonnotification'] ?? 0);

    // Save settings (replace with actual DB save)
    $_SESSION['forum_preferences'] = compact(
        'maildigest', 'autosubscribe', 'useexperimentalui', 'trackforums', 'markasreadonnotification'
    );

    $message = "Settings saved successfully!";
}

// Use saved preferences if available
$prefs = $_SESSION['forum_preferences'] ?? $defaults;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Forum Preferences</title>
</head>
<body>
<h1>Edit Forum Preferences</h1>

<?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

<form method="post">
    <label>Email Digest:</label>
    <select name="maildigest">
        <option value="0" <?php if($prefs['maildigest']==0) echo 'selected'; ?>>No digest</option>
        <option value="1" <?php if($prefs['maildigest']==1) echo 'selected'; ?>>Complete digest</option>
        <option value="2" <?php if($prefs['maildigest']==2) echo 'selected'; ?>>Subjects only</option>
    </select>
    <br><br>

    <label>Auto-subscribe to forums:</label>
    <select name="autosubscribe">
        <option value="1" <?php if($prefs['autosubscribe']==1) echo 'selected'; ?>>Yes</option>
        <option value="0" <?php if($prefs['autosubscribe']==0) echo 'selected'; ?>>No</option>
    </select>
    <br><br>

    <label>Use Experimental UI:</label>
    <select name="useexperimentalui">
        <option value="1" <?php if($prefs['useexperimentalui']==1) echo 'selected'; ?>>Yes</option>
        <option value="0" <?php if($prefs['useexperimentalui']==0) echo 'selected'; ?>>No</option>
    </select>
    <br><br>

    <label>Track read posts:</label>
    <select name="trackforums">
        <option value="1" <?php if($prefs['trackforums']==1) echo 'selected'; ?>>Yes</option>
        <option value="0" <?php if($prefs['trackforums']==0) echo 'selected'; ?>>No</option>
    </select>
    <br><br>

    <label>Mark as read on notification:</label>
    <select name="markasreadonnotification" <?php if($prefs['trackforums']==0) echo 'disabled'; ?>>
        <option value="1" <?php if($prefs['markasreadonnotification']==1) echo 'selected'; ?>>Yes</option>
        <option value="0" <?php if($prefs['markasreadonnotification']==0) echo 'selected'; ?>>No</option>
    </select>
    <br><br>

    <button type="submit">Save Changes</button>
</form>

</body>
</html>
