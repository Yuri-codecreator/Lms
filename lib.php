<?php
/**
 * lib_rewritten.php
 * Standalone library for LMS — no Moodle dependency.
 */

/**
 * Return an array of switchable roles for a given context.
 *
 * @param mixed $context
 * @param PDO|null $pdo
 * @return array
 */
function get_switchable_roles($context, ?PDO $pdo = null): array {
    $roles = [
        ['id' => 1, 'shortname' => 'student', 'name' => 'Student'],
        ['id' => 2, 'shortname' => 'teacher', 'name' => 'Teacher'],
        ['id' => 3, 'shortname' => 'manager', 'name' => 'Manager'],
    ];

    if ($pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('SELECT id, shortname, name FROM roles WHERE context = :context OR context IS NULL');
            $stmt->execute(['context' => $context]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows && count($rows) > 0) {
                $roles = $rows;
            }
        } catch (Exception $e) {
            // fall back to static roles
        }
    }

    return $roles;
}

/**
 * Build a switch-role link object (not HTML) for use in templates.
 *
 * @param int $courseId
 * @param string $returnUrl
 * @param string $baseUrl
 * @return object
 */
function build_switchrole_link(int $courseId, string $returnUrl, string $baseUrl = '/course/switchrole.php'): object {
    $url = $baseUrl . '?id=' . urlencode($courseId) . '&switchrole=-1&returnurl=' . urlencode($returnUrl);
    return (object)[
        'itemtype' => 'link',
        'url' => $url,
        'title' => 'Switch role',
        'titleidentifier' => 'switchroleto',
    ];
}

/**
 * Check whether the current session is impersonating another user.
 *
 * @return bool
 */
function is_logged_in_as(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['impersonated_user_id']);
}

/**
 * Save or update a user's device public key.
 *
 * @param PDO $pdo
 * @param string $uuid
 * @param string $appid
 * @param string $publickey
 * @param int $userid
 * @return bool
 */
function save_user_device_publickey(PDO $pdo, string $uuid, string $appid, string $publickey, int $userid): bool {
    if (!$pdo instanceof PDO) {
        throw new InvalidArgumentException('A valid PDO instance is required.');
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) as cnt FROM user_devices WHERE uuid = :uuid AND appid = :appid AND userid = :userid');
    $stmt->execute(['uuid' => $uuid, 'appid' => $appid, 'userid' => $userid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && (int)$row['cnt'] > 0) {
        $up = $pdo->prepare('UPDATE user_devices SET publickey = :publickey, updated_at = CURRENT_TIMESTAMP WHERE uuid = :uuid AND appid = :appid AND userid = :userid');
        return $up->execute(['publickey' => $publickey, 'uuid' => $uuid, 'appid' => $appid, 'userid' => $userid]);
    } else {
        $ins = $pdo->prepare('INSERT INTO user_devices (uuid, appid, userid, publickey, created_at, updated_at) VALUES (:uuid, :appid, :userid, :publickey, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        return $ins->execute(['uuid' => $uuid, 'appid' => $appid, 'userid' => $userid, 'publickey' => $publickey]);
    }
}

/**
 * Helper to initialize PDO connection.
 *
 * @return PDO
 */
function init_pdo(string $host, string $dbname, string $user, string $pass, array $options = []): PDO {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $defaultOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $opts = $options + $defaultOptions;
    return new PDO($dsn, $user, $pass, $opts);
}

/**
 * Simple translation stub.
 */
function get_string_stub(string $identifier, ?string $component = null): string {
    $map = [
        'switchroleto' => 'Switch role to...',
    ];
    return $map[$identifier] ?? $identifier;
}
?>
