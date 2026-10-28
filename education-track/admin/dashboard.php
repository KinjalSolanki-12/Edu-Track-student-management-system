<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

$totalStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalTeachers = (int) $pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn();
$totalCourses  = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
$totalAssignments = (int) $pdo->query('SELECT COUNT(*) FROM assignments')->fetchColumn();

// Attendance today
$attToday = $pdo->query("SELECT status, COUNT(*) as cnt FROM attendance WHERE attendance_date = CURDATE() GROUP BY status")->fetchAll();
$attTodaySummary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
foreach ($attToday as $row) $attTodaySummary[$row['status']] = (int) $row['cnt'];

// Enrollment count per course (top 6)
$courseEnroll = $pdo->query("SELECT c.course_code, COUNT(e.id) as cnt FROM courses c
    LEFT JOIN enrollments e ON e.course_id = c.id
    GROUP BY c.id, c.course_code ORDER BY cnt DESC LIMIT 6")->fetchAll();
$courseLabels = array_column($courseEnroll, 'course_code');
$courseValues = array_map('intval', array_column($courseEnroll, 'cnt'));

// Recent students
$recentStudents = $pdo->query('SELECT * FROM students ORDER BY created_at DESC LIMIT 5')->fetchAll();

$flash = getFlash();
$pageTitle = 'Admin Dashboard';
$extraCss = ['admin.css', 'dashboard.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'dashboard'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>

        <div class="welcome-banner">
            <h2>Welcome, <?= sanitize(currentAdminName()) ?> <span class="admin-badge">ADMIN</span></h2>
            <p>Here's what's happening across Education Track today.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Total Students</div></div>
            </div>
            <div class="stat-card" style="border-left-color:#0ea5e9;">
                <div class="stat-icon">🧑‍🏫</div>
                <div><div class="stat-value"><?= $totalTeachers ?></div><div class="stat-label">Total Teachers</div></div>
            </div>
            <div class="stat-card" style="border-left-color:#16a34a;">
                <div class="stat-icon">📚</div>
                <div><div class="stat-value"><?= $totalCourses ?></div><div class="stat-label">Active Courses</div></div>
            </div>
            <div class="stat-card" style="border-left-color:#ffb020;">
                <div class="stat-icon">📝</div>
                <div><div class="stat-value"><?= $totalAssignments ?></div><div class="stat-label">Assignments Posted</div></div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card">
                <div class="card-title">Enrollment by Course</div>
                <?php if (empty($courseEnroll)): ?>
                    <div class="empty-state"><div class="icon">📚</div>No enrollment data yet.</div>
                <?php else: ?>
                    <canvas id="enrollChart" width="500" height="260"></canvas>
                <?php endif; ?>
            </div>
            <div class="card">
                <div class="card-title">Today's Attendance</div>
                <?php if (array_sum($attTodaySummary) === 0): ?>
                    <div class="empty-state"><div class="icon">📅</div>No attendance marked today.</div>
                <?php else: ?>
                    <canvas id="attTodayDoughnut" width="260" height="260" style="margin:0 auto;"></canvas>
                    <div class="d-flex gap-1 flex-wrap mt-1" style="justify-content:center;">
                        <span class="badge badge-success">Present: <?= $attTodaySummary['Present'] ?></span>
                        <span class="badge badge-danger">Absent: <?= $attTodaySummary['Absent'] ?></span>
                        <span class="badge badge-warning">Late: <?= $attTodaySummary['Late'] ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Recently Registered Students</div>
            <?php if (empty($recentStudents)): ?>
                <div class="empty-state"><div class="icon">🎓</div>No students registered yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Roll No</th><th>Name</th><th>Email</th><th>Registered</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentStudents as $s): ?>
                            <tr>
                                <td><?= sanitize($s['roll_no']) ?></td>
                                <td><?= sanitize($s['full_name']) ?></td>
                                <td><?= sanitize($s['email']) ?></td>
                                <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                                <td><span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($s['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="students.php" class="btn btn-secondary btn-sm">Manage All Students</a>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if (!empty($courseEnroll)): ?>
    drawBarChart('enrollChart', <?= json_encode($courseLabels) ?>, <?= json_encode($courseValues) ?>, { color: '#1e5eff' });
    <?php endif; ?>
    <?php if (array_sum($attTodaySummary) > 0): ?>
    drawDoughnutChart('attTodayDoughnut', <?= json_encode($attTodaySummary) ?>);
    <?php endif; ?>
});
</script>

<?php
$extraJs = ['chart.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
