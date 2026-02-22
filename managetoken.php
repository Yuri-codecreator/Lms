<?php
// managetoken.php - Standalone token management for a custom LMS

declare(strict_types=1);

require_once __DIR__ . '/lib.php'; // must provide init_pdo() or your DB helper

session_start();

// ---------- Configuration ----------
$dsn_host   = '127.0.0.1';
$dsn_name   = 'lms_db';
$dsn_user   = 'dbuser';
$dsn_pass   = 'dbpass';

// optionally override by config file / environment
$pdo = init_pdo($dsn_host, $dsn_name, $dsn_user, $dsn_pass);

// ---------- Helpers ----------
function require_login_or_redirect() {
    if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        header('Location: login.php');
        exit;
    }
}

function generate_token_string(int $length = 40): string {
    // cryptographically secure token
    return bin2hex(random_bytes((int)ceil($length / 2)));
}

function sesskey(): string {
    if (empty($_SESSION['sesskey'])) {
        $_SESSION['sesskey'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['sesskey'];
}

function require_sesskey(string $key): void {
    if (!hash_equals((string)sesskey(), (string)$key)) {
        http_response_code(400);
        echo "Invalid session key.";
        exit;
    }
}

function render_header(string $title = 'Manage Tokens') {
    echo "<!doctype html>\n<html lang='en'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>\n";
    echo "<title>" . htmlspecialchars($title) . "</title>\n";
    // minimal styling
    echo "<style>body{font-family:Arial,Helvetica,sans-serif;margin:20px} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ddd;padding:8px} th{background:#f7f7f7}</style>\n";
    echo "</head><body>\n";
    echo "<h1>" . htmlspecialchars($title) . "</h1>\n";
}

function render_footer() {
    echo "\n</body></html>";
}

// ---------- Authentication ----------
require_login_or_redirect();
$currentUserId = (int)$_SESSION['user']['id'];

// ---------- Actions: create, delete, reset ----------
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$tokenid = isset($_REQUEST['tokenid']) ? (int)$_REQUEST['tokenid'] : 0;
$confirm = isset($_REQUEST['confirm']) ? (bool)$_REQUEST['confirm'] : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create token
    if ($action === 'create_token') {
        require_sesskey($_POST['sesskey'] ?? '');
        $tokenname = trim((string)($_POST['tokenname'] ?? ''));
        $externalserviceid = isset($_POST['externalserviceid']) ? (int)$_POST['externalserviceid'] : null;
        if ($tokenname === '') {
            $_SESSION['flash_error'] = "Token name is required.";
            header('Location: managetoken.php');
            exit;
        }
        $tok = generate_token_string(48);
        $stmt = $pdo->prepare('INSERT INTO user_tokens (userid, token, tokenname, externalserviceid, restrictedusers, created_at) VALUES (:userid, :token, :tokenname, :externalserviceid, 0, NOW())');
        $stmt->execute([
            'userid' => $currentUserId,
            'token' => $tok,
            'tokenname' => $tokenname,
            'externalserviceid' => $externalserviceid,
        ]);
        $_SESSION['webservicenewlycreatedtoken'] = ['token' => $tok, 'tokenname' => $tokenname];
        header('Location: managetoken.php');
        exit;
    }

    // Delete token
    if ($action === 'delete_token') {
        require_sesskey($_POST['sesskey'] ?? '');
        $tokenid = (int)($_POST['tokenid'] ?? 0);
        if ($tokenid > 0) {
            $stmt = $pdo->prepare('DELETE FROM user_tokens WHERE id = :id AND userid = :userid');
            $stmt->execute(['id' => $tokenid, 'userid' => $currentUserId]);
        }
        header('Location: managetoken.php');
        exit;
    }

    // Reset token (regenerate) - implemented as delete + create new token with same name
    if ($action === 'reset_token') {
        require_sesskey($_POST['sesskey'] ?? '');
        $tokenid = (int)($_POST['tokenid'] ?? 0);
        if ($tokenid > 0) {
            // fetch token info for name and externalserviceid
            $stmt = $pdo->prepare('SELECT tokenname, externalserviceid FROM user_tokens WHERE id = :id AND userid = :userid');
            $stmt->execute(['id' => $tokenid, 'userid' => $currentUserId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $newTok = generate_token_string(48);
                $up = $pdo->prepare('UPDATE user_tokens SET token = :token, created_at = NOW() WHERE id = :id AND userid = :userid');
                $up->execute(['token' => $newTok, 'id' => $tokenid, 'userid' => $currentUserId]);
                $_SESSION['webservicenewlycreatedtoken'] = ['token' => $newTok, 'tokenname' => $row['tokenname']];
            }
        }
        header('Location: managetoken.php');
        exit;
    }
}

// ---------- Fetch tokens for display ----------
$stmt = $pdo->prepare('SELECT id, token, tokenname, externalserviceid, restrictedusers, created_at FROM user_tokens WHERE userid = :userid ORDER BY created_at DESC');
$stmt->execute(['userid' => $currentUserId]);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- Render page ----------
render_header('Manage Web Service Tokens');

if (!empty($_SESSION['flash_error'])) {
    echo "<div style='color:red'>" . htmlspecialchars($_SESSION['flash_error']) . "</div>\n";
    unset($_SESSION['flash_error']);
}

// If we have a newly created token in session, show it
if (!empty($_SESSION['webservicenewlycreatedtoken'])) {
    $nt = $_SESSION['webservicenewlycreatedtoken'];
    echo "<div style='background:#eef;padding:10px;margin-bottom:10px;border:1px solid #cde'>\n";
    echo "<strong>New token created:</strong><br>\n";
    echo "Name: " . htmlspecialchars($nt['tokenname']) . "<br>\n";
    echo "Token: <code>" . htmlspecialchars($nt['token']) . "</code>\n";
    echo "</div>\n";
    unset($_SESSION['webservicenewlycreatedtoken']);
}

// Create token form
?>
<h2>Create a new token</h2>
<form method="post" action="managetoken.php">
    <input type="hidden" name="action" value="create_token">
    <input type="hidden" name="sesskey" value="<?php echo htmlspecialchars(sesskey()); ?>">
    <label>Token name: <input type="text" name="tokenname" required></label>
    <label style="margin-left:10px">External service id (optional): <input type="number" name="externalserviceid" min="0"></label>
    <button type="submit" style="margin-left:10px">Create token</button>
</form>

<h2>Your tokens</h2>
<?php if (empty($tokens)): ?>
    <p>You have no tokens.</p>
<?php else: ?>
    <table>
        <thead><tr><th>Token name</th><th>Token (partial)</th><th>Created</th><th>Restricted</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($tokens as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['tokenname']); ?></td>
                <td><code><?php echo htmlspecialchars(substr($t['token'], 0, 8) . '...' . substr($t['token'], -6)); ?></code></td>
                <td><?php echo htmlspecialchars($t['created_at']); ?></td>
                <td><?php echo $t['restrictedusers'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <!-- Reset token -->
                    <form method="post" action="managetoken.php" style="display:inline">
                        <input type="hidden" name="action" value="reset_token">
                        <input type="hidden" name="tokenid" value="<?php echo (int)$t['id']; ?>">
                        <input type="hidden" name="sesskey" value="<?php echo htmlspecialchars(sesskey()); ?>">
                        <button type="submit" onclick="return confirm('Re-generate this token? This will replace the old token.')">Reset</button>
                    </form>

                    <!-- Delete token -->
                    <form method="post" action="managetoken.php" style="display:inline;margin-left:6px">
                        <input type="hidden" name="action" value="delete_token">
                        <input type="hidden" name="tokenid" value="<?php echo (int)$t['id']; ?>">
                        <input type="hidden" name="sesskey" value="<?php echo htmlspecialchars(sesskey()); ?>">
                        <button type="submit" onclick="return confirm('Delete this token permanently?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
render_footer();
