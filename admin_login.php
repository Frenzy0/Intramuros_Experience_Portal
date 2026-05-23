<?php
header('Content-Type: application/json');
include 'config.php';
include 'admin_helpers.php';

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $response['message'] = 'Username and password are required.';
    echo json_encode($response);
    exit();
}

// Auto-seed default admin (admin / password123) if the table is empty.
$count_result = $conn->query("SELECT COUNT(*) AS c FROM admin_users");
if ($count_result) {
    $row = $count_result->fetch_assoc();
    if ((int)$row['c'] === 0) {
        $default_hash = password_hash('password123', PASSWORD_BCRYPT);
        $seed = $conn->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        $default_user = 'admin';
        $seed->bind_param('ss', $default_user, $default_hash);
        $seed->execute();
        $seed->close();
    }
}

$stmt = $conn->prepare("SELECT id, username, password_hash FROM admin_users WHERE BINARY username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        log_activity($conn, $admin['username'], 'login_success', 'Admin signed in.');
        $response = [
            'status' => 'success',
            'message' => 'Logged in successfully.',
            'username' => $admin['username']
        ];
    } else {
        log_activity($conn, $username, 'login_failed', 'Wrong password.');
        $response['message'] = 'Invalid username or password.';
    }
} else {
    log_activity($conn, $username, 'login_failed', 'Unknown username.');
    $response['message'] = 'Invalid username or password.';
}

$stmt->close();
$conn->close();

echo json_encode($response);
