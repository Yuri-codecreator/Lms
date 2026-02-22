<?php
/**
 * User Renderer (Non-Moodle Version)
 * -----------------------------------
 * Provides functions to render user-related pages such as:
 * - Search users
 * - List users
 * - Filter users
 *
 * This version works standalone, not with Moodle.
 * Compatible with your own LMS project.
 */

// --- Database Connection ---
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "lms_db"; // change this to your DB name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/**
 * Displays a user search form and result table.
 */
function render_user_search($conn)
{
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    echo "<h2>🔍 User Search</h2>";
    echo '<form method="GET">';
    echo '<input type="text" name="search" placeholder="Enter user name..." value="' . htmlspecialchars($search) . '">';
    echo '<button type="submit">Search</button>';
    echo '</form>';

    $query = "SELECT * FROM users WHERE fullname LIKE ? OR email LIKE ? ORDER BY fullname";
    $stmt = $conn->prepare($query);
    $like = "%" . $search . "%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<p>Found <b>{$result->num_rows}</b> users</p>";

    echo '<table border="1" cellpadding="8" cellspacing="0" width="100%">';
    echo '<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Last Access</th></tr>';

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['last_access']) . "</td>";
        echo "</tr>";
    }

    echo '</table>';
}

/**
 * Displays all users grouped by first letter of their name.
 */
function render_user_list($conn)
{
    echo "<h2>👥 User List</h2>";

    $result = $conn->query("SELECT * FROM users ORDER BY fullname ASC");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $initial = strtoupper(substr($row['fullname'], 0, 1));
        $users[$initial][] = $row;
    }

    foreach ($users as $letter => $group) {
        echo "<h3>$letter</h3>";
        echo "<ul>";
        foreach ($group as $user) {
            echo "<li>" . htmlspecialchars($user['fullname']) . " ({$user['email']})</li>";
        }
        echo "</ul>";
    }
}

/**
 * Filters users by role or last access.
 */
function render_user_filter($conn)
{
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    $filter = "";
    if ($role !== '') {
        $filter = "WHERE role = ?";
    }

    echo "<h2>⚙️ Filter Users</h2>";
    echo '<form method="GET">';
    echo '<select name="role">
            <option value="">All Roles</option>
            <option value="Admin"' . ($role == "Admin" ? " selected" : "") . '>Admin</option>
            <option value="Teacher"' . ($role == "Teacher" ? " selected" : "") . '>Teacher</option>
            <option value="Student"' . ($role == "Student" ? " selected" : "") . '>Student</option>
          </select>';
    echo '<button type="submit">Filter</button>';
    echo '</form>';

    $sql = "SELECT * FROM users $filter ORDER BY fullname ASC";
    $stmt = $conn->prepare($sql);

    if ($filter !== '') {
        $stmt->bind_param("s", $role);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo "<p>Showing <b>{$result->num_rows}</b> user(s)</p>";

    echo "<table border='1' width='100%' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th></tr>";

    while ($user = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Renderer</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f6f7; }
        table { background: #fff; border-collapse: collapse; }
        th { background: #273c75; color: white; }
        tr:nth-child(even) { background: #f1f2f6; }
        input, select, button { padding: 8px; margin: 5px; }
        button { background: #44bd32; color: white; border: none; cursor: pointer; }
        button:hover { background: #4cd137; }
    </style>
</head>
<body>

<h1>User Renderer (Standalone Version)</h1>

<?php
// You can comment/uncomment what you need:
render_user_search($conn);
render_user_filter($conn);
render_user_list($conn);
?>

</body>
</html>
