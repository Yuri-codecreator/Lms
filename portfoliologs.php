<?php
// ============================================================================
// Standalone Portfolio Logs Page
// Rewritten from Moodle's portfoliologs.php for independent PHP use.
// Author: Jhon Robert N. Carrera (Project Manager) & Team
// Date: 2025
// ============================================================================

// DATABASE CONNECTION SETTINGS
$host = "localhost";       // Your MySQL host
$user = "root";            // Your MySQL username
$pass = "";                // Your MySQL password
$dbname = "lms_database";  // Your database name

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// GET PARAMETERS
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$perpage = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 10;
$start = $page * $perpage;

// Assume logged-in user (temporary)
$userid = 1; // You can replace this with your session user ID

// COUNT TOTAL LOGS
$totalLogsQuery = $conn->prepare("SELECT COUNT(*) AS total FROM portfolio_log WHERE userid = ?");
$totalLogsQuery->bind_param("i", $userid);
$totalLogsQuery->execute();
$totalLogsResult = $totalLogsQuery->get_result()->fetch_assoc();
$totalLogs = $totalLogsResult['total'] ?? 0;

// FETCH LOGS
$query = $conn->prepare("SELECT plugin, displayarea, transfertime FROM portfolio_log WHERE userid = ? ORDER BY transfertime DESC LIMIT ?, ?");
$query->bind_param("iii", $userid, $start, $perpage);
$query->execute();
$result = $query->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portfolio Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #0073e6;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .pagination {
            text-align: center;
            margin-top: 15px;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #0073e6;
            font-weight: bold;
        }
        .pagination a.active {
            color: #fff;
            background-color: #0073e6;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>📘 Portfolio Logs</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Plugin</th>
                <th>Display Area</th>
                <th>Transfer Time</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['plugin']) ?></td>
                    <td><?= htmlspecialchars($row['displayarea']) ?></td>
                    <td><?= date('Y-m-d H:i:s', strtotime($row['transfertime'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            $totalPages = ceil($totalLogs / $perpage);
            for ($i = 0; $i < $totalPages; $i++):
                $active = ($i === $page) ? 'active' : '';
                echo "<a class='$active' href='?page=$i&perpage=$perpage'>" . ($i + 1) . "</a>";
            endfor;
            ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; color:#555;">No portfolio logs found for this user.</p>
    <?php endif; ?>

</body>
</html>
<?php
$conn->close();
?>
