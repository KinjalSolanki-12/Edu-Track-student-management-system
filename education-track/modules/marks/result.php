<?php
/**
 * Standalone module: Consolidated result sheet for a single student across all courses.
 * Usage: result.php?student_id=1
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$studentId = (int) ($_GET['student_id'] ?? 0);

$students = $pdo->query('SELECT id, full_name, roll_no FROM students ORDER BY full_name')->fetchAll();
$results = [];
if ($studentId) {
    $stmt = $pdo->prepare("SELECT m.*, c.course_name, c.course_code FROM marks m
        JOIN courses c ON c.id = m.course_id WHERE m.student_id = :id ORDER BY c.course_name");
    $stmt->execute(['id' => $studentId]);
    $results = $stmt->fetchAll();
}

$pageTitle = 'Student Result Sheet';
$extraCss = ['admin.css'];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'marks'; require __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><h1>Student Result Sheet</h1></div>
        <div class="card">
            <form method="GET" class="filter-bar">
                <select name="student_id" class="form-control" onchange="this.form.submit()">
                    <option value="0">Select a student</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $studentId === (int)$s['id'] ? 'selected':'' ?>><?= sanitize($s['full_name'].' ('.$s['roll_no'].')') ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if (empty($results)): ?>
                <div class="empty-state"><div class="icon">📄</div>Select a student to view their result sheet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Course</th><th>Exam Type</th><th>Marks</th><th>Grade</th></tr></thead>
                        <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= sanitize($r['course_code'].' - '.$r['course_name']) ?></td>
                                <td><?= sanitize($r['exam_type']) ?></td>
                                <td><?= $r['marks_obtained'] ?> / <?= $r['total_marks'] ?></td>
                                <td><span class="badge badge-info"><?= sanitize($r['grade']) ?></span></td>
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
