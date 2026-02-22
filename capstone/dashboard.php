<?php
require_once 'config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

$user = get_user_by_id($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Capstone LMS</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($user['fullname']) ?>!</h2>
    <p>Your email: <?= htmlspecialchars($user['email']) ?></p>
    <a href="user/language.php">Change Language</a> |
    <a href="user/portfoliologs.php">Portfolio Logs</a> |
    <a href="logout.php">Logout</a>
</body>
</html>
