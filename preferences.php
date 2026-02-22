<?php
// ============================================================================
// Standalone User Preferences Page
// Rewritten from Moodle's preferences.php for independent PHP LMS use.
// Author: Jhon Robert N. Carrera & Team
// Date: 2025
// ============================================================================

// DATABASE CONFIGURATION
$host = "localhost";       // MySQL host
$user = "root";            // MySQL username
$pass = "";                // MySQL password
$dbname = "lms_database";  // Database name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

session_start();

// Simulate login
if (!isset($_SESSION['userid'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$userid = isset($_GET['userid']) ? (int)$_GET['userid'] : $_SESSION['userid'];
$currentuser = ($userid === $_SESSION['userid']);

// Fetch user info
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, theme, language, timezone FROM users WHERE id = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("<p style='text-align:center; color:red;'>Invalid user.</p>");
}

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'default';
    $language = $_POST['language'] ?? 'en';
    $timezone = $_POST['timezone'] ?? 'UTC';

    $update = $conn->prepare("UPDATE users SET theme = ?, language = ?, timezone = ? WHERE id = ?");
    $update->bind_param("sssi", $theme, $language, $timezone, $userid);
    $update->execute();

    echo "<script>alert('✅ Preferences updated successfully!'); window.location='preferences.php?userid=$userid';</script>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Preferences</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            padding: 20px;
        }
        .container {
            width: 60%;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }
        input[type=text], input[type=email], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background-color: #0073e6;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #005bb5;
        }
        .readonly {
            background-color: #f3f3f3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>⚙️ User Preferences</h2>
        <form method="post">
            <label>Full Name:</label>
            <input type="text" value="<?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>" class="readonly" readonly>

            <label>Email:</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="readonly" readonly>

            <label for="theme">Theme:</label>
            <select name="theme" id="theme">
                <option value="default" <?= $user['theme'] === 'default' ? 'selected' : '' ?>>Default</option>
                <option value="dark" <?= $user['theme'] === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                <option value="light" <?= $user['theme'] === 'light' ? 'selected' : '' ?>>Light Mode</option>
            </select>

            <label for="language">Language:</label>
            <select name="language" id="language">
                <option value="en" <?= $user['language'] === 'en' ? 'selected' : '' ?>>English</option>
                <option value="fil" <?= $user['language'] === 'fil' ? 'selected' : '' ?>>Filipino</option>
            </select>

            <label for="timezone">Timezone:</label>
            <select name="timezone" id="timezone">
                <option value="UTC" <?= $user['timezone'] === 'UTC' ? 'selected' : '' ?>>UTC</option>
                <option value="Asia/Manila" <?= $user['timezone'] === 'Asia/Manila' ? 'selected' : '' ?>>Asia/Manila</option>
                <option value="America/New_York" <?= $user['timezone'] === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
            </select>

            <div style="text-align:center;">
                <button type="submit">💾 Save Preferences</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>
