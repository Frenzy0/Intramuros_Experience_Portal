# **LTFF - Code Explained**

A walkthrough of how the **Local Tourist Feedback System (Intramuros)** works. Each section shows a small piece of code and tells what it does and _why_ it’s there.

## **Table of Contents**

1. Big Picture
2. The Database (intramuros_db.sql)
3. Connecting to the Database (Config.php)
4. Loading Feedback for the Homepage (Index.php)
5. The Feedback Form (Index.php)
6. Visit Date Validation (Index.php + js/script.js)
7. Saving New Feedback (Index.php POST handler)
8. Sending the Form with JavaScript (js/script.js)
9. The Quick Survey (save_survey.php)
10. Admin Login (admin_login.php)
11. Protecting Admin Actions with Sessions
12. Activity Logging (admin_helpers.php)
13. Deleting Feedback (delete_feedback.php)
14. Clearing the Survey (clear_survey.php)
15. Changing Admin Credentials (admin_change_credentials.php)
16. Reading the Activity Log (admin_activity_log.php)
17. Logging Out (admin_logout.php)
18. Toast Messages (js/script.js)
19. Quick Glossary

## **Big Picture**

The system has **two kinds of users**:

- **Tourists** – open the homepage, see the overall rating, and fill in a feedback form. No login needed.
- **Admin** – logs in and can view every feedback entry, delete fake or bad ones, see the Quick Survey answers, and check an activity log.

Behind the scenes, **PHP** runs on the server, **MySQL** stores the data, and **HTML/CSS/JavaScript** show the pages in the browser.

A simple flow:

Tourist fills feedback → PHP saves it to MySQL → Admin sees the data on the Dashboard

## **1. The Database (intramuros_db.sql)**

We have **4 tables**:

Table | What it stores
--- | ---
feedback | Each tourist review (ratings + comments)
survey | Quick Survey answers (“Was the form helpful?”)
admin_users | Admin username + hashed password
activity_log | History of admin actions (logins, deletes, etc.)

The important columns in `feedback`:

```sql
CREATE TABLE feedback (
    id            int(11) NOT NULL,
    nationality   varchar(50) NOT NULL,
    visit_date    date NOT NULL,
    cleanliness   int NOT NULL,
    restroom      int NOT NULL,
    guides        int NOT NULL,
    accommodation int NOT NULL,
    overall       int NOT NULL,
    comments      text,
    average       decimal(3,2),
    created_at    timestamp DEFAULT CURRENT_TIMESTAMP
);
```

**Why this matters:** each row is one review. The five ratings are numbers from 1 to 5. The `average` column saves the math so the homepage doesn’t have to recompute it every time.

## **2. Connecting to the Database (Config.php)**

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "intramuros_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

**In Simple:**

- `mysqli(...)` opens a connection to MySQL (using the default XAMPP settings).
- `session_start()` turns on PHP “sessions” so the server can remember a logged-in admin across pages.
- If the connection fails, the page stops and shows an error.

This file is **included** at the top of every PHP page that needs the database — so we don’t repeat the same code everywhere.

## **3. Loading Feedback for the Homepage (Index.php)**

```php
$sql = "SELECT * FROM feedback ORDER BY visit_date DESC";
$result = $conn->query($sql);
$feedbackData = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $avg = ($row['cleanliness'] + $row['restroom'] + $row['guides']
              + $row['accommodation'] + $row['overall']) / 5;
        $feedbackData[] = [
            'id'          => $row['id'],
            'nationality' => $row['nationality'],
            'visitDate'   => $row['visit_date'],
            'comments'    => $row['comments'],
            'average'     => $avg,
        ];
    }
}
```

**In Simple:** read every feedback entry, newest visit date first. For each one, add the five star ratings together and divide by 5 to get the average. The list is then handed to JavaScript so the homepage can show review cards and the star slideshow.

## **4. The Feedback Form (Index.php)**

```html
<form id="feedbackForm" onsubmit="submitFeedback(event)" novalidate>
    <select id="nationality" name="nationality">
        <option value="" disabled selected>Select an option</option>
        <option value="Local">Local</option>
        <option value="Foreign">Foreign</option>
    </select>

    <input type="date" id="visitDate" name="visitDate"
           min="<?php echo date('Y-m-d'); ?>"
           max="<?php echo date('Y-m-d'); ?>">

    <!-- 5 sets of star radios: cleanliness, restroom, guides, accommodation, overall -->
    <input type="radio" name="cleanliness" value="5"> ...
    <input type="radio" name="overall"     value="5"> ...

    <textarea id="comments" name="comments"></textarea>
    <button type="submit">Submit Feedback</button>
</form>
```

The `name` attribute on each input is the **key** PHP uses to read the value later (`$_POST['nationality']`, `$_POST['visitDate']`, and so on).

When the user clicks Submit, the `onsubmit="submitFeedback(event)"` handler runs — that’s our JavaScript function that checks everything before sending it.

## **5. Visit Date Validation (Index.php + js/script.js)**

This is the rule we want enforced:

> The visit date must be **today**. Not yesterday, not tomorrow.

### Part A — Block bad dates in the picker (Index.php)

```html
<input type="date" id="visitDate" name="visitDate"
       min="<?php echo date('Y-m-d'); ?>"
       max="<?php echo date('Y-m-d'); ?>">
```

`date('Y-m-d')` prints today’s date in `YYYY-MM-DD` form. Both `min` and `max` are set to today, so the browser’s built-in calendar only lets the user click **today’s date**. Past and future days appear greyed out.

### Part B — Double-check before sending (js/script.js)

```js
const visitDate = document.getElementById('visitDate').value;

if (!visitDate) {
    showToast('Please choose your visit date.', 'error');
    document.getElementById('visitDate').focus();
    return;
}

const today = new Date();
today.setHours(0, 0, 0, 0);
const selectedDate = new Date(visitDate + 'T00:00:00');

if (selectedDate < today) {
    showToast('Visit date cannot be in the past.', 'error');
    document.getElementById('visitDate').focus();
    return;
}
if (selectedDate > today) {
    showToast('Visit date cannot be in the future.', 'error');
    document.getElementById('visitDate').focus();
    return;
}
```

**In Simple — step by step:**

1. Read the date the user picked.
2. If the box is empty → red toast _“Please choose your visit date.”_ and stop.
3. Make a `today` value with the time chopped off, so we compare day-to-day only (not hours and minutes).
4. Turn the picked date into a real date object too.
5. If it is **earlier than today** → red toast _“Visit date cannot be in the past.”_
6. If it is **later than today** → red toast _“Visit date cannot be in the future.”_
7. Only if it equals today does the form keep going.

**Why both HTML and JavaScript?** The HTML `min/max` blocks the calendar UI, but a clever user could still type a date by hand or change it with developer tools. The JavaScript check is the safety net that catches anything the picker missed.

## **6. Saving New Feedback (Index.php POST handler)**

When the form is submitted, the same `Index.php` file handles it.

```php
if ($_SERVER["REQUEST_METHOD"] == "POST"
    && !isset($_POST['clear_survey'])
    && !isset($_POST['admin_login'])) {

    $nationality  = $_POST['nationality'] ?? '';
    $visit_date   = $_POST['visitDate']   ?? '';
    $comments     = $_POST['comments']    ?? '';
    $cleanliness  = $_POST['cleanliness'] ?? null;
    $restroom     = $_POST['restroom']    ?? null;
    $guides       = $_POST['guides']      ?? null;
    $accommodation= $_POST['accommodation'] ?? null;
    $overall      = $_POST['overall']     ?? null;

    if ($cleanliness !== null && $restroom !== null && $guides !== null
        && $accommodation !== null && $overall !== null) {

        $average = ($cleanliness + $restroom + $guides + $accommodation + $overall) / 5;

        $sql = "INSERT INTO feedback
                (nationality, visit_date, cleanliness, restroom, guides,
                 accommodation, overall, comments, average)
                VALUES ('$nationality', '$visit_date', $cleanliness, $restroom,
                        $guides, $accommodation, $overall, '$comments', $average)";

        if ($conn->query($sql) === TRUE) {
            echo "success";
            exit();
        }
    }
}
```

**In Simple:**

1. Read every form value out of `$_POST`.
2. Make sure all five star ratings are filled in.
3. Compute the average and save the whole row into the `feedback` table.
4. If it works, print `success`. The JavaScript watches for this exact word to know everything went well.

## **7. Sending the Form with JavaScript (js/script.js)**

```js
function submitFeedback(event) {
    event.preventDefault();

    const nationality = document.getElementById('nationality').value;
    const visitDate   = document.getElementById('visitDate').value;
    const cleanliness = document.querySelector('input[name="cleanliness"]:checked');
    // ...the other ratings...

    if (!nationality) { showToast('Please select your nationality.', 'error'); return; }
    // visit date checks (see section 5)
    if (!cleanliness) { showToast('Please rate the cleanliness.', 'error'); return; }
    // ...

    const formData = new FormData();
    formData.append('nationality', nationality);
    formData.append('visitDate',   visitDate);
    formData.append('cleanliness', cleanliness.value);
    // ...

    fetch('Index.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                showToast('Feedback submitted successfully!', 'success');
                document.getElementById('feedbackForm').reset();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error submitting feedback: ' + data, 'error');
            }
        });
}
```

**In Simple:**

1. Stop the page from reloading the normal way (`preventDefault`).
2. Check each field one by one. If anything is missing, show a red toast and stop.
3. Pack everything into a `FormData` bundle and `fetch()` sends it to `Index.php`.
4. If the server replies with `success`, show a green toast, clear the form, and reload the page so the new review appears.

## **8. The Quick Survey (save_survey.php)**

A small floating popup asks the tourist if the form was easy to use. The yes/no answer is saved here.

```php
<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $helpful           = $conn->real_escape_string($_POST['helpful'] ?? '');
    $surveySuggestions = $conn->real_escape_string($_POST['surveySuggestions'] ?? '');

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
```

**In Simple:** take the “yes” or “no” answer and the optional suggestion, then add a new row to the `survey` table. `real_escape_string()` makes the values safe to put inside the SQL string so users can’t break the query with quotes.

## **9. Admin Login (admin_login.php)**

```php
$stmt = $conn->prepare("SELECT id, username, password_hash
                        FROM admin_users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_id']       = $admin['id'];
        log_activity($conn, $admin['username'], 'login_success', 'Admin signed in.');
        $response = ['status' => 'success', 'username' => $admin['username']];
    } else {
        log_activity($conn, $username, 'login_failed', 'Wrong password.');
        $response['message'] = 'Invalid username or password.';
    }
} else {
    log_activity($conn, $username, 'login_failed', 'Unknown username.');
    $response['message'] = 'Invalid username or password.';
}
```

**In Simple:**

1. Look up the admin by username.
2. `password_verify()` checks the typed password against the scrambled (hashed) password in the database. We **never** store the plain password — even if someone steals the database, they can’t read the real ones.
3. If the password matches, save the admin’s username and id in `$_SESSION` so other pages know they’re logged in. Then log the success.
4. Wrong username or wrong password → log the failed attempt and send back an error message.

**Why use `prepare()` + `bind_param()`?** This is called a **prepared statement**. It protects against **SQL injection** — a trick where someone types `'; DROP TABLE admin_users; --` into the form to break the database. Prepared statements treat the typed value as data, not as code, so the attack fails.

## **10. Protecting Admin Actions with Sessions**

The admin’s username is saved into `$_SESSION` once they log in. Other admin pages read it back from the session, like this in `admin_logout.php`:

```php
$username = $_SESSION['admin_username'] ?? '';
```

A **session** is the server’s short-term memory about one user. If `$_SESSION['admin_username']` is empty, the visitor is not logged in. This is how the system tells a real admin apart from a random visitor.

## **11. Activity Logging (admin_helpers.php)**

```php
function log_activity($conn, $username, $action, $details = '') {
    if (!$conn) return;
    $stmt = $conn->prepare("INSERT INTO activity_log (username, action, details)
                            VALUES (?, ?, ?)");
    if ($stmt) {
        $u = $username !== '' ? $username : null;
        $stmt->bind_param('sss', $u, $action, $details);
        $stmt->execute();
        $stmt->close();
    }
}

function actor_from_request() {
    $actor = $_POST['actor'] ?? $_GET['actor'] ?? '';
    return is_string($actor) ? trim($actor) : '';
}
```

**In Simple:** a reusable helper. Every time an admin does something important (login, delete, clear survey, change profile), we call `log_activity(...)` so a row is added to the `activity_log` table. Later the admin can see a full history of what happened in the system.

`actor_from_request()` is a tiny helper to grab the admin’s username from the request so we know **who** did the action.

## **12. Deleting Feedback (delete_feedback.php)**

```php
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            log_activity($conn, $actor, 'feedback_deleted', 'Deleted feedback id=' . $id);
            echo json_encode(["status" => "success"]);
        }
    }
}
```

**In Simple:** the page expects an ID in the URL (for example `delete_feedback.php?id=12`). `intval()` makes sure it’s a real number. The row is removed and the action is written to the activity log. The page sends back JSON so the JavaScript can update the table without a full reload.

## **13. Clearing the Survey (clear_survey.php)**

```php
$count_result = $conn->query("SELECT COUNT(*) AS c FROM survey");
// ...read the count...

$sql = "TRUNCATE TABLE survey";

if ($conn->query($sql) === TRUE) {
    log_activity($conn, $actor, 'survey_cleared', "Cleared $count survey response(s).");
    echo json_encode(['status' => 'success']);
}
```

**In Simple:** count how many survey answers there are (so we can record it), then `TRUNCATE TABLE` empties the whole `survey` table at once. The deletion is then logged.

## **14. Changing Admin Credentials (admin_change_credentials.php)**

```php
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

$update = $conn->prepare("UPDATE admin_users SET username = ?, password_hash = ?
                          WHERE id = ?");
$update->bind_param('ssi', $final_username, $final_hash, $admin['id']);
```

**In Simple:**

1. Always ask for the **old password** and check it first. No one should be able to change credentials without proving they own the account.
2. If the admin only wants to change one thing (username OR password), keep the old value for the other.
3. `password_hash()` scrambles the new password before saving it.
4. A duplicate-username check stops two admins from having the same name.
5. Every successful or failed attempt is written to the activity log.

## **15. Reading the Activity Log (admin_activity_log.php)**

```php
$limit = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 100;

$sql = "SELECT id, username, action, details, created_at
        FROM activity_log
        ORDER BY created_at DESC, id DESC
        LIMIT $limit";
$result = $conn->query($sql);
```

**In Simple:** read the latest entries from the `activity_log` table (newest first). The `$limit` is clamped between 1 and 500 so the page can’t be asked for too many rows. The result is sent back as JSON for the admin dashboard to display.

## **16. Logging Out (admin_logout.php)**

```php
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
```

**In Simple:** empty the session, expire the session cookie, then destroy the session. The admin is now logged out and trying to open any admin action will be rejected. The logout itself is recorded in the activity log.

## **17. Toast Messages (js/script.js)**

Throughout the app, when something good or bad happens we call `showToast`:

```js
showToast('Feedback submitted successfully!', 'success');
showToast('Visit date cannot be in the past.', 'error');
```

**In Simple:** pops a small message in the corner of the screen. Green for `success`, red for `error`. The user sees what happened without an alert box blocking the page.

## **Quick Glossary**

Term | Meaning
--- | ---
**PHP** | Server-side language that runs *before* the page is sent to the browser.
**MySQL** | The database — where all the feedback, surveys, admins, and logs are stored.
**Session** | Temporary memory the server keeps about a logged-in admin.
**$_POST / $_GET** | How PHP reads form data sent by the browser.
**$_SESSION** | How PHP remembers a user across pages.
**Prepared statement** | Safer way to run SQL queries — prevents SQL injection.
**password_hash() / password_verify()** | Built-in PHP functions to safely store and check passwords.
**real_escape_string()** | Cleans a value before putting it inside an SQL string to stop quotes from breaking the query.
**htmlspecialchars()** | Cleans output so user-typed code can’t run on the page (prevents XSS).
**JSON** | A simple text format used to send data back from PHP to JavaScript.
**fetch()** | Modern JavaScript function for sending data to the server without reloading the page.
**Toast** | A small pop-up message at the corner of the screen, green for success and red for error.
**Visit Date rule** | Must equal today. Past dates and future dates are rejected — both by the date picker (`min`/`max`) and by JavaScript (toast errors).
