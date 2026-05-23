# Local Tourist Feedback System (LTFF)

A simple web based where tourists who visit **Intramuros** can share their experience by giving star ratings and comments. Admins can then view, manage, and clean up the feedback through a private dashboard.

---

## What Is the Local Tourist Feedback System About?

The Local Tourist Feedback System (also called the **Intramuros Tourist Feedback** portal) is a website made for tourists and the people who manage the tourist site.

- **Tourists** can rate their visit (cleanliness, restrooms, tour guides, hotels, and overall experience) and leave comments.
- **Future visitors** can read past reviews and see the overall rating before they go.
- **Admins** can log in to see all feedback, delete bad or fake entries, view a short survey about the form itself, and check an activity log of what happened in the system.

Think of it as a small "tourist review wall" for Intramuros, plus a private control panel for the staff.

---

## Table of Contents
1. [Features](#1-features)
2. [Tech Stack](#2-tech-stack)
3. [Project Structure](#3-project-structure)
4. [Database Schema](#4-database-schema)
5. [Installation / Setup](#5-installation--setup)
6. [How It Works](#6-how-it-works)
7. [User Roles & Pages](#7-user-roles--pages)

---

## 1. Features

**For Tourists (Public Side):**
- Welcome page that shows the overall star rating from past visitors.
- A sliding panel with past visitor comments.
- A feedback form with 5 star ratings (Cleanliness, Restroom, Tour Guides, Accommodation, Overall) plus a comment box.
- A "View Visitor Reviews" page that lists all past ratings.
- A small floating **Quick Survey** that asks if the form was easy to use.
- **Dark Mode** toggle.

**For Admins (Private Side):**
- Login page with username and password.
- Dashboard showing every feedback entry.
- Delete any feedback entry.
- View Quick Survey answers and clear them.
- Edit profile (change username and/or password).
- Activity Log that records logins, logouts, deletions, and failed login attempts.
- Logout button.

---

## 2. Tech Stack

| Part | What's Used |
|---|---|
| Front-end | HTML, CSS, JavaScript |
| Icons | Google Material Icons |
| Back-end | PHP |
| Database | MySQL / MariaDB (via phpMyAdmin) |
| Server | Apache (XAMPP) |

No frameworks, no build tools — just plain PHP + JS + CSS.

---

## 3. Project Structure

```
LTFF/
├── css/
│   └── styles.css              # All styles (light & dark mode)
├── js/
│   └── script.js               # Page switching, form submit, dark mode
│
├── Index.php                   # Main page (homepage, form, table, admin views)
├── Config.php                  # Database connection settings
│
├── Save_Feedback.php           # Saves a new tourist feedback
├── Fetch_Feedback.php          # Loads feedback for the tables
├── delete_feedback.php         # Deletes a feedback entry (admin only)
│
├── save_survey.php             # Saves a Quick Survey answer
├── clear_survey.php            # Clears all survey answers (admin only)
│
├── admin_login.php             # Handles admin login (case-sensitive)
├── admin_logout.php            # Handles admin logout
├── admin_change_credentials.php# Lets admin change username/password
├── admin_activity_log.php      # Sends activity log entries to the dashboard
├── admin_helpers.php           # Shared admin helper functions
├── setup_admin.php             # One-shot tool to create/reset an admin account
│
├── intramuros_db.sql           # Full database backup (all 4 tables)
│
└── localhost_LTFF_.png         # Screenshot of the app
```

---

## 4. Database Schema

The database is called **`intramuros_db`** and has **4 tables**.

### `feedback` — tourist reviews
| Column | Type | Meaning |
|---|---|---|
| id | int (auto) | Unique ID |
| nationality | varchar(50) | "Local" or "Foreign" |
| visit_date | date | When the tourist visited |
| cleanliness | int | Star rating 1–5 |
| restroom | int | Star rating 1–5 |
| guides | int | Star rating 1–5 |
| accommodation | int | Star rating 1–5 |
| overall | int | Star rating 1–5 |
| comments | text | Optional written comment |
| average | decimal(3,2) | Average of the 5 ratings |
| created_at | timestamp | When the entry was saved |

### `survey` — quick form feedback
| Column | Type | Meaning |
|---|---|---|
| id | int (auto) | Unique ID |
| helpful | varchar(3) | "yes" or "no" |
| survey_suggestions | text | Optional suggestion |
| created_at | timestamp | When the answer was saved |

### `admin_users` — admin accounts
| Column | Type | Meaning |
|---|---|---|
| id | int (auto) | Unique ID |
| username | varchar(50) | Admin username (unique) |
| password_hash | varchar(255) | Hashed password (safe, not plain text) |
| created_at | timestamp | When the account was made |

### `activity_log` — audit trail
| Column | Type | Meaning |
|---|---|---|
| id | int (auto) | Unique ID |
| username | varchar(50) | Who did the action |
| action | varchar(64) | What happened (e.g. `login_success`, `feedback_deleted`) |
| details | text | A short description |
| created_at | timestamp | When it happened |

---

## 5. Installation / Setup

### What you need
- **XAMPP** (or any setup with Apache + PHP + MySQL/MariaDB)
- A web browser

### Steps

1. **Install XAMPP** and start **Apache** and **MySQL** from the XAMPP Control Panel.

2. **Copy the project** to your XAMPP web folder:
   ```
   C:\xampp\htdocs\LTFF
   ```

3. **Create the database:**
   - Open your browser and go to `http://localhost/phpmyadmin`
   - Click **New** and create a database named `intramuros_db`
   - Click the **Import** tab and upload `intramuros_db.sql`
   - Press **Go**

4. **Check the database settings** in `Config.php`:
   ```php
   $servername = "localhost";
   $username   = "root";
   $password   = "";
   $dbname     = "intramuros_db";
   ```
   (The defaults above work for a normal XAMPP install.)

5. **Open the site:**
   ```
   http://localhost/LTFF/Index.php
   ```

6. **Default admin login (case-sensitive):**
   - Username: `admin`
   - Password: `password123` (auto-seeded the first time the `admin_users` table is empty)

7. **Need to create or reset an admin account?**
   Open `http://localhost/LTFF/setup_admin.php` in your browser, fill in a username and a strong password (12+ chars with uppercase, lowercase, number, and special character), then **delete `setup_admin.php`** from the server using the red button shown after saving. Leaving it on the server is a security risk.

---

## 6. How It Works

### Tourist flow
1. Tourist opens the homepage and sees the average rating + past comments.
2. They click **Leave Feedback**, fill in nationality, visit date, 5 star ratings, and an optional comment.
3. They press **Submit** — the data is sent to `Save_Feedback.php`, which inserts it into the `feedback` table.
4. The new review now shows up in the public review list and counts toward the overall rating.
5. A small **Quick Survey** pops up asking if the form was easy. The answer is saved by `save_survey.php`.

### Admin flow
1. Admin clicks the **Admin** button at the top and enters their username and password (the username is **case-sensitive** — `Admin` and `admin` are different).
2. The login form first checks that the password meets the strong-password rule (12+ chars, uppercase, lowercase, number, special) before sending it. `admin_login.php` then checks the password against the hashed one in `admin_users` and starts a session.
3. The admin sees the **Admin Dashboard** with:
   - All feedback entries (with Delete buttons)
   - All Quick Survey answers (with a Clear All button)
   - The Activity Log
4. Every important action (login, logout, deletion, failed login) is recorded in the `activity_log` table by `admin_helpers.php`.
5. Admin can also click **Edit Profile** to change their username or password (handled by `admin_change_credentials.php`).
6. Logging out (`admin_logout.php`) ends the session.

### Star rating math
The overall average per feedback = (cleanliness + restroom + guides + accommodation + overall) ÷ 5.
The site-wide rating shown on the homepage = the **average of the `overall` column** across all feedback entries.

---

## 7. User Roles & Pages

There are only **2 roles** in this system.

### Visitor (no login needed)
| Page / View | What they can do |
|---|---|
| Homepage (planner) | See overall rating, browse past comments |
| Feedback Form | Submit a rating and comment |
| Visitor Reviews Table | See all past reviews (paginated) |
| Quick Survey popup | Say if the form was helpful |
| Dark Mode toggle | Switch between light and dark theme |

### Admin (requires login)
| Page / View | What they can do |
|---|---|
| Admin Login | Sign in with username + password |
| Admin Dashboard – Feedback Table | View and **delete** feedback entries |
| Admin Dashboard – Survey Table | View Quick Survey answers and clear them all |
| Admin Dashboard – Activity Log | See history of admin actions and login attempts |
| Edit Profile modal | Change own username and/or password |
| Logout | End the admin session |

---

**Team:** DIT 2-4 | TEAM 9
Thank you for visiting our portal!
"# Intramuros_Experience_Portal" 
