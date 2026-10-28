<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

// Save attendance for a course/date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $courseId = (int) $_POST['course_id'];
        $date = $_POST['attendance_date'];
        $statuses = $_POST['status'] ?? []; // [student_id => status]

        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, course_id, attendance_date, status, marked_by)
            VALUES (:sid, :cid, :date, :status, :marker)
            ON DUPLICATE KEY UPDATE status = :status2, marked_by = :marker2");

        foreach ($statuses as $studentId => $status) {
            $stmt->execute([
                'sid' => (int) $studentId, 'cid' => $courseId, 'date' => $date,
                'status' => $status, 'marker' => currentAdminName(),
                'status2' => $status, 'marker2' => currentAdminName(),
            ]);
        }
        setFlash('success', 'Attendance saved successfully.');
    }
    header('Location: attendance.php?course_id=' . (int) ($_POST['course_id'] ?? 0) . '&date=' . urlencode($_POST['attendance_date'] ?? ''));
    exit;
}

$courses = $pdo->query('SELECT id, course_code, course_name FROM courses ORDER BY course_name')->fetchAll();

$selectedCourseId = (int) ($_GET['course_id'] ?? ($courses[0]['id'] ?? 0));
$selectedDate = $_GET['date'] ?? date('Y-m-d');

$students = [];
$existingAttendance = [];
if ($selectedCourseId) {
    $stmt = $pdo->prepare("SELECT s.id, s.roll_no, s.full_name FROM students s
        JOIN enrollments e ON e.student_id = s.id WHERE e.course_id = :cid ORDER BY s.full_name");
    $stmt->execute(['cid' => $selectedCourseId]);
    $students = $stmt->fetchAll();

    $attStmt = $pdo->prepare('SELECT student_id, status FROM attendance WHERE course_id = :cid AND attendance_date = :date');
    $attStmt->execute(['cid' => $selectedCourseId, 'date' => $selectedDate]);
    foreach ($attStmt->fetchAll() as $row) {
        $existingAttendance[$row['student_id']] = $row['status'];
    }
}

$flash = getFlash();
$pageTitle = 'Manage Attendance';
$extraCss = ['admin.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'attendance'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Manage Attendance</h1></div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>

        <div class="card">
            <form method="GET" class="filter-bar">
                <select name="course_id" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $selectedCourseId === (int) $c['id'] ? 'selected' : '' ?>><?= sanitize($c['course_code'] . ' - ' . $c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date" class="form-control" value="<?= sanitize($selectedDate) ?>" onchange="this.form.submit()">
            </form>

            <?php if (empty($students)): ?>
                <div class="empty-state"><div class="icon">📅</div>No students enrolled in this course.</div>
            <?php else: ?>
                <form method="POST" action="attendance.php">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="save_attendance" value="1">
                    <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
                    <input type="hidden" name="attendance_date" value="<?= sanitize($selectedDate) ?>">

                    <button type="button" id="markAllPresent" class="btn btn-secondary btn-sm mb-1">Mark All Present</button>

                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Roll No</th><th>Name</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($students as $s): ?>
                                <?php $current = $existingAttendance[$s['id']] ?? 'Present'; ?>
                                <tr class="att-<?= strtolower($current) ?>">
                                    <td><?= sanitize($s['roll_no']) ?></td>
                                    <td><?= sanitize($s['full_name']) ?></td>
                                    <td>
                                        <select name="status[<?= $s['id'] ?>]" class="form-control attendance-status-select">
                                            <option value="Present" <?= $current === 'Present' ? 'selected' : '' ?>>Present</option>
                                            <option value="Absent" <?= $current === 'Absent' ? 'selected' : '' ?>>Absent</option>
                                            <option value="Late" <?= $current === 'Late' ? 'selected' : '' ?>>Late</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary mt-1">Save Attendance</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php
$extraJs = ['attendance.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
