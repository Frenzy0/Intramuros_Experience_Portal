<?php
header('Content-Type: application/json');
include 'config.php';
include 'admin_helpers.php';

$actor = actor_from_request();

$count_result = $conn->query("SELECT COUNT(*) AS c FROM survey");
$count = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $count = (int)$row['c'];
}

$sql = "TRUNCATE TABLE survey";

if ($conn->query($sql) === TRUE) {
    log_activity($conn, $actor, 'survey_cleared', "Cleared $count survey response(s).");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$conn->close();
?>
