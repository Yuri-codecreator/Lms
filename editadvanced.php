<?php
// editadvanced.php - Standalone user create/edit form (no Moodle)
// © 2025 - Free to use and modify

session_start();

/*
  Behavior:
  - Provide a form to create (id=-1) or edit an existing user (?id=USERID).
  - Stores users in users.json (same directory).
  - Uploaded pictures go to /uploads (created automatically).
  - Simple validation for required fields, username/email uniqueness.
  - Password hashing via password_hash(). For edits, leave password blank to keep existing.
*/

// --------- Utility functions ----------
function storage_file() {
    return __DIR__ . '/users.json';
}
function load_users() {
    $file = storage_file();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}
function save_users($users) {
    file_put_contents(storage_file(), json_encode($users, JSON_PRETTY_PRINT));
}
function next_user_id($users) {
    if (empty($users)) return 1;
    return max(array_keys($users)) + 1;
}
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// --------- Ensure upload dir ----------
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// --------- Load users ----------
$users = load_users();

// Simulated auth (for editing own account fallback)
if (!isset($_SESSION['userid'])) $_SESSION['userid'] = 1;

// Determine id param (create/edit)
$idparam = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['userid'];
$creating = ($idparam === -1);

// If creating and no users exist, allow creation; otherwise editing an existing user.
if ($creating) {
    $user = [
        'id' => -1,
        'username' => '',
        'firstname' => '',
        'lastname' => '',
        'email' => '',
        'country' => '',
        'description' => '',
        'interests' => '',
        'picture' => null,
        'suspended' => false,
        'password_hash' => null,
        'deleted' => false
    ];
} else {
    if (!isset($users[$idparam])) {
        die("User not found.");
    }
    $user = $users[$idparam];
}

// Handle form submission
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize
    $username = trim($_POST['username'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $suspended = isset($_POST['suspended']) ? true : false;
    $createpassword = isset($_POST['createpassword']) ? true : false;
    $newpassword = $_POST['newpassword'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? '';

    // Basic validation
    if ($username === '') $errors[] = "Username is required.";
    if (!preg_match('/^[a-z0-9_]+$/', $username)) $errors[] = "Username may only contain lowercase letters, numbers, and underscores.";
    if ($firstname === '') $errors[] = "First name is required.";
    if ($lastname === '') $errors[] = "Last name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";

    // Unique checks (case-insensitive) for username and email among other users
    foreach ($users as $uid => $u) {
        if (!$creating && $uid == $idparam) continue;
        if (strcasecmp($u['username'] ?? '', $username) === 0) $errors[] = "That username is already taken.";
        if (strcasecmp($u['email'] ?? '', $email) === 0) $errors[] = "That email is already used by another account.";
    }

    // Password rules
    if ($creating) {
        // For creation: require password either via createpassword flag or newpassword present
        if ($createpassword === false && trim($newpassword) === '') {
            // allow blank if you want to create without password (but we enforce password here)
            $errors[] = "Password is required for new users (fill New password or check Create password).";
        }
    }

    if (trim($newpassword) !== '') {
        if (strlen($newpassword) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($newpassword !== $confirmpassword) $errors[] = "New password and confirmation do not match.";
    }

    // Picture upload handling (optional)
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
                    $basename = 'user_' . ($creating ? 'new' : $idparam) . '_' . time() . '.' . $ext;
                    $target = $uploadDir . '/' . $basename;
                    if (!move_uploaded_file($f['tmp_name'], $target)) {
                        $errors[] = "Failed to save uploaded picture.";
                    } else {
                        // delete old picture if editing
                        if (!$creating && !empty($user['picture']) && file_exists(__DIR__ . '/' . $user['picture'])) {
                            @unlink(__DIR__ . '/' . $user['picture']);
                        }
                        $user['picture'] = 'uploads/' . $basename;
                    }
                }
            }
        }
    }

    // If no errors, process create/update
    if (empty($errors)) {
        $user['username'] = strtolower($username);
        $user['firstname'] = $firstname;
        $user['lastname'] = $lastname;
        $user['email'] = $email;
        $user['country'] = $country;
        $user['description'] = $description;
        $user['interests'] = $interests;
        $user['suspended'] = $suspended;

        if ($creating) {
            // assign new ID
            $newid = next_user_id($users);
            $user['id'] = $newid;
            // password
            if (trim($newpassword) !== '') {
                $user['password_hash'] = password_hash($newpassword, PASSWORD_DEFAULT);
            } else {
                $user['password_hash'] = null; // optional
            }
            $users[$newid] = $user;
            save_users($users);
            $success = "User created successfully (ID: $newid).";
            // After creating, switch to edit mode for that user
            header("Location: ?id=" . $newid . "&created=1");
            exit;
        } else {
            // editing existing
            if (trim($newpassword) !== '') {
                $user['password_hash'] = password_hash($newpassword, PASSWORD_DEFAULT);
            }
            $users[$idparam] = $user;
            save_users($users);
            $success = "Profile updated successfully.";
            // reload the updated user
            $user = $users[$idparam];
        }
    }
}

// Display form HTML below
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= $creating ? 'Create User' : 'Edit User' ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    body { font-family: Arial, sans-serif; background:#f6f7fb; padding:24px; }
    .card { max-width:820px; margin:0 auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
    h1 { margin-top:0; }
    label { display:block; margin-top:12px; font-weight:600; }
    input[type=text], input[type=email], input[type=password], textarea, select {
        width:100%; padding:8px; border:1px solid #ccc; border-radius:6px; margin-top:6px;
    }
    textarea { min-height:90px; }
    .row { display:flex; gap:12px; }
    .col { flex:1; }
    .small { max-width:200px; }
    .actions { margin-top:16px; }
    button { background:#007bff; color:#fff; border:none; padding:10px 14px; border-radius:6px; cursor:pointer; }
    button:hover { background:#0056b3; }
    .msg { padding:10px; border-radius:6px; margin-bottom:12px; }
    .success { background:#e6ffed; border:1px solid #b7f5c9; color:#145214; }
    .error { background:#fff1f0; border:1px solid #ffb3b3; color:#8a1f1f; }
    img.profile { width:120px; height:120px; object-fit:cover; border-radius:8px; margin-top:8px; }
    .note { font-size:0.9em; color:#666; margin-top:6px; }
    .inline { display:inline-block; vertical-align:middle; margin-right:8px; }
</style>
</head>
<body>
<div class="card">
    <h1><?= $creating ? 'Create New User' : 'Edit User: ' . e($user['username']) ?></h1>

    <?php if (!empty($_GET['created'])): ?>
        <div class="msg success">✅ User created. You are now editing the new user.</div>
    <?php endif; ?>

    <?php if ($success && !$creating): ?>
        <div class="msg success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="msg error"><strong>There was a problem:</strong><ul><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <div class="row">
            <div class="col">
                <label>Username</label>
                <input type="text" name="username" value="<?= e($user['username']) ?>" required>
                <div class="note">Lowercase letters, numbers, and underscores only.</div>
            </div>
            <div class="col">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($user['email']) ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>First name</label>
                <input type="text" name="firstname" value="<?= e($user['firstname']) ?>" required>
            </div>
            <div class="col">
                <label>Last name</label>
                <input type="text" name="lastname" value="<?= e($user['lastname']) ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>Country</label>
                <input type="text" name="country" value="<?= e($user['country']) ?>">
            </div>
            <div class="col small">
                <label>Suspend account</label>
                <div style="margin-top:8px;">
                    <label><input type="checkbox" name="suspended" <?= !empty($user['suspended']) ? 'checked' : '' ?>> Suspended</label>
                </div>
            </div>
        </div>

        <label>Description / Bio</label>
        <textarea name="description"><?= e($user['description']) ?></textarea>

        <label>Interests (comma-separated)</label>
        <input type="text" name="interests" value="<?= e($user['interests']) ?>">

        <label style="margin-top:14px;">Profile picture</label>
        <div style="display:flex; gap:12px; align-items:center;">
            <div>
                <input type="file" name="picture" accept="image/*">
                <div class="note">Max 2MB. JPG/PNG/GIF only.</div>
            </div>
            <div>
                <?php if (!empty($user['picture']) && file_exists(__DIR__ . '/' . $user['picture'])): ?>
                    <img src="<?= e($user['picture']) ?>" alt="Profile" class="profile">
                <?php else: ?>
                    <div class="note">No profile picture.</div>
                <?php endif; ?>
            </div>
        </div>

        <hr style="margin:18px 0; border:none; border-top:1px solid #eee;">

        <label>New password <?php if ($creating) echo '(required)'; else echo '(leave blank to keep existing)'; ?></label>
        <input type="password" name="newpassword" autocomplete="new-password">
        <label>Confirm new password</label>
        <input type="password" name="confirmpassword" autocomplete="new-password">

        <?php if ($creating): ?>
            <div class="note">You can optionally check "Create password" on creation; if not set, password will be required.</div>
        <?php endif; ?>

        <div class="actions">
            <button type="submit"><?= $creating ? 'Create User' : 'Save Changes' ?></button>
            &nbsp;
            <a href="?id=1" style="color:#007bff; text-decoration:none; margin-left:10px;">Edit user 1</a>
        </div>
    </form>
</div>
</body>
</html>
