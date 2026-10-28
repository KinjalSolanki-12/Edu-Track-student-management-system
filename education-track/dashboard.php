<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();

// Enrolled course count
$courseCount = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = :id');
$courseCount->execute(['id' => $studentId]);
$courseCount = (int) $courseCount->fetchColumn();

// Attendance summary
$attStmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM attendance WHERE student_id = :id GROUP BY status");
$attStmt->execute(['id' => $studentId]);
$attendanceSummary = ['Present' => 0, 'Absent' => 0, 'Late' => 0];
foreach ($attStmt->fetchAll() as $row) {
    $attendanceSummary[$row['status']] = (int) $row['cnt'];
}
$totalAttendanceMarks = array_sum($attendanceSummary);
$attendancePercent = $totalAttendanceMarks > 0
    ? round((($attendanceSummary['Present']) / $totalAttendanceMarks) * 100)
    : 0;

// Pending assignments (enrolled courses, due date in future, not yet submitted)
$pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM assignments a
    JOIN enrollments e ON e.course_id = a.course_id AND e.student_id = :id
    LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = :id2
    WHERE s.id IS NULL");
$pendingStmt->execute(['id' => $studentId, 'id2' => $studentId]);
$pendingAssignments = (int) $pendingStmt->fetchColumn();

// Marks for chart (average per course)
$marksStmt = $pdo->prepare("SELECT c.course_name, AVG(m.marks_obtained) as avg_marks
    FROM marks m JOIN courses c ON c.id = m.course_id
    WHERE m.student_id = :id GROUP BY c.id, c.course_name");
$marksStmt->execute(['id' => $studentId]);
$marksRows = $marksStmt->fetchAll();
$marksLabels = array_column($marksRows, 'course_name');
$marksValues = array_map(fn($v) => round((float) $v, 1), array_column($marksRows, 'avg_marks'));

// Recent notifications
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE target_audience IN ('all','students') ORDER BY created_at DESC LIMIT 3");
$notifStmt->execute();
$recentNotifications = $notifStmt->fetchAll();

$flash = getFlash();
$pageTitle = 'Dashboard';
$extraCss = ['dashboard.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'dashboard'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <?php if (!empty($flash['success'])): ?>
            <div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div>
        <?php endif; ?>

        <div class="welcome-banner">
            <h2>Welcome back, <?= sanitize(currentStudentName()) ?> 👋</h2>
            <p>Here's a quick snapshot of your academic progress.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div>
                    <div class="stat-value"><?= $courseCount ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
            </div>
            <div class="stat-card" style="border-left-color:#16a34a;">
                <div class="stat-icon">📅</div>
                <div>
                    <div class="stat-value"><?= $attendancePercent ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
            <div class="stat-card" style="border-left-color:#ffb020;">
                <div class="stat-icon">📝</div>
                <div>
                    <div class="stat-value"><?= $pendingAssignments ?></div>
                    <div class="stat-label">Pending Assignments</div>
                </div>
            </div>
            <div class="stat-card" style="border-left-color:#0ea5e9;">
                <div class="stat-icon">📊</div>
                <div>
                    <div class="stat-value"><?= count($marksRows) ?></div>
                    <div class="stat-label">Graded Courses</div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card">
                <div class="card-title">Average Marks by Course</div>
                <?php if (empty($marksRows)): ?>
                    <div class="empty-state"><div class="icon">📊</div>No marks recorded yet.</div>
                <?php else: ?>
                    <canvas id="marksChart" width="500" height="260"></canvas>
                <?php endif; ?>
            </div>
            <div class="card">
                <div class="card-title">Attendance Breakdown</div>
                <?php if ($totalAttendanceMarks === 0): ?>
                    <div class="empty-state"><div class="icon">📅</div>No attendance recorded yet.</div>
                <?php else: ?>
                    <canvas id="attendanceDoughnut" width="260" height="260" style="margin:0 auto;"></canvas>
                    <div class="d-flex gap-1 flex-wrap mt-1" style="justify-content:center;">
                        <span class="badge badge-success">Present: <?= $attendanceSummary['Present'] ?></span>
                        <span class="badge badge-danger">Absent: <?= $attendanceSummary['Absent'] ?></span>
                        <span class="badge badge-warning">Late: <?= $attendanceSummary['Late'] ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Recent Notifications</div>
            <?php if (empty($recentNotifications)): ?>
                <div class="empty-state"><div class="icon">🔔</div>No notifications yet.</div>
            <?php else: ?>
                <?php foreach ($recentNotifications as $n): ?>
                    <div class="notif-item">
                        <div class="notif-title"><?= sanitize($n['title']) ?></div>
                        <div><?= sanitize($n['message']) ?></div>
                        <div class="notif-date"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
                <a href="notifications.php" class="btn btn-secondary btn-sm">View All</a>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
window.dashboardData = {
    marksLabels: <?= json_encode($marksLabels) ?>,
    marksValues: <?= json_encode($marksValues) ?>,
    attendanceSummary: <?= json_encode($attendanceSummary) ?>
};
</script>

<?php
$extraJs = ['chart.js', 'dashboard.js'];
require_once __DIR__ . '/includes/footer.php';
?>
