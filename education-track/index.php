<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Welcome';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<section class="hero">
    <h1>Empowering Learning, Simplifying Education</h1>
    <p>Education Track is an all-in-one Student Management System — manage enrollment, attendance, results, assignments and timetables in one clean, modern dashboard.</p>
    <div class="hero-actions">
        <a href="register.php" class="btn btn-secondary">Get Started - Register</a>
        <a href="login.php" class="btn" style="background:rgba(255,255,255,.15); color:#fff; border:1px solid #fff;">Student Login</a>
    </div>
</section>

<section class="features">
    <div class="feature-card">
        <div class="icon">📚</div>
        <h3>Course Enrollment</h3>
        <p>Browse and enroll in courses with ease, all managed from your student dashboard.</p>
    </div>
    <div class="feature-card">
        <div class="icon">📅</div>
        <h3>Attendance Tracking</h3>
        <p>Stay on top of your attendance record for every course, in real time.</p>
    </div>
    <div class="feature-card">
        <div class="icon">📊</div>
        <h3>Marks & Results</h3>
        <p>View grades and performance analytics with clean visual charts.</p>
    </div>
    <div class="feature-card">
        <div class="icon">📝</div>
        <h3>Assignments</h3>
        <p>Submit assignments online and track due dates without missing a deadline.</p>
    </div>
    <div class="feature-card">
        <div class="icon">🗓️</div>
        <h3>Timetable</h3>
        <p>A clear weekly timetable so you always know where to be.</p>
    </div>
    <div class="feature-card">
        <div class="icon">🔔</div>
        <h3>Notifications</h3>
        <p>Never miss important announcements from your institution.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
