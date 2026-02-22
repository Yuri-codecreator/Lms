<?php
// policy.php - Standalone version for your LMS (no Moodle dependencies)

session_start();
require_once('../config.php'); // make sure this connects to your DB
require_once('../includes/functions.php'); // optional: place helper functions here

// Example: database connection (adjust as needed)
$conn = new mysqli('localhost', 'root', '', 'your_lms_database');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$agree = isset($_POST['agree']) ? (int)$_POST['agree'] : 0;

// Check if user already agreed to the policy
$result = $conn->query("SELECT policy_agreed FROM users WHERE id = $userId");
$user = $result->fetch_assoc();

if ($user && $user['policy_agreed']) {
    header('Location: ../dashboard.php');
    exit();
}

// Policy content file or text
$policyFile = '../assets/policy.html'; // path to your terms/policy file
$policyContent = file_exists($policyFile) ? file_get_contents($policyFile) : 'No policy available.';

// If user agreed
if ($agree === 1) {
    $conn->query("UPDATE users SET policy_agreed = 1 WHERE id = $userId");
    header('Location: ../dashboard.php');
    exit();
}

// If user declined
if ($agree === 0 && isset($_POST['decline'])) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy Agreement</title>
    <link rel="stylesheet" href="../assets/styles.css"> <!-- optional -->
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .policy-content { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; background: #fafafa; }
        .buttons { display: flex; justify-content: space-between; }
        button { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .agree { background: #28a745; color: white; }
        .decline { background: #dc3545; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h2>Privacy Policy & Terms of Use</h2>
    <div class="policy-content">
        <?= nl2br(htmlspecialchars($policyContent)); ?>
    </div>
    <form method="post">
        <div class="buttons">
            <button type="submit" name="agree" value="1" class="agree">I Agree</button>
            <button type="submit" name="decline" value="0" class="decline">I Disagree</button>
        </div>
    </form>
</div>
</body>
</html>
