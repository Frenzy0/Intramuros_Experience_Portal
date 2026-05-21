<?php
function log_activity($conn, $username, $action, $details = '') {
    if (!$conn) return;
    $stmt = $conn->prepare("INSERT INTO activity_log (username, action, details) VALUES (?, ?, ?)");
    if ($stmt) {
        $u = $username !== '' ? $username : null;
        $stmt->bind_param('sss', $u, $action, $details);
        $stmt->execute();
        $stmt->close();
    }
}

function actor_from_request() {
    $actor = $_POST['actor'] ?? $_GET['actor'] ?? '';
    return is_string($actor) ? trim($actor) : '';
}
