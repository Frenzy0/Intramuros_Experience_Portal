<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $helpful = isset($_POST['helpful']) ? $conn->real_escape_string($_POST['helpful']) : '';
    $surveySuggestions = isset($_POST['surveySuggestions']) ? $conn->real_escape_string($_POST['surveySuggestions']) : '';

    $sql = "INSERT INTO survey (helpful, survey_suggestions) 
        VALUES ('$helpful', '$surveySuggestions')";

    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>