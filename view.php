<?php
// ==========================================
// USER PROFILE PAGE (Standalone LMS Version)
// ==========================================

// Include database connection and session
include_once 'config.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID (from query or session)
$userId = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];

// Fetch user details from database
$stmt = $conn->prepare("SELECT id, fullname, email, username, bio, profile_picture, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h2>User not found.</h2>";
    exit();
}

$user = $result->fetch_assoc();
$currentUser = $_SESSION['user_id'] == $user['id'];

// Close statement
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - <?= htmlspecialchars($user['fullname']) ?></title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }
        .profile-header img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
        }
        .profile-header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }
        .profile-header span {
            color: #666;
            font-size: 16px;
        }
        .profile-info {
            line-height: 1.8;
        }
        .profile-info label {
            font-weight: bold;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: #007bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <img src="<?= htmlspecialchars($user['profile_picture'] ?: 'uploads/default-avatar.png') ?>" alt="Profile Picture">
        <div>
            <h1><?= htmlspecialchars($user['fullname']) ?></h1>
            <span><?= htmlspecialchars($user['role']) ?></span>
        </div>
    </div>

    <div class="profile-info">
        <p><label>Email:</label> <?= htmlspecialchars($user['email']) ?></p>
        <p><label>Username:</label> <?= htmlspecialchars($user['username']) ?></p>
        <?php if (!empty($user['bio'])): ?>
            <p><label>About:</label><br><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($currentUser): ?>
        <a href="edit_profile.php" class="btn">Edit Profile</a>
    <?php endif; ?>

    <a href="dashboard.php" class="btn" style="background:#6c757d;">Back to Dashboard</a>
</div>

</body>
</html>
