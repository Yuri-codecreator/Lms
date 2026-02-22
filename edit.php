<?php
// user_edit.php - Standalone user profile editor (no Moodle)
// © 2025 - Free to use and modify

session_start();

// --------- Simple "DB" (JSON file) setup ----------
$dataFile = __DIR__ . '/users.json';
if (!file_exists($dataFile)) {
    // Create a default user
    $default = [
        1 => [
            'id' => 1,
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'email'     => 'john@example.com',
            'country'   => 'Philippines',
            'description' => 'A short bio about John.',
            'interests' => 'coding,reading',
            'picture'   => null,
            'deleted'   => false
        ]
    ];
    file_put_contents($dataFile, json_encode($default, JSON_PRETTY_PRINT));
}

// Load users
$users = json_decode(file_get_contents($dataFile), true);

// Simulated logged-in user
if (!isset($_SESSION['userid'])) {
    $_SESSION['userid'] = 1;
}
$userid = $_SESSION['userid'];

if (!isset($users[$userid])) {
    die("User not found.");
}
$user = $users[$userid];

// Prevent editing deleted accounts
if (!empty($user['deleted'])) {
    die("This account cannot be edited.");
}

// --------- Handle form submission ----------
$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $country   = trim($_POST['country'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $interests = trim($_POST['interests'] ?? '');

    // Validation
    if ($firstname === '') { $errors[] = "First name is required."; }
    if ($lastname === '')  { $errors[] = "Last name is required."; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }

    // Check email uniqueness
    foreach ($users as $uid => $u) {
        if ($uid == $userid) continue;
        if (strcasecmp($u['email'] ?? '', $email) === 0) {
            $errors[] = "That email address is already used by another account.";
            break;
        }
    }

    // Handle picture upload (optional)
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (!empty($_FILES['picture']['name'])) {
        $f = $_FILES['picture'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading picture.";
        } else {
            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($f['size'] > $maxSize) {
                $errors[] = "Picture must be 2MB or smaller.";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                if (!isset($allowed[$mime])) {
                    $errors[] = "Only JPG, PNG, or GIF images are allowed.";
                } else {
                    $ext = $allowed[$mime];
                    $basename = 'user_' . $userid . '_' . time() . '.' . $ext;
                    $target = $uploadDir . '/' . $basename;
                    if (!move_uploaded_file($f['tmp_name'], $target)) {
                        $errors[] = "Failed to save uploaded picture.";
                    } else {
                        // Delete old picture if any
                        if (!empty($user['picture']) && file_exists(__DIR__ . '/' . $user['picture'])) {
                            @unlink(__DIR__ . '/' . $user['picture']);
                        }
                        // Store relative path
                        $user['picture'] = 'uploads/' . $basename;
                    }
                }
            }
        }
    }

    // If no errors, save updates
    if (empty($errors)) {
        $user['firstname'] = $firstname;
        $user['lastname']  = $lastname;
        $user['email']     = $email;
        $user['country']   = $country;
        $user['description'] = $description;
        $user['interests'] = $interests;

        $users[$userid] = $user;
        file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT));
        $message = "✅ Profile updated successfully.";
    }
}

// Escape for HTML output
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Profile</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; padding:30px; }
.card { max-width:720px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
h1 { margin-top:0; text-align:center; }
label { display:block; margin-top:12px; font-weight:600; }
input[type=text], input[type=email], textarea {
    width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;
}
textarea { resize:vertical; height:80px; }
button {
    background:#007bff; color:#fff; padding:10px 20px;
    border:none; border-radius:5px; margin-top:20px; cursor:pointer;
}
button:hover { background:#0056b3; }
.message { background:#e6ffe6; border:1px solid #7c7; padding:10px; border-radius:6px; margin-bottom:10px; color:#060; }
.errors { background:#ffe6e6; border:1px solid #c77; padding:10px; border-radius:6px; margin-bottom:10px; color:#900; }
img.profile-pic { width:100px; height:100px; object-fit:cover; border-radius:50%; margin-top:10px; }
</style>
</head>
<body>

<div class="card">
    <h1>Edit Profile</h1>

    <?php if ($message): ?>
        <div class="message"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>First Name</label>
        <input type="text" name="firstname" value="<?= e($user['firstname']) ?>">

        <label>Last Name</label>
        <input type="text" name="lastname" value="<?= e($user['lastname']) ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= e($user['email']) ?>">

        <label>Country</label>
        <input type="text" name="country" value="<?= e($user['country']) ?>">

        <label>Description</label>
        <textarea name="description"><?= e($user['description']) ?></textarea>

        <label>Interests (comma separated)</label>
        <input type="text" name="interests" value="<?= e($user['interests']) ?>">

        <label>Profile Picture</label>
        <input type="file" name="picture" accept="image/*">
        <?php if (!empty($user['picture']) && file_exists(__DIR__ . '/' . $user['picture'])): ?>
            <br><img src="<?= e($user['picture']) ?>" alt="Profile Picture" class="profile-pic">
        <?php endif; ?>

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
