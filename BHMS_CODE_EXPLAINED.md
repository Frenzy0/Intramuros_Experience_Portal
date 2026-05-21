# **BHMS - Code Explained**

A walkthrough of how the **Barangay Health Management System** works, Each section shows
a small piece of code and tells what it does and _why_ it’s there.

## **Table of Contents**

1. Big Picture
2. The Database (bhms_db.sql)
3. Connecting to the Database (db.php)
4. Creating the First Admin (setup_admin.php)
5. Protecting Admin Pages (auth.php)
6. Resident Survey Form (index.php)
7. Saving the Survey (handlers/submit_survey.php)
8. Admin Login Page (login.php)
9. Checking the Login (handlers/login.php)
10. Activity Logging (helpers/log.php)
11. The Dashboard (dashboard.php)
12. Managing Residents (residents.php)
13. Adding/Deleting Residents (handlers)
14. Health Notes (notes.php)
15. Logging Out (logout.php)
16. Quick Glossary

## **Big Picture**

The system has **two kinds of users** :

 - **Residents**  - fill out a health survey on the homepage. No login needed.

 - **Admin**  - logs in and can view stats, add/edit/delete residents, and check health notes.

Behind the scenes, **PHP** runs on the server, **MySQL** stores the data, and **HTML/CSS/JavaScript**
show the pages in the browser.

A simple flow:


Resident fills survey → PHP saves it to MySQL → Admin sees the data on the Dashboard


## **1. The Database (bhms_db.sql)**

We have **4 tables** :


Table What it stores

admins Admin username + hashed password

admin_logs History of what the admin did (login, edit, etc.)

residents Personal info of each resident

survey_responses Each health survey submitted


The important link: every survey_responses row has a resident_id that connects it to one row
in residents.


**CREATE** **TABLE** `survey_responses` (
`id` int(11) **NOT** **NULL**,
`resident_id` int(11) **NOT** **NULL**,
`vaccination_status` enum('Vaccinated','Unvaccinated','Partially Vaccinated') **NOT**
**NULL**,
`has_fever` tinyint(1) **DEFAULT** 0,
...
);


**Why this matters:** one resident can submit many surveys over time. We use the resident_id
to know which surveys belong to whom.


ON DELETE CASCADE means: if a resident is deleted, all their surveys are automatically
deleted too — no leftover data.

## **2. Connecting to the Database (db.php)**

**<?php**
$conn = **new** mysqli('localhost', 'root', '', 'bhms_db');
**if** ($conn->connect_error) {
**die** (json_encode(['success' => **false**, 'error' => 'DB connection failed: ' . $conn>connect_error]));
}
$conn->set_charset('utf8mb4');


**In Simple:** - mysqli(...) opens a connection to MySQL (using the default XAMPP settings). - If
something goes wrong, the page stops and shows an error. - utf8mb4 lets the database store
any character, including emojis or special letters.

This file is **included** at the top of every page that needs the database — so we don’t repeat the
same code everywhere.


## **3. Creating the First Admin (setup_admin.php)**

This page is run **only once** to make the first admin account.


$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);


**Important:** we never store the plain password. password_hash() turns it into a long,
scrambled string that _can’t be reversed_ . Later, when the user logs in, we use password_verify()
to check if the password they typed matches that scrambled version.

**Why:** even if someone steals the database, they still can’t read the real passwords.

## **4. Protecting Admin Pages (auth.php)**

**<?php**
**if** (session_status() === PHP_SESSION_NONE) {
session_start();
}
**if** ( **empty** ($_SESSION['admin_id'])) {
header('Location: login.php');
**exit** ;
}


**In Simple:** - A **session** is a way for PHP to remember a user across pages (like a wristband at
an event). - If the session doesn’t have admin_id, the visitor isn’t logged in → send them back to
the login page.


Every admin page starts with require 'auth.php'; so random people can’t open the dashboard
by typing the URL.

## **5. Resident Survey Form (index.php)**

This is the homepage that any resident can use.


< **form** id="surveyForm" method="POST" action="" novalidate>
< **input** type="text" name="full_name" placeholder="Juan Dela Cruz">
< **input** type="date" name="birthdate" id="surveyBirthdate">
< **input** type="number" name="age" id="surveyAge" min="0" max="120">
...
</ **form** 

A regular HTML form. The name attribute on each input is the **key** PHP uses to read the value
later ($_POST['full_name'], etc.).

A small JavaScript trick **auto-fills the age** when a birthday is picked:


bd.addEventListener('change', () **=>** {
**const** d = **new** Date(bd.value);
**const** today = **new** Date();
**let** a = today.getFullYear() - d.getFullYear();
**const** m = today.getMonth() - d.getMonth();
**if** (m < 0 || (m === 0 && today.getDate() < d.getDate())) a--;
**if** (a >= 0 && a <= 120) age.value = a;
});


**In Simple:** subtract the birth year from this year, then take 1 off if the birthday hasn’t
happened yet this year. That’s how we get the correct age.

## **6. Saving the Survey (handlers/submit_survey.php)**

This is the most important “back-end” file. When the resident clicks **Submit**, the data is sent
here.

**Step 1 — Read the form values:**


$name = trim($_POST['full_name'] ?? '');
$age = (int)($_POST['age'] ?? 0);
$symptoms = $_POST['symptoms'] ?? [];
$has_fever = in_array('fever', $symptoms) ? 1 : 0;


trim() removes accidental spaces. (int) makes sure the age is a real number. We check the
symptom checkboxes — if “fever” was ticked, save 1; otherwise 0.

**Step 2 — Validate (check the data is correct):**


**if** (!$name || !preg_match('/^[A-Za-z\s.\-\']+$/', $name)) {
**echo** json_encode(['success' => **false**, 'error' => 'Full name may only contain letters...']);
**exit** ;
}


We reject names with numbers or strange symbols, ages over 120, birthdays in the future, etc.
**Never trust what the user typed** - always check.

**Step 3 — Insert or update the resident:**


$lookup = $conn->prepare(
"SELECT id FROM residents
WHERE LOWER(full_name) = LOWER(?)


AND COALESCE(LOWER(suffix), '') = COALESCE(LOWER(?), '')
LIMIT 1"
);


We look for a resident with the same name. If found → update them. If not → create a new
resident record.

**Step 4 — Save the survey:**


$stmt = $conn->prepare(
"INSERT INTO survey_responses
(resident_id, vaccination_status, last_checkup, has_fever, ...)
VALUES (?, ?, ?, ?, ...)"
);
$stmt->bind_param('issiiiiis', $resident_id, $vaccination, ...);


**Why use prepare() + bind_param() instead of plain SQL?** This is called a **prepared**
**statement** . It protects against **SQL injection** - a famous trick where a hacker types
something like '; DROP TABLE residents; -- into a form to mess up the database. Prepared
statements treat user input as _data_, not _code_, so the attack fails.


Finally, the page sends back a JSON response like {"success": true} and JavaScript shows a
“Survey submitted!” message.

## **7. Admin Login Page (login.php)**


A normal HTML form pointing to handlers/login.php:


< **form** method="POST" action="handlers/login.php" class="login-form">
< **input** type="text" name="username" required>
< **input** type="password" name="password" required>
< **button** type="submit">Sign In</ **button**  </ **form** 

It also has a small “eye” button to show/hide the password — just for user convenience.

## **8. Checking the Login (handlers/login.php)**

$stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE
username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();


**if** (!$admin || !password_verify($password, $admin['password_hash'])) {


$_SESSION['login_error'] = 'Invalid username or password.';
header('Location: ../login.php');
**exit** ;
}


$_SESSION['admin_id']    = $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
logAction($conn, 'Logged in');
header('Location: ../dashboard.php');


**In Simple:** 1. Look up the admin by username. 2. If not found, or the password doesn’t match
the saved hash → show error. 3. If it matches → save the admin’s id in the session (so other
pages know they’re logged in) and send them to the dashboard. 4. Record the login in the
activity log.


password_verify() is the secure way to compare a typed password against the hashed one in
the database.

## **9. Activity Logging (helpers/log.php)**

**function** logAction($conn, $action, $target = **null**, $details = **null** ) {
**if** ( **empty** ($_SESSION['admin_id'])) **return** ;
$stmt = $conn->prepare(
"INSERT INTO admin_logs (admin_id, action, target, details) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('isss', $admin_id, $action, $target, $details);
$stmt->execute();
}


A reusable helper. Every time the admin does something important (login, add, edit, delete) we
call this function so it gets saved in the admin_logs table. This way the admin can review their
own activity later.

## **10. The Dashboard (dashboard.php)**

This page has **two big jobs** : show summary numbers (cards) and show a table of all residents.

**Counting residents:**


$total = $conn->query("SELECT COUNT(*) FROM residents")->fetch_row()[0];
$male = $conn->query("SELECT COUNT(*) FROM residents WHERE gender='Male'")>fetch_row()[0];
$vaccinated = $conn->query("SELECT COUNT(DISTINCT resident_id) FROM
survey_responses WHERE vaccination_status='Vaccinated'")->fetch_row()[0];


COUNT(*) returns how many rows there are. DISTINCT resident_id makes sure we count each
resident only once, even if they submitted multiple surveys.

**Getting each resident’s latest survey:**


**SELECT** r.full_name, sr.vaccination_status, sr.has_fever, ...
**FROM** residents r
**LEFT** **JOIN** survey_responses sr **ON** sr. **id** = (
**SELECT** **id** **FROM** survey_responses
**WHERE** resident_id = r. **id**
**ORDER** **BY** submitted_at **DESC** **LIMIT** 1
)


**In Simple:** for each resident, attach their **most recent** survey. LEFT JOIN means: include the
resident even if they don’t have a survey yet (the survey fields will just be empty).

**Showing the data with a loop:**


**<?php** **foreach** ($rows **as** $row): **?>**
<tr>
<td><?= htmlspecialchars($row['full_name']) **?>** </td>
<td><?= htmlspecialchars($row['gender']) **?>** </td>
...
</tr>
<?php **endforeach** ; **?>**


htmlspecialchars() is a safety function — it makes sure that if someone types <script> in their
name, it shows as plain text instead of running as code. This protects against **XSS attacks**
(Cross-Site Scripting).


**The filter cards** (Total, Male, Vaccinated, etc.) use a data-filter attribute, and JavaScript reads
that to filter the table when a card is clicked.

## **11. Managing Residents (residents.php)**

Very similar to the dashboard, but each row has **Edit** and **Delete** buttons:


< **button** class="edit-btn editResidentBtn">
< **span** class="material-icons">edit_note</ **span**  - Edit
</ **button** < **button** class="delete-btn deleteResidentBtn">
< **span** class="material-icons">delete_outline</ **span**  </ **button** 

It also includes **modals** (popup boxes) for Add, Edit, and Delete confirmation. The JavaScript
opens these modals, fills them with the resident’s current data, and then sends the form to the
right handler file.

## **12. Adding/Deleting Residents (handlers)**

**Add (handlers/add_resident.php):**


$stmt = $conn->prepare(
"INSERT INTO residents (full_name, suffix, birthdate, age, civil_status, gender, purok)
VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssisss', $name, $suffix_db, $bd, $age, $status, $gender, $purok);
**if** ($stmt->execute()) {
logAction($conn, 'Added resident', $name);
**echo** json_encode(['success' => **true** ]);
}


Validate input → insert → log it → respond with JSON.

**Delete (handlers/delete_resident.php):**


$stmt = $conn->prepare("DELETE FROM residents WHERE id = ?");
$stmt->bind_param('i', $id);
**if** ($stmt->execute()) {
logAction($conn, 'Deleted resident', $name);
}


Because of the ON DELETE CASCADE rule in the SQL, deleting one resident automatically
deletes all of their surveys too.


All handlers return **JSON** ({"success": true} or {"success": false, "error": "..."}). The JavaScript
reads that and updates the page without a full reload — this is what makes the app feel quick.

## **13. Health Notes (notes.php)**

Shows each resident as a **card** with their latest survey: vaccination status, last checkup,
symptoms, and the notes they typed.


$res = $conn->query("
SELECT r.full_name, sr.vaccination_status, sr.health_notes, sr.submitted_at, ...
FROM residents r
INNER JOIN survey_responses sr ON sr.id = (
SELECT id FROM survey_responses


WHERE resident_id = r.id
ORDER BY submitted_at DESC LIMIT 1
)
");


Notice this uses INNER JOIN instead of LEFT JOIN: residents who never submitted a survey
**won’t** appear here, because there are no notes to show.

The PHP loop then builds one card per resident, showing symptom badges and the typed notes.

## **14. Logging Out (logout.php)**

**<?php**
session_start();
$_SESSION = [];
session_destroy();
header('Location: index.php');
**exit** ;


**In Simple:** empty the session, destroy it, and send the user back to the homepage. Now they’re
logged out, and trying to open the dashboard will redirect them to the login page.

## **Quick Glossary**

Term Meaning

**PHP** Server-side language that runs _before_ the page is sent
to the browser.
**MySQL** The database — where all the data is stored.
**Session** Temporary memory the server keeps about a logged-in
user.

**$_POST / $_GET** How PHP reads form data sent by the browser.

**$_SESSION** How PHP remembers a user across pages.

**Prepared statement** Safer way to run SQL queries — prevents SQL injection.



**password_hash() /**
**password_verify()**



Built-in PHP functions to safely store and check
passwords.



**htmlspecialchars()** Cleans output so user-typed code can’t run on the page
(prevents XSS).
**JSON** A simple text format used to send data back from
handlers to JavaScript.
**JOIN** A SQL way to combine data from two tables (residents
+ their surveys).
**CASCADE** Auto-delete related rows. Delete a resident → their


Term Meaning
surveys also vanish.


