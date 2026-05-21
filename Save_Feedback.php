<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nationality = $_POST['nationality'];
    $visitDate = $_POST['visitDate'];
    $cleanliness = $_POST['cleanliness'];
    $restroom = $_POST['restroom'];
    $guides = $_POST['guides'];
    $accommodation = $_POST['accommodation'];
    $overall = $_POST['overall'];
    $nps = $_POST['nps'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("INSERT INTO feedback (nationality, visit_date, cleanliness, restroom, guides, accommodation, overall, nps, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiiiiis", $nationality, $visitDate, $cleanliness, $restroom, $guides, $accommodation, $overall, $nps, $comments);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>