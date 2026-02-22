<?php
/**
 * Standalone User Edit Advanced Form
 * Converted from Moodle's editadvanced_form.php
 *
 * Author: Ivan (Clean PHP Conversion)
 * License: Free to use and modify
 */

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = false;

    // Sanitize and collect form data
    $fullname = trim($_POST['fullname'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $suspended = isset($_POST['suspended']) ? true : false;

    // --- VALIDATION ---

    // Full name
    if (empty($fullname)) {
        $errors['fullname'] = "Full name is required.";
    }

    // Username
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } elseif (!preg_match('/^[a-z0-9_]+$/', $username)) {
        $errors['username'] = "Username must use lowercase letters, numbers, or underscores only.";
    }

    // Email
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    // Password
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // If validation passes
    if (empty($errors)) {
        $success = true;
        // In real use, replace this with DB save logic
        $userdata = [
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'suspended' => $suspended,
        ];

        // Example: save to JSON for testing/demo
        file_put_contents('user_data.json', json_encode($userdata, JSON_PRETTY_PRINT));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Advanced Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            max-width: 500px;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
        }
        .checkbox {
            margin-top: 10px;
        }
        .error {
            color: #d00;
            font-size: 0.9em;
        }
        .success {
            background: #d4ffd4;
            border: 1px solid #6c6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            color: #060;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<h2>Edit Advanced User Form</h2>

<form method="POST">
    <?php if (!empty($success)): ?>
        <div class="success">✅ User profile updated successfully!</div>
    <?php endif; ?>

    <label>Full Name</label>
    <input type="text" name="fullname" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
    <div class="error"><?= $errors['fullname'] ?? '' ?></div>

    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    <div class="error"><?= $errors['username'] ?? '' ?></div>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    <div class="error"><?= $errors['email'] ?? '' ?></div>

    <label>Password</label>
    <input type="password" name="password">
    <div class="error"><?= $errors['password'] ?? '' ?></div>

    <label>Confirm Password</label>
    <input type="password" name="confirm_password">
    <div class="error"><?= $errors['confirm_password'] ?? '' ?></div>

    <label class="checkbox">
        <input type="checkbox" name="suspended" <?= !empty($_POST['suspended']) ? 'checked' : '' ?>> Suspend Account
    </label>

    <button type="submit">Save Changes</button>
</form>

</body>
</html>
