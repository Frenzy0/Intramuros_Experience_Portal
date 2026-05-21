<?php
include 'config.php';

$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);

$feedback_data = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $feedback_data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($feedback_data);

$conn->close();
?>