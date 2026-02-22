<?php
// externallib.php
// Standalone external library for users, private files, forum preferences, and course participants

header('Content-Type: application/json');
session_start();

// -------------------
// Database connection
// -------------------
$host = 'localhost';
$db   = 'your_database';
$user = 'db_user';
$pass = 'db_pass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// -------------------
// Authentication
// -------------------
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'You must be logged in.']);
        exit;
    }
}

function get_current_user() {
    if (!isset($_SESSION['user_id'])) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['user_name'] ?? 'User'
    ];
}

// -------------------
// User management
// -------------------
function get_user_info($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_user_email($id, $newEmail) {
    global $pdo;
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        return ['error' => 'Invalid email format'];
    }

    // Check for duplicates
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$newEmail, $id]);
    if ($stmt->fetch()) return ['error' => 'Email already exists'];

    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->execute([$newEmail, $id]);
    return ['success' => 'Email updated successfully'];
}

// -------------------
// Private file management
// -------------------
function list_user_files($userId) {
    $userDir = __DIR__ . "/private_files/user_$userId";
    if (!is_dir($userDir)) return [];
    return array_values(array_diff(scandir($userDir), ['.', '..']));
}

function upload_user_file($userId, $file) {
    $userDir = __DIR__ . "/private_files/user_$userId";
    if (!is_dir($userDir)) mkdir($userDir, 0755, true);

    $target = $userDir . '/' . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => 'File uploaded successfully'];
    }
    return ['error' => 'Failed to upload file'];
}

function delete_user_file($userId, $filename) {
    $userDir = __DIR__ . "/private_files/user_$userId";
    $filePath = $userDir . '/' . basename($filename);
    if (file_exists($filePath)) {
        unlink($filePath);
        return ['success' => 'File deleted successfully'];
    }
    return ['error' => 'File not found'];
}

// -------------------
// Forum preferences management
// -------------------
function get_forum_preferences($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $prefs = $stmt->fetch();
    if (!$prefs) {
        // default values
        $prefs = [
            'maildigest' => 0,
            'autosubscribe' => 1,
            'useexperimentalui' => 0,
            'trackforums' => 1,
            'markasreadonnotification' => 1
        ];
    }
    return $prefs;
}

function update_forum_preferences($userId, $data) {
    global $pdo;

    $maildigest = (int)($data['maildigest'] ?? 0);
    $autosubscribe = (int)($data['autosubscribe'] ?? 0);
    $useexperimentalui = (int)($data['useexperimentalui'] ?? 0);
    $trackforums = (int)($data['trackforums'] ?? 0);
    $markasreadonnotification = (int)($data['markasreadonnotification'] ?? 0);

    // Disable markasreadonnotification if trackforums is off
    if ($trackforums === 0) $markasreadonnotification = 0;

    $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, maildigest, autosubscribe, useexperimentalui, trackforums, markasreadonnotification)
        VALUES (:user_id, :maildigest, :autosubscribe, :useexperimentalui, :trackforums, :markasreadonnotification)
        ON DUPLICATE KEY UPDATE
            maildigest = :maildigest,
            autosubscribe = :autosubscribe,
            useexperimentalui = :useexperimentalui,
            trackforums = :trackforums,
            markasreadonnotification = :markasreadonnotification
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':maildigest' => $maildigest,
        ':autosubscribe' => $autosubscribe,
        ':useexperimentalui' => $useexperimentalui,
        ':trackforums' => $trackforums,
        ':markasreadonnotification' => $markasreadonnotification
    ]);

    return ['success' => true, 'message' => 'Forum preferences updated successfully'];
}

// -------------------
// Course participants
// -------------------
function get_course_users($courseId, $groupId = 0, $roleId = 0) {
    global $pdo;

    $sql = "SELECT u.id, u.username, u.email
            FROM users u
            JOIN course_users cu ON u.id = cu.user_id
            WHERE cu.course_id = :courseid";
    $params = ['courseid' => $courseId];

    if ($groupId) {
        if (is_array($groupId)) {
            $placeholders = implode(',', array_fill(0, count($groupId), '?'));
            $sql .= " AND cu.group_id IN ($placeholders)";
            $params = array_merge($params, $groupId);
        } else {
            $sql .= " AND cu.group_id = :groupid";
            $params['groupid'] = $groupId;
        }
    }

    if ($roleId) {
        if (is_array($roleId)) {
            $placeholders = implode(',', array_fill(0, count($roleId), '?'));
            $sql .= " AND cu.role_id IN ($placeholders)";
            $params = array_merge($params, $roleId);
        } else {
            $sql .= " AND cu.role_id = :roleid";
            $params['roleid'] = $roleId;
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($params));

    return $stmt->fetchAll();
}

// -------------------
// API Router
// -------------------
require_login();
$userId = $_SESSION['user_id'];

// Determine action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_user_info':
        echo json_encode(get_user_info($userId));
        break;

    case 'update_email':
        $newEmail = $_POST['email'] ?? '';
        echo json_encode(update_user_email($userId, $newEmail));
        break;

    case 'list_files':
        echo json_encode(list_user_files($userId));
        break;

    case 'upload_file':
        if (isset($_FILES['file'])) {
            echo json_encode(upload_user_file($userId, $_FILES['file']));
        } else {
            echo json_encode(['error' => 'No file uploaded']);
        }
        break;

    case 'delete_file':
        $filename = $_POST['filename'] ?? '';
        echo json_encode(delete_user_file($userId, $filename));
        break;

    case 'get_forum_preferences':
        echo json_encode(get_forum_preferences($userId));
        break;

    case 'update_forum_preferences':
        echo json_encode(update_forum_preferences($userId, $_POST));
        break;

    case 'get_course_users':
        $courseId = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
        $groupId = $_GET['group_id'] ?? $_POST['group_id'] ?? 0;
        $roleId = $_GET['role_id'] ?? $_POST['role_id'] ?? 0;
        echo json_encode(get_course_users($courseId, $groupId, $roleId));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
