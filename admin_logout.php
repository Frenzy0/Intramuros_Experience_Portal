<?php
header('Content-Type: application/json');
include 'config.php';
include 'admin_helpers.php';

$username = $_SESSION['admin_username'] ?? '';

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

if ($username !== '') {
    log_activity($conn, $username, 'logout', 'Admin signed out.');
}

echo json_encode(['status' => 'success']);
$conn->close();
