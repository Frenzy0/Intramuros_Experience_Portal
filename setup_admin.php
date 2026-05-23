<?php
/**
 * setup_admin.php — one-shot bootstrap/recovery utility.
 *
 * Use this to create the first admin account, or to reset a forgotten
 * admin password. Hashes the password with bcrypt (password_hash) and
 * writes it directly to the admin_users table.
 *
 * SECURITY: DELETE THIS FILE FROM THE SERVER IMMEDIATELY AFTER USE.
 * Anyone who can reach this URL can take over the admin account.
 */

require __DIR__ . '/Config.php';
require __DIR__ . '/admin_helpers.php';

$errors  = [];
$success = null;
$form    = ['username' => '', 'mode' => ''];

function validate_password($pwd) {
    $missing = [];
    if (strlen($pwd) < 12)                      $missing[] = '12+ characters';
    if (!preg_match('/[A-Z]/', $pwd))           $missing[] = 'an uppercase letter';
    if (!preg_match('/[a-z]/', $pwd))           $missing[] = 'a lowercase letter';
    if (!preg_match('/[0-9]/', $pwd))           $missing[] = 'a number';
    if (!preg_match('/[^A-Za-z0-9]/', $pwd))    $missing[] = 'a special character';
    return $missing;
}

// Self-delete action.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'self_delete') {
    $deleted = @unlink(__FILE__);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>setup_admin.php</title>';
    echo '<body style="font-family:system-ui,Segoe UI,Arial,sans-serif;max-width:640px;margin:64px auto;padding:24px;">';
    if ($deleted) {
        echo '<h1 style="color:#0a7f3f;">setup_admin.php deleted</h1>';
        echo '<p>The setup file has been removed from the server. You can close this tab.</p>';
        echo '<p><a href="Index.php">Go to the homepage</a></p>';
    } else {
        echo '<h1 style="color:#c0392b;">Could not auto-delete</h1>';
        echo '<p>The web server user does not have permission to delete <code>'
           . htmlspecialchars(__FILE__, ENT_QUOTES, 'UTF-8') . '</code>.</p>';
        echo '<p><strong>Please delete this file manually right now.</strong></p>';
    }
    echo '</body>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $username        = trim($_POST['username']        ?? '');
    $password        = $_POST['password']              ?? '';
    $confirm         = $_POST['confirm_password']      ?? '';
    $form['username'] = $username;

    if ($username === '')                $errors[] = 'Username is required.';
    if (strlen($username) > 50)          $errors[] = 'Username must be 50 characters or fewer.';
    if ($password === '')                $errors[] = 'Password is required.';
    if ($password !== $confirm)          $errors[] = 'Password and confirmation do not match.';

    $missing = validate_password($password);
    if (!empty($missing)) {
        $errors[] = 'Password must include ' . implode(', ', $missing) . '.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Case-sensitive lookup so admin and ADMIN are distinct.
        $check = $conn->prepare("SELECT id FROM admin_users WHERE BINARY username = ? LIMIT 1");
        $check->bind_param('s', $username);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing) {
            $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $existing['id']);
            $stmt->execute();
            $stmt->close();
            $form['mode'] = 'updated';
            log_activity($conn, $username, 'profile_updated', 'Password reset via setup_admin.php');
        } else {
            $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
            $stmt->bind_param('ss', $username, $hash);
            $stmt->execute();
            $stmt->close();
            $form['mode'] = 'created';
            log_activity($conn, $username, 'profile_updated', 'Admin created via setup_admin.php');
        }
        $success = $username;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Setup — Intramuros Feedback Portal</title>
<meta name="robots" content="noindex,nofollow">
<style>
    body {
        font-family: system-ui, "Segoe UI", Arial, sans-serif;
        background: #f4f5f7;
        margin: 0;
        padding: 48px 16px;
        color: #222;
    }
    .card {
        max-width: 560px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
        padding: 28px 32px;
        box-shadow: 0 6px 20px rgba(0,0,0,.08);
    }
    h1 { margin: 0 0 8px; font-size: 22px; }
    p  { line-height: 1.5; }
    .warn {
        background: #fff3cd;
        border: 1px solid #ffe69c;
        color: #664d03;
        padding: 12px 14px;
        border-radius: 6px;
        margin: 16px 0 24px;
        font-size: 14px;
    }
    .danger {
        background: #f8d7da;
        border: 1px solid #f1aeb5;
        color: #842029;
        padding: 14px 16px;
        border-radius: 6px;
        margin: 16px 0 24px;
    }
    .success {
        background: #d1e7dd;
        border: 1px solid #a3cfbb;
        color: #0f5132;
        padding: 14px 16px;
        border-radius: 6px;
        margin: 16px 0 24px;
    }
    label {
        display: block;
        margin: 14px 0 4px;
        font-weight: 600;
        font-size: 14px;
    }
    input[type=text], input[type=password] {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #c4c9d1;
        border-radius: 6px;
        font-size: 15px;
    }
    input:focus { outline: none; border-color: #4a6ee0; }
    .rules {
        font-size: 13px;
        color: #555;
        margin: 6px 0 0;
    }
    .btn {
        display: inline-block;
        margin-top: 18px;
        padding: 10px 18px;
        border: 0;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-primary { background: #2d6cdf; color: #fff; }
    .btn-primary:hover { background: #224fa6; }
    .btn-danger  { background: #c0392b; color: #fff; }
    .btn-danger:hover  { background: #922d22; }
    code {
        background: #f0f2f5;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 13px;
    }
    .row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .pwd-wrap { position: relative; }
    .pwd-toggle {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: 0;
        cursor: pointer;
        font-size: 13px;
        color: #2d6cdf;
        padding: 4px 8px;
    }
    ul.errors { color: #842029; margin: 0; padding-left: 20px; }
</style>
</head>
<body>
<div class="card">
    <h1>Admin Setup</h1>
    <p>Create a new admin account, or reset the password for an existing one.</p>

    <div class="warn">
        <strong>Security notice:</strong> this file lets anyone with the URL
        create or overwrite an admin account. <strong>Delete it from the
        server the moment you are done.</strong> File path:
        <code><?= htmlspecialchars(__FILE__, ENT_QUOTES, 'UTF-8') ?></code>
    </div>

    <?php if ($success): ?>
        <div class="success">
            <strong>Success.</strong>
            Admin <code><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></code>
            was <?= $form['mode'] === 'created' ? 'created' : 'updated' ?>.
            You can now sign in from <a href="Index.php">the homepage</a>.
        </div>
        <div class="danger">
            <strong>Now delete <code>setup_admin.php</code>.</strong>
            Leaving it on the server is a takeover risk. Use the button below
            to delete it automatically, or remove it manually from
            <code><?= htmlspecialchars(__DIR__, ENT_QUOTES, 'UTF-8') ?></code>.
            <form method="post" style="margin-top:12px;"
                  onsubmit="return confirm('Delete setup_admin.php from the server now?');">
                <input type="hidden" name="action" value="self_delete">
                <button type="submit" class="btn btn-danger">Delete setup_admin.php now</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="danger">
            <strong>Please fix the following:</strong>
            <ul class="errors">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="hidden" name="action" value="save">

        <label for="username">Username (case-sensitive)</label>
        <input type="text" id="username" name="username" maxlength="50" required
               value="<?= htmlspecialchars($form['username'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="password">Password</label>
        <div class="pwd-wrap">
            <input type="password" id="password" name="password" required>
            <button type="button" class="pwd-toggle"
                    onclick="togglePwd('password', this)">Show</button>
        </div>
        <p class="rules">Min 12 characters, with uppercase, lowercase, number, and special character.</p>

        <label for="confirm_password">Confirm password</label>
        <div class="pwd-wrap">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="button" class="pwd-toggle"
                    onclick="togglePwd('confirm_password', this)">Show</button>
        </div>

        <div class="row">
            <button type="submit" class="btn btn-primary">Save admin account</button>
        </div>
    </form>
</div>

<script>
function togglePwd(id, btn) {
    var el = document.getElementById(id);
    if (!el) return;
    var showing = el.type === 'password';
    el.type = showing ? 'text' : 'password';
    btn.textContent = showing ? 'Hide' : 'Show';
}
</script>
</body>
</html>
