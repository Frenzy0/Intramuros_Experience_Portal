<?php
header('Content-Type: application/json');
include 'config.php';
include 'admin_helpers.php';

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit();
}

$current_username = trim($_POST['current_username'] ?? '');
$old_password     = $_POST['old_password'] ?? '';
$new_username     = trim($_POST['new_username'] ?? '');
$new_password     = $_POST['new_password'] ?? '';

if ($current_username === '' || $old_password === '') {
    $response['message'] = 'Current username and old password are required.';
    echo json_encode($response);
    exit();
}

if ($new_username === '' && $new_password === '') {
    $response['message'] = 'Provide a new username, a new password, or both.';
    echo json_encode($response);
    exit();
}

if ($new_password !== '' && strlen($new_password) < 6) {
    $response['message'] = 'New password must be at least 6 characters.';
    echo json_encode($response);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $current_username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows !== 1) {
    $response['message'] = 'Account not found.';
    $stmt->close();
    echo json_encode($response);
    exit();
}

$admin = $result->fetch_assoc();
$stmt->close();

if (!password_verify($old_password, $admin['password_hash'])) {
    log_activity($conn, $current_username, 'profile_update_failed', 'Old password did not match.');
    $response['message'] = 'Old password is incorrect.';
    echo json_encode($response);
    exit();
}

$final_username = $new_username !== '' ? $new_username : $admin['username'];
$final_hash     = $new_password !== ''
    ? password_hash($new_password, PASSWORD_BCRYPT)
    : $admin['password_hash'];

if ($new_username !== '' && $new_username !== $admin['username']) {
    $check = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id <> ? LIMIT 1");
    $check->bind_param('si', $new_username, $admin['id']);
    $check->execute();
    $check_res = $check->get_result();
    if ($check_res && $check_res->num_rows > 0) {
        $check->close();
        $response['message'] = 'That username is already taken.';
        echo json_encode($response);
        exit();
    }
    $check->close();
}

$update = $conn->prepare("UPDATE admin_users SET username = ?, password_hash = ? WHERE id = ?");
$update->bind_param('ssi', $final_username, $final_hash, $admin['id']);

if ($update->execute()) {
    $changed = [];
    if ($new_username !== '' && $new_username !== $admin['username']) $changed[] = 'username';
    if ($new_password !== '') $changed[] = 'password';
    $details = 'Updated: ' . implode(', ', $changed);

    log_activity($conn, $final_username, 'profile_updated', $details);

    $response = [
        'status' => 'success',
        'message' => 'Credentials updated successfully.',
        'username' => $final_username
    ];
} else {
    $response['message'] = 'Failed to update credentials: ' . $conn->error;
}

$update->close();
$conn->close();

echo json_encode($response);
