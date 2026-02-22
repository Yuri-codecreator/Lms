<?php
// user_edit_form.php — Standalone version (no Moodle required)
// © 2025 Free to use and modify

session_start();

// Fake user database (you can replace this with a real DB later)
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        1 => [
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'country' => 'Philippines',
            'bio' => 'A sample user profile.',
        ],
    ];
}

// Simulated logged-in user (ID 1)
$userid = 1;
$user = $_SESSION['users'][$userid];

// Initialize message
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $country   = trim($_POST['country'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');

    $errors = [];

    // Basic validation
    if ($firstname === '') $errors[] = "First name is required.";
    if ($lastname === '') $errors[] = "Last name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if ($country === '') $errors[] = "Country is required.";

    if (empty($errors)) {
        // Update "database"
        $_SESSION['users'][$userid] = [
            'id' => $userid,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'country' => $country,
            'bio' => $bio,
        ];

        $message = "✅ Profile updated successfully!";
        $user = $_SESSION['users'][$userid];
    } else {
        $message = "⚠️ " . implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #444;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        textarea {
            resize: vertical;
        }
        button {
            margin-top: 20px;
            width: 100%;
            background-color: #007bff;
            border: none;
            color: white;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005dc1;
        }
        .message {
            background: #e8f4ff;
            border-left: 4px solid #007bff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Your Profile</h2>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="firstname">First Name:</label>
        <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>

        <label for="lastname">Last Name:</label>
        <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label for="country">Country:</label>
        <input type="text" name="country" id="country" value="<?= htmlspecialchars($user['country']) ?>" required>

        <label for="bio">Bio:</label>
        <textarea name="bio" id="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>

        <button type="submit">Update Profile</button>
    </form>
</div>
</body>
</html>
