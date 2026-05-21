<?php
header('Content-Type: application/json');
include 'config.php';
include 'admin_helpers.php';

$actor = actor_from_request();

$id = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} elseif (isset($_GET['ID'])) {
    $id = intval($_GET['ID']);
}

if ($id !== null && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            log_activity($conn, $actor, 'feedback_deleted', 'Deleted feedback id=' . $id);
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Record not found in the database."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "No ID specified."]);
}

$conn->close();
?>
