<?php
require_once __DIR__ . '/../config.php';

// ============================================
// language_form.php
// Change a user's preferred language (standalone)
// ============================================

session_start();
require_once 'config.php'; // Database connection

// Redirect if user not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user info from DB
$stmt = $conn->prepare("SELECT id, fullname, preferred_language FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<p>User not found.</p>";
    exit;
}

$currentLang = $user['preferred_language'] ?? 'en';

// Available languages (you can add more)
$translations = [
    'en' => 'English',
    'fr' => 'French',
    'es' => 'Spanish',
    'de' => 'German',
    'tl' => 'Tagalog'
];

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedLang = $_POST['lang'] ?? '';

    if (array_key_exists($selectedLang, $translations)) {
        $update = $conn->prepare("UPDATE users SET preferred_language = ? WHERE id = ?");
        $update->bind_param("si", $selectedLang, $user['id']);
        if ($update->execute()) {
            $message = "<div style='color:green;'>Language preference updated successfully!</div>";
            $currentLang = $selectedLang;
        } else {
            $message = "<div style='color:red;'>Error updating language. Please try again.</div>";
        }
        $update->close();
    } else {
        $message = "<div style='color:red;'>Invalid language selected.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Preferred Language</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 50px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #444;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            display: block;
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            margin-top: 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Change Your Preferred Language</h2>
        <div class="message"><?= $message ?></div>

        <form method="post" action="">
            <label for="lang">Select Language:</label>
            <select name="lang" id="lang">
                <?php foreach ($translations as $code => $name): ?>
                    <option value="<?= htmlspecialchars($code) ?>" <?= ($currentLang === $code) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Save Changes</button>
        </form>

        <div style="text-align:center;margin-top:15px;">
            <a href="dashboard.php" style="color:#007bff;text-decoration:none;">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
