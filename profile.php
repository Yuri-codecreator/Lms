<?php
// profile.php - Standalone public profile page (rewritten from Moodle's profile.php).
// Author: adapted for standalone LMS
// Date: 2025

declare(strict_types=1);
session_start();

// -------------------------- CONFIGURATION --------------------------
$DB_HOST = '127.0.0.1';
$DB_NAME = 'lms_database';
$DB_USER = 'root';
$DB_PASS = '';
$DSN = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

// Site defaults
$SITE_FULLNAME = 'My LMS Site';
$SITE_PUBLIC_PROFILE_DEFAULT = "<p>This user has not created a public profile yet.</p>";

// -------------------------- HELPERS --------------------------
function pdo_connect(string $dsn, string $user, string $pass): PDO {
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $user, $pass, $opts);
}

function require_login_or_redirect(): void {
    if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        header('Location: /login.php');
        exit;
    }
}

function current_user_id(): ?int {
    return $_SESSION['user']['id'] ?? null;
}

function sanitize_html(string $html): string {
    // Minimal sanitization: allow basic tags. Replace with a library (e.g. HTMLPurifier) in production.
    $allowed = '<p><a><br><strong><em><ul><ol><li><b><i><u><img>';
    return strip_tags($html, $allowed);
}

function avatar_url_for_user(int $userid): string {
    $path = __DIR__ . '/../uploads/user_images/' . $userid . '_f1.jpg';
    if (file_exists($path)) {
        return '/uploads/user_images/' . $userid . '_f1.jpg';
    }
    return '/assets/default_user.png';
}

// -------------------------- CONNECT --------------------------
try {
    $pdo = pdo_connect($DSN, $DB_USER, $DB_PASS);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

// -------------------------- ROUTING / INPUT --------------------------
$requestedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editToggle    = isset($_GET['edit']) ? (bool)$_GET['edit'] : null; // ?edit=1 or 0
$resetAction   = isset($_GET['reset']) ? (bool)$_GET['reset'] : false;

// If no id provided, show current user's profile if logged in, otherwise 404.
if ($requestedId <= 0) {
    $uid = current_user_id();
    if ($uid === null) {
        http_response_code(404);
        echo "User not found.";
        exit;
    }
    $requestedId = $uid;
}

// Fetch requested user
$stmt = $pdo->prepare('SELECT id, firstname, lastname, email, profile_description, profile_public, created_at FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $requestedId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo "User not found.";
    exit;
}

$viewerId = current_user_id();
$isOwner = ($viewerId !== null && $viewerId === (int)$user['id']);

// Simple privacy: if profile_public == 0 and viewer is not owner, deny.
if ((int)$user['profile_public'] === 0 && !$isOwner) {
    http_response_code(403);
    echo "This profile is private.";
    exit;
}

// -------------------------- HANDLE POST (save edits) --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only owner can post edits.
    if (!$isOwner) {
        http_response_code(403);
        echo "Not allowed.";
        exit;
    }

    // Basic CSRF using session token
    $sesskey = $_POST['sesskey'] ?? '';
    if (!hash_equals($_SESSION['sesskey'] ?? '', (string)$sesskey)) {
        http_response_code(400);
        echo "Invalid session key.";
        exit;
    }

    $newdesc = $_POST['description'] ?? '';
    $publicFlag = isset($_POST['profile_public']) ? 1 : 0;

    // Sanitize minimal HTML
    $newdesc = sanitize_html($newdesc);

    $up = $pdo->prepare('UPDATE users SET profile_description = :desc, profile_public = :pub WHERE id = :id');
    $up->execute(['desc' => $newdesc, 'pub' => $publicFlag, 'id' => $user['id']]);

    // Reload user
    $stmt = $pdo->prepare('SELECT id, firstname, lastname, email, profile_description, profile_public, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $requestedId]);
    $user = $stmt->fetch();

    // redirect to avoid resubmission
    header('Location: profile.php?id=' . $user['id']);
    exit;
}

// -------------------------- HANDLE RESET --------------------------
if ($resetAction && $isOwner) {
    // Reset to default profile (clear description or set to site default)
    $resetStmt = $pdo->prepare('UPDATE users SET profile_description = :desc WHERE id = :id');
    $resetStmt->execute(['desc' => $SITE_PUBLIC_PROFILE_DEFAULT, 'id' => $user['id']]);

    header('Location: profile.php?id=' . $user['id']);
    exit;
}

// -------------------------- EDIT STATE --------------------------
// Determine if editing is on: owner can toggle (via ?edit=1)
$editing = false;
if ($isOwner) {
    if ($editToggle !== null) {
        // toggle editing in session for persistence
        $_SESSION['profile_editing_' . $user['id']] = $editToggle ? 1 : 0;
    }
    $editing = !empty($_SESSION['profile_editing_' . $user['id']]);
}

// -------------------------- SESSION KEY --------------------------
if (empty($_SESSION['sesskey'])) {
    $_SESSION['sesskey'] = bin2hex(random_bytes(16));
}
$sesskey = $_SESSION['sesskey'];

// -------------------------- RENDER PAGE --------------------------
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
$fullname = trim($user['firstname'] . ' ' . $user['lastname']);
$avatar = avatar_url_for_user((int)$user['id']);
$descriptionHtml = $user['profile_description'] ? $user['profile_description'] : $SITE_PUBLIC_PROFILE_DEFAULT;
$publicChecked = ((int)$user['profile_public'] === 1) ? 'checked' : '';

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($fullname) ?> — Public Profile</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fa;margin:0;padding:20px}
.container{max-width:900px;margin:24px auto;background:#fff;padding:24px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
.header{display:flex;gap:16px;align-items:center}
.header img{width:96px;height:96px;border-radius:8px;object-fit:cover;border:1px solid #ddd}
.h1{font-size:20px;margin:0}
.meta{color:#666;font-size:13px}
.controls{margin-left:auto}
.btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;border:1px solid #ccc;background:#fff;color:#333}
.btn-primary{background:#0073e6;color:#fff;border-color:#0073e6}
.form-area textarea{width:100%;min-height:160px;padding:10px;border-radius:8px;border:1px solid #ccc}
.small{font-size:13px;color:#666}
.notice{background:#eef7ff;padding:10px;border:1px solid #d6ebff;border-radius:8px;margin:12px 0}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="<?= h($avatar) ?>" alt="avatar">
        <div>
            <h1 class="h1"><?= h($fullname) ?></h1>
            <div class="meta">Member since: <?= h(date('Y-m-d', strtotime($user['created_at'] ?? 'now'))) ?></div>
            <div class="meta">Email: <?= h($user['email']) ?></div>
        </div>

        <div class="controls">
            <?php if ($isOwner): ?>
                <?php if ($editing): ?>
                    <a class="btn" href="profile.php?id=<?= $user['id'] ?>&edit=0">Exit edit</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="profile.php?id=<?= $user['id'] ?>&edit=1">Edit profile</a>
                <?php endif; ?>
                <a class="btn" href="profile.php?id=<?= $user['id'] ?>&reset=1" onclick="return confirm('Reset public profile to default?')">Reset</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($editing) && $isOwner): ?>
        <div class="form-area" style="margin-top:18px">
            <form method="post" action="profile.php?id=<?= $user['id'] ?>">
                <input type="hidden" name="sesskey" value="<?= h($sesskey) ?>">
                <label class="small">Public profile visible to others: <input type="checkbox" name="profile_public" <?= $publicChecked ?>></label>
                <p class="small">You may use a little HTML: &lt;p&gt;, &lt;strong&gt;, &lt;em&gt; etc. (Sanitized.)</p>
                <textarea name="description"><?= h($user['profile_description']) ?></textarea>
                <div style="margin-top:10px">
                    <button class="btn btn-primary" type="submit">Save profile</button>
                    <a class="btn" href="profile.php?id=<?= $user['id'] ?>&edit=0">Cancel</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <?php if (!empty($descriptionHtml)): ?>
            <div class="notice" style="margin-top:18px">
                <?= $descriptionHtml /* already sanitized when saved */ ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <h3 style="margin-top:22px">Public fields</h3>
    <ul>
        <li><strong>Name:</strong> <?= h($fullname) ?></li>
        <li><strong>Email:</strong> <?= h($user['email']) ?></li>
        <!-- Add other public fields here -->
    </ul>
</div>
</body>
</html>
