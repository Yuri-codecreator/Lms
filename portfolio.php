<?php
// portfolio.php — Standalone version for your LMS (no Moodle dependencies)

session_start();
require_once('../config.php'); // include database connection
require_once('../includes/functions.php'); // optional: your helper functions

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'your_lms_database');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$userId = $_SESSION['user_id'];

// Handle visibility toggle
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $query = "UPDATE portfolios SET visible = NOT visible WHERE id = $id AND user_id = $userId";
    $conn->query($query);
    header('Location: portfolio.php');
    exit();
}

// Handle configuration update
if (isset($_POST['save_config'])) {
    $id = (int)$_POST['portfolio_id'];
    $settings = $conn->real_escape_string($_POST['settings']);
    $conn->query("UPDATE portfolios SET settings = '$settings' WHERE id = $id AND user_id = $userId");
    header('Location: portfolio.php?success=1');
    exit();
}

// Fetch portfolios for this user
$result = $conn->query("SELECT * FROM portfolios WHERE user_id = $userId");
$portfolios = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Your Portfolios</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 900px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .actions a { margin-right: 10px; text-decoration: none; color: #007bff; }
        .actions a:hover { text-decoration: underline; }
        .config-box { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Your Portfolios</h2>
    <p>You can configure your personal portfolios and toggle their visibility below.</p>

    <table>
        <tr>
            <th>Name</th>
            <th>Plugin</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($portfolios as $portfolio): ?>
            <tr>
                <td><?= htmlspecialchars($portfolio['name']); ?></td>
                <td><?= htmlspecialchars($portfolio['plugin']); ?></td>
                <td><?= $portfolio['visible'] ? 'Visible' : 'Hidden'; ?></td>
                <td class="actions">
                    <a href="?toggle=<?= $portfolio['id']; ?>">
                        <?= $portfolio['visible'] ? 'Hide' : 'Show'; ?>
                    </a>
                    <a href="?config=<?= $portfolio['id']; ?>">Configure</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if (isset($_GET['config'])): 
        $configId = (int)$_GET['config'];
        $conf = $conn->query("SELECT * FROM portfolios WHERE id = $configId AND user_id = $userId")->fetch_assoc();
        if ($conf): ?>
        <div class="config-box">
            <h3>Configure: <?= htmlspecialchars($conf['name']); ?></h3>
            <form method="post">
                <input type="hidden" name="portfolio_id" value="<?= $conf['id']; ?>">
                <textarea name="settings" rows="5" style="width:100%;"><?= htmlspecialchars($conf['settings']); ?></textarea>
                <br><br>
                <button type="submit" name="save_config">Save Settings</button>
                <a href="portfolio.php" style="margin-left:10px;">Cancel</a>
            </form>
        </div>
        <?php endif; endif; ?>
</div>
</body>
</html>
