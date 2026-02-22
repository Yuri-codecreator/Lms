<?php
session_start();
require_once('externallib.php'); // Our standalone library

// Check login
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in.');
}

$userId = $_SESSION['user_id'];

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'maildigest' => (int)($_POST['maildigest'] ?? 0),
        'autosubscribe' => (int)($_POST['autosubscribe'] ?? 1),
        'useexperimentalui' => (int)($_POST['useexperimentalui'] ?? 0),
        'trackforums' => (int)($_POST['trackforums'] ?? 1),
        'markasreadonnotification' => (int)($_POST['markasreadonnotification'] ?? 1),
    ];

    $result = update_forum_preferences($userId, $data);
    if (isset($result['success'])) {
        $message = $result['message'];
    } else {
        $message = $result['error'] ?? 'Unknown error';
    }
}

// Get current preferences
$prefs = get_forum_preferences($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Forum Preferences</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; }
        label { display: block; margin-top: 15px; }
        select { width: 100%; padding: 6px; }
        button { margin-top: 20px; padding: 10px 20px; }
        .message { margin: 15px 0; color: green; }
    </style>
</head>
<body>
<h1>Edit Forum Preferences</h1>

<?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="post">
    <label>Email Digest:
        <select name="maildigest">
            <option value="0" <?php if($prefs['maildigest']==0) echo 'selected'; ?>>No digest</option>
            <option value="1" <?php if($prefs['maildigest']==1) echo 'selected'; ?>>Complete digest</option>
            <option value="2" <?php if($prefs['maildigest']==2) echo 'selected'; ?>>Subjects only</option>
        </select>
    </label>

    <label>Auto-subscribe to forums:
        <select name="autosubscribe">
            <option value="1" <?php if($prefs['autosubscribe']==1) echo 'selected'; ?>>Yes</option>
            <option value="0" <?php if($prefs['autosubscribe']==0) echo 'selected'; ?>>No</option>
        </select>
    </label>

    <label>Use Experimental UI:
        <select name="useexperimentalui">
            <option value="1" <?php if($prefs['useexperimentalui']==1) echo 'selected'; ?>>Yes</option>
            <option value="0" <?php if($prefs['useexperimentalui']==0) echo 'selected'; ?>>No</option>
        </select>
    </label>

    <label>Track read posts:
        <select name="trackforums" id="trackforums">
            <option value="1" <?php if($prefs['trackforums']==1) echo 'selected'; ?>>Yes</option>
            <option value="0" <?php if($prefs['trackforums']==0) echo 'selected'; ?>>No</option>
        </select>
    </label>

    <label>Mark as read on notification:
        <select name="markasreadonnotification" id="markasreadonnotification">
            <option value="1" <?php if($prefs['markasreadonnotification']==1) echo 'selected'; ?>>Yes</option>
            <option value="0" <?php if($prefs['markasreadonnotification']==0) echo 'selected'; ?>>No</option>
        </select>
    </label>

    <button type="submit">Save Changes</button>
</form>

<script>
    // Disable "Mark as read on notification" if tracking is off
    const track = document.getElementById('trackforums');
    const mark = document.getElementById('markasreadonnotification');
    track.addEventListener('change', function() {
        mark.disabled = (this.value === '0');
        if(this.value === '0') mark.value = '0';
    });
</script>

</body>
</html>
