<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

function computeGrade(float $percent): string
{
    if ($percent >= 90) return 'A+';
    if ($percent >= 80) return 'A';
    if ($percent >= 70) return 'B+';
    if ($percent >= 60) return 'B';
    if ($percent >= 50) return 'C';
    return 'F';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'], true)) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $studentId = (int) $_POST['student_id'];
        $courseId = (int) $_POST['course_id'];
        $examType = trim($_POST['exam_type'] ?? 'Final');
        $obtained = (float) $_POST['marks_obtained'];
        $total = (float) $_POST['total_marks'];
        $percent = $total > 0 ? ($obtained / $total) * 100 : 0;
        $grade = computeGrade($percent);

        if ($_POST['action'] === 'create') {
            $stmt = $pdo->prepare('INSERT INTO marks (student_id, course_id, exam_type, marks_obtained, total_marks, grade)
                VALUES (:sid, :cid, :exam, :obtained, :total, :grade)');
            $stmt->execute(['sid' => $studentId, 'cid' => $courseId, 'exam' => $examType, 'obtained' => $obtained, 'total' => $total, 'grade' => $grade]);
            setFlash('success', 'Marks added successfully.');
        } else {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('UPDATE marks SET student_id = :sid, course_id = :cid, exam_type = :exam,
                marks_obtained = :obtained, total_marks = :total, grade = :grade WHERE id = :id');
            $stmt->execute(['sid' => $studentId, 'cid' => $courseId, 'exam' => $examType, 'obtained' => $obtained, 'total' => $total, 'grade' => $grade, 'id' => $id]);
            setFlash('success', 'Marks updated successfully.');
        }
    }
    header('Location: marks.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM marks WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Marks record deleted.');
    }
    header('Location: marks.php');
    exit;
}

$marks = $pdo->query("SELECT m.*, s.full_name AS student_name, s.roll_no, c.course_name, c.course_code
    FROM marks m JOIN students s ON s.id = m.student_id JOIN courses c ON c.id = m.course_id
    ORDER BY m.created_at DESC")->fetchAll();
$students = $pdo->query('SELECT id, full_name, roll_no FROM students ORDER BY full_name')->fetchAll();
$courses = $pdo->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name')->fetchAll();

$flash = getFlash();
$pageTitle = 'Manage Marks & Results';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'marks'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Marks & Results</h1>
            <button class="btn btn-primary" onclick="openMarksModal()">+ Add Marks</button>
        </div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>

        <div class="card">
            <?php if (empty($marks)): ?>
                <div class="empty-state"><div class="icon">📊</div>No marks recorded yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Student</th><th>Course</th><th>Exam</th><th>Marks</th><th>Grade</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($marks as $m): ?>
                            <tr>
                                <td><?= sanitize($m['student_name']) ?> <span style="color:var(--text-muted);">(<?= sanitize($m['roll_no']) ?>)</span></td>
                                <td><?= sanitize($m['course_code']) ?></td>
                                <td><?= sanitize($m['exam_type']) ?></td>
                                <td><?= $m['marks_obtained'] ?> / <?= $m['total_marks'] ?></td>
                                <td><span class="badge badge-info"><?= sanitize($m['grade']) ?></span></td>
                                <td class="table-actions">
                                    <button class="btn btn-secondary btn-sm" onclick='openMarksModal(<?= json_encode($m) ?>)'>Edit</button>
                                    <form method="POST" action="marks.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this record?">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="modal-overlay" id="marksModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3 id="marksModalTitle">Add Marks</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="marks.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" id="marksFormAction" value="create">
            <input type="hidden" name="id" id="marksFormId" value="">

            <div class="form-group">
                <label for="student_id">Student</label>
                <select id="student_id" name="student_id" class="form-control" data-rule="required">
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= sanitize($s['full_name'] . ' (' . $s['roll_no'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="course_id">Course</label>
                <select id="course_id" name="course_id" class="form-control" data-rule="required">
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= sanitize($c['course_code'] . ' - ' . $c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="exam_type">Exam Type</label>
                <select id="exam_type" name="exam_type" class="form-control">
                    <option value="Quiz">Quiz</option>
                    <option value="Midterm">Midterm</option>
                    <option value="Final">Final</option>
                    <option value="Assignment">Assignment</option>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="marks_obtained">Marks Obtained</label>
                    <input type="number" step="0.01" id="marks_obtained" name="marks_obtained" class="form-control" data-rule="required|number">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="total_marks">Total Marks</label>
                    <input type="number" step="0.01" id="total_marks" name="total_marks" class="form-control" value="100" data-rule="required|number">
                    <div class="form-error"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Marks</button>
        </form>
    </div>
</div>

<script>
function openMarksModal(m) {
    const modal = document.getElementById('marksModal');
    modal.querySelector('form').reset();
    document.querySelectorAll('#marksModal .invalid').forEach(el => el.classList.remove('invalid'));

    if (m && m.id) {
        document.getElementById('marksModalTitle').textContent = 'Edit Marks';
        document.getElementById('marksFormAction').value = 'update';
        document.getElementById('marksFormId').value = m.id;
        document.getElementById('student_id').value = m.student_id;
        document.getElementById('course_id').value = m.course_id;
        document.getElementById('exam_type').value = m.exam_type;
        document.getElementById('marks_obtained').value = m.marks_obtained;
        document.getElementById('total_marks').value = m.total_marks;
    } else {
        document.getElementById('marksModalTitle').textContent = 'Add Marks';
        document.getElementById('marksFormAction').value = 'create';
        document.getElementById('marksFormId').value = '';
    }
    modal.classList.add('open');
}
</script>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
