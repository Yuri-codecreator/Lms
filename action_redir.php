<?php
/**
 * Simple user action redirect script (standalone PHP version, no Moodle).
 * 
 * Features:
 * - Accepts a form action (e.g., download, delete)
 * - Validates a simple session token (security check)
 * - Supports CSV download example
 * - Clean and framework-free
 */

session_start();

// --------------------------------------------------------------------
// 1. Simple CSRF protection (replacement for Moodle's confirm_sesskey)
// --------------------------------------------------------------------
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}
$token = $_SESSION['token'];

// --------------------------------------------------------------------
// 2. Handle incoming form submissions
// --------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['formaction'] ?? '';
    $userid = (int)($_POST['id'] ?? 0);
    $postedtoken = $_POST['token'] ?? '';

    // Validate token
    if (!hash_equals($token, $postedtoken)) {
        http_response_code(403);
        exit('Invalid session token.');
    }

    // Simulated "database" data (you can replace with real DB)
    $users = [
        1 => ['firstname' => 'John', 'lastname' => 'Doe'],
        2 => ['firstname' => 'Jane', 'lastname' => 'Smith'],
        3 => ['firstname' => 'Alex', 'lastname' => 'Brown']
    ];

    if (!isset($users[$userid])) {
        exit("Error: User not found.");
    }

    // ---------------------------------------------------------------
    // 3. Perform the selected action
    // ---------------------------------------------------------------
    switch ($action) {
        case 'download':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="user_'.$userid.'.csv"');
            echo "id,firstname,lastname\n";
            echo "{$userid},{$users[$userid]['firstname']},{$users[$userid]['lastname']}\n";
            exit;

        case 'delete':
            // Just a simulation — in real code, you’d delete from DB
            echo "<h2>User deleted successfully:</h2>";
            echo "<p>ID: {$userid}</p>";
            echo "<p>Name: {$users[$userid]['firstname']} {$users[$userid]['lastname']}</p>";
            break;

        case 'view':
            echo "<h2>User Info</h2>";
            echo "<p>ID: {$userid}</p>";
            echo "<p>Name: {$users[$userid]['firstname']} {$users[$userid]['lastname']}</p>";
            break;

        default:
            echo "<h2>Unknown action: $action</h2>";
            break;
    }

    echo '<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">Go Back</a></p>';
    exit();
}

// --------------------------------------------------------------------
// 4. Display simple form (GET request)
// --------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Action Redirect (Standalone)</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        form { margin-bottom: 20px; }
        input, select, button { padding: 8px; margin: 5px; }
    </style>
</head>
<body>
    <h1>User Action Redirect Example</h1>
    <form method="POST" action="">
        <label for="id">Select User ID:</label>
        <select name="id" id="id" required>
            <option value="1">1 - John Doe</option>
            <option value="2">2 - Jane Smith</option>
            <option value="3">3 - Alex Brown</option>
        </select>
        <br>

        <label for="formaction">Choose Action:</label>
        <select name="formaction" id="formaction" required>
            <option value="view">View</option>
            <option value="download">Download CSV</option>
            <option value="delete">Delete</option>
        </select>

        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <br><br>
        <button type="submit">Go</button>
    </form>
</body>
</html>
