<?php
header('Content-Type: application/json');
include 'config.php';

$limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 100;

$entries = [];
$sql = "SELECT id, username, action, details, created_at
        FROM activity_log
        ORDER BY created_at DESC, id DESC
        LIMIT $limit";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    echo json_encode(['status' => 'success', 'entries' => $entries]);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error, 'entries' => []]);
}

$conn->close();
