<?php
function get_user_by_id($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>
