# Education Track – Student Management System

A full-featured Student Management System built with **plain PHP 8+, MySQL, CSS3, and vanilla JavaScript** — no frameworks. Clean, responsive, blue-and-white education theme with separate Student and Admin panels.

## ✨ Features

### Student Panel
- Registration & secure login (bcrypt password hashing, CSRF protection)
- Dashboard with attendance %, course count, pending assignments, and charts
- Profile management (edit info + profile photo upload + change password)
- Course enrollment / unenrollment
- Attendance tracking with filters
- Marks & results with bar-chart visualization
- Assignment submission (file upload) with resubmission support
- Weekly timetable (grid view)
- Notifications feed

### Admin Panel
- Secure admin login
- Dashboard with system-wide statistics and charts
- Manage Students (full CRUD + search)
- Manage Teachers (full CRUD + search)
- Manage Courses & Subjects (full CRUD, assign instructor)
- Manage Attendance (mark per course/date, "mark all present")
- Manage Marks & Results (full CRUD, auto-grade calculation)
- Upload Assignments + grade student submissions
- Create Timetable entries
- Send Notifications (targeted: all / students / teachers)
- System Settings + admin password change

## 🛠 Tech Stack
- **Frontend:** PHP (server-rendered), CSS3, vanilla JavaScript
- **Backend:** PHP 8+ (PDO)
- **Database:** MySQL / MariaDB
- **No frameworks, no external JS libraries** — charts are drawn with a tiny custom `<canvas>` renderer (`assets/js/chart.js`)

## 📁 Folder Structure

See the project tree — it follows the modular structure with `includes/`, `config/`, `modules/`, `api/`, `assets/`, and `uploads/` directories exactly as specified, with reusable `header.php`, `navbar.php`, `sidebar.php`, and `footer.php` includes used across every page.

## 🚀 Setup Instructions

### 1. Requirements
- PHP 8.0+ with `pdo_mysql` extension enabled
- MySQL 5.7+ or MariaDB 10.3+
- A web server (Apache/Nginx) or PHP's built-in server for local testing

### 2. Database Setup
```bash
mysql -u root -p < database/education_track.sql
mysql -u root -p < database/seed_data.sql
```
This creates the `education_track` database with all tables and loads sample data.

### 3. Configure Database Connection
Edit `config/database.php` and set your credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'education_track');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Configure Base URL
Edit `config/config.php` and set `BASE_URL` to match your deployment path, e.g.:
```php
define('BASE_URL', '/education-track');   // or '' if served from domain root
```

### 5. Set Upload Directory Permissions
```bash
chmod -R 755 uploads/
```

### 6. Run
**Option A — PHP built-in server (quick local testing):**
```bash
php -S localhost:8000
```
Then visit `http://localhost:8000/`.

**Option B — Apache/XAMPP/WAMP/MAMP:**
Place the `education-track` folder inside your `htdocs`/`www` directory and visit `http://localhost/education-track/`.

## 🔑 Demo Login Credentials

| Role    | Email / Username                          | Password     |
|---------|--------------------------------------------|--------------|
| Admin   | admin@edutrack.com                          | admin123     |
| Teacher | sarah.johnson@edutrack.com (record only)    | teacher123   |
| Student | aarav.sharma@student.edutrack.com           | student123   |
| Student | isha.patel@student.edutrack.com             | student123   |
| Student | rohan.mehta@student.edutrack.com            | student123   |

> Note: Teacher accounts exist as records (assignable to courses) but do not have their own login panel in this build — course/attendance/marks management is performed by the Admin. Extending a dedicated Teacher panel would reuse the same `includes/` and `modules/` structure.

## 🔒 Security Notes
- All database queries use **prepared statements** (PDO) — no raw SQL interpolation.
- Passwords are hashed with `password_hash()` / verified with `password_verify()`.
- CSRF tokens are generated per-session and validated on every POST form.
- Uploaded files are validated by extension and size, and renamed on save to prevent path traversal / overwrite attacks.
- Sessions use `httponly` cookies and are regenerated on login to prevent fixation.
- All output is escaped with `htmlspecialchars()` via the `sanitize()` helper to prevent XSS.

## 📊 Charts
Charts (bar, line, doughnut) are rendered with a small dependency-free canvas-drawing script (`assets/js/chart.js`) — no external charting library is loaded, per the "no frameworks" requirement.

## 🧪 Tested
This build was functionally tested end-to-end (student registration, login, admin CRUD for students/teachers/courses, attendance marking, marks entry, notifications, file uploads) against a live MySQL/MariaDB + PHP 8.3 environment before delivery.

## 📌 Notes for Production
- Set `APP_DEBUG` to `false` in `config/config.php` before deploying.
- Serve over HTTPS and set the `secure` cookie flag in `includes/session.php`.
- Consider moving `uploads/` outside the web root or adding `.htaccess` rules to prevent direct PHP execution inside it.
