<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();

$courseFilter = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

$courses = $pdo->prepare("SELECT c.id, c.course_name FROM courses c JOIN enrollments e ON e.course_id = c.id WHERE e.student_id = :id ORDER BY c.course_name");
$courses->execute(['id' => $studentId]);
$courses = $courses->fetchAll();

$sql = "SELECT a.*, c.course_name FROM attendance a JOIN courses c ON c.id = a.course_id WHERE a.student_id = :id";
$params = ['id' => $studentId];
if ($courseFilter > 0) {
    $sql .= " AND a.course_id = :cid";
    $params['cid'] = $courseFilter;
}
$sql .= " ORDER BY a.attendance_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$total = count($records);
$present = count(array_filter($records, fn($r) => $r['status'] === 'Present'));
$percent = $total > 0 ? round(($present / $total) * 100) : 0;

$pageTitle = 'Attendance Tracking';
$extraCss = ['dashboard.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'attendance'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Attendance Tracking</h1></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div><div class="stat-value"><?= $percent ?>%</div><div class="stat-label">Overall Attendance</div></div>
            </div>
            <div class="stat-card" style="border-left-color:#16a34a;">
                <div class="stat-icon">✅</div>
                <div><div class="stat-value"><?= $present ?></div><div class="stat-label">Classes Present</div></div>
            </div>
            <div class="stat-card" style="border-left-color:#dc2626;">
                <div class="stat-icon">❌</div>
                <div><div class="stat-value"><?= $total - $present ?></div><div class="stat-label">Classes Missed/Late</div></div>
            </div>
        </div>

        <div class="card">
            <form method="GET" class="filter-bar">
                <select name="course_id" class="form-control" onchange="this.form.submit()">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $courseFilter === (int) $c['id'] ? 'selected' : '' ?>><?= sanitize($c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if (empty($records)): ?>
                <div class="empty-state"><div class="icon">📅</div>No attendance records found.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date</th><th>Course</th><th>Status</th><th>Marked By</th></tr></thead>
                        <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($r['attendance_date'])) ?></td>
                                <td><?= sanitize($r['course_name']) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = $r['status'] === 'Present' ? 'badge-success' : ($r['status'] === 'Late' ? 'badge-warning' : 'badge-danger');
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= sanitize($r['status']) ?></span>
                                </td>
                                <td><?= sanitize($r['marked_by'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
