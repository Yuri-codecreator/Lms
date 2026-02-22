<?php
session_start();
require_once('externallib.php'); // Our standalone library

require_login();
$userId = $_SESSION['user_id'];

// Fake course ID from GET
$courseId = intval($_GET['course'] ?? 1);

// Pagination
$page = max(0, intval($_GET['page'] ?? 0));
$perpage = max(1, intval($_GET['perpage'] ?? 20));
$offset = $page * $perpage;

// Optional filters
$groupId = intval($_GET['group'] ?? 0);
$roleId = intval($_GET['role'] ?? 0);

// Fetch all users in this course (filtered by group/role if needed)
$allUsers = get_course_users($courseId, $groupId, $roleId); // Implement in externallib.php
$totalUsers = count($allUsers);
$users = array_slice($allUsers, $offset, $perpage);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Course Participants</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        th { background: #eee; }
        .pagination { margin-top: 20px; }
    </style>
</head>
<body>
<h1>Participants in Course <?php echo htmlspecialchars($courseId); ?></h1>
<p>Total users: <?php echo $totalUsers; ?></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['id']); ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <a href="message.php?to=<?php echo $u['id']; ?>">Message</a> |
                    <a href="note.php?user=<?php echo $u['id']; ?>">Add Note</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($page > 0): ?>
        <a href="?course=<?php echo $courseId; ?>&page=<?php echo $page-1; ?>&perpage=<?php echo $perpage; ?>">Prev</a>
    <?php endif; ?>
    <?php if (($offset + $perpage) < $totalUsers): ?>
        <a href="?course=<?php echo $courseId; ?>&page=<?php echo $page+1; ?>&perpage=<?php echo $perpage; ?>">Next</a>
    <?php endif; ?>
</div>

</body>
</html>
