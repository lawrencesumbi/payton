<?php
// log_helper.php
function addLog($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $action]);
}