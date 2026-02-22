<?php
// config.php
$host = 'localhost';
$db   = 'your_database';
$user = 'db_user';
$pass = 'db_pass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

// emailupdate.php
if (!isset($_GET['id'], $_GET['key'])) {
    exit('Missing parameters.');
}

$id  = (int)$_GET['id'];
$key = $_GET['key'];

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    exit('Invalid user ID.');
}

// Fetch user preferences
$stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->execute([$id]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);

// Validate key
if (!isset($preferences['email_change_key']) || $preferences['email_change_key'] !== $key) {
    // Decrement attempts if key is invalid
    $attemptsLeft = $preferences['newemailattemptsleft'] ?? 3;
    $attemptsLeft--;
    $stmt = $pdo->prepare("UPDATE user_preferences SET newemailattemptsleft = ? WHERE user_id = ?");
    $stmt->execute([$attemptsLeft, $id]);

    exit($attemptsLeft < 1 ? 'Out of attempts.' : 'Invalid email update key.');
}

// Check attempts left
if (($preferences['newemailattemptsleft'] ?? 3) < 1) {
    exit('Out of attempts.');
}

// Validate new email
$newEmail = $preferences['newemail'] ?? '';
if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    exit('Invalid email format.');
}

// Check for duplicate email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$newEmail, $id]);
if ($stmt->fetch()) {
    exit('Email already exists.');
}

// Update email
$stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->execute([$newEmail, $id]);

// Clear temporary preferences
$stmt = $pdo->prepare("UPDATE user_preferences SET newemail = NULL, email_change_key = NULL, newemailattemptsleft = NULL WHERE user_id = ?");
$stmt->execute([$id]);

echo "Email updated successfully to $newEmail.";
?>
