<?php
/**
 * Standalone module: Attendance Report - per-course attendance percentage summary for all students.
 * Usage: attendance_report.php?course_id=1
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$courseId = (int) ($_GET['course_id'] ?? 0);
$courses = $pdo->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name')->fetchAll();

$report = [];
if ($courseId) {
    $stmt = $pdo->prepare("SELECT s.roll_no, s.full_name,
            SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) AS present_count,
            COUNT(a.id) AS total_count
        FROM students s
        JOIN enrollments e ON e.student_id = s.id AND e.course_id = :cid
        LEFT JOIN attendance a ON a.student_id = s.id AND a.course_id = :cid2
        GROUP BY s.id, s.roll_no, s.full_name ORDER BY s.full_name");
    $stmt->execute(['cid' => $courseId, 'cid2' => $courseId]);
    $report = $stmt->fetchAll();
}

$pageTitle = 'Attendance Report';
$extraCss = ['admin.css'];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'attendance'; require __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><h1>Attendance Report</h1></div>
        <div class="card">
            <form method="GET" class="filter-bar">
                <select name="course_id" class="form-control" onchange="this.form.submit()">
                    <option value="0">Select a course</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected':'' ?>><?= sanitize($c['course_code'].' - '.$c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if (empty($report)): ?>
                <div class="empty-state"><div class="icon">📅</div>Select a course to view its attendance report.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Roll No</th><th>Name</th><th>Present</th><th>Total Marked</th><th>Percentage</th></tr></thead>
                        <tbody>
                        <?php foreach ($report as $r): ?>
                            <?php $pct = $r['total_count'] > 0 ? round(($r['present_count']/$r['total_count'])*100) : 0; ?>
                            <tr>
                                <td><?= sanitize($r['roll_no']) ?></td>
                                <td><?= sanitize($r['full_name']) ?></td>
                                <td><?= (int)$r['present_count'] ?></td>
                                <td><?= (int)$r['total_count'] ?></td>
                                <td><span class="badge <?= $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger') ?>"><?= $pct ?>%</span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
