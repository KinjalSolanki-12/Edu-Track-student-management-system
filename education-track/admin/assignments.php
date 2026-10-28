<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

// Create assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $courseId = (int) $_POST['course_id'];
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;
        $filePath = null;

        if (!empty($_FILES['attachment']['name'])) {
            $file = $_FILES['attachment'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file['error'] === UPLOAD_ERR_OK && in_array($ext, ALLOWED_DOC_TYPES, true) && $file['size'] <= MAX_UPLOAD_SIZE) {
                $newFileName = 'assign_' . time() . '_' . random_int(100, 999) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR_ASSIGNMENTS . $newFileName)) {
                    $filePath = $newFileName;
                }
            }
        }

        if ($title === '' || $courseId === 0) {
            setFlash('error', 'Title and course are required.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO assignments (course_id, title, description, file_path, due_date, uploaded_by)
                VALUES (:cid, :title, :desc, :file, :due, :by)');
            $stmt->execute(['cid' => $courseId, 'title' => $title, 'desc' => $description, 'file' => $filePath, 'due' => $dueDate ?: null, 'by' => currentAdminName()]);
            setFlash('success', 'Assignment uploaded successfully.');
        }
    }
    header('Location: assignments.php');
    exit;
}

// Delete assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM assignments WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Assignment deleted.');
    }
    header('Location: assignments.php');
    exit;
}

// Grade a submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'grade_submission') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('UPDATE assignment_submissions SET grade = :grade, feedback = :feedback WHERE id = :id');
        $stmt->execute(['grade' => trim($_POST['grade'] ?? ''), 'feedback' => trim($_POST['feedback'] ?? ''), 'id' => (int) $_POST['submission_id']]);
        setFlash('success', 'Submission graded.');
    }
    header('Location: assignments.php');
    exit;
}

$assignments = $pdo->query("SELECT a.*, c.course_name, c.course_code,
        (SELECT COUNT(*) FROM assignment_submissions s WHERE s.assignment_id = a.id) AS submission_count,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = a.course_id) AS enrolled_count
    FROM assignments a JOIN courses c ON c.id = a.course_id ORDER BY a.created_at DESC")->fetchAll();
$courses = $pdo->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name')->fetchAll();

$flash = getFlash();
$pageTitle = 'Manage Assignments';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'assignments'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Assignments</h1>
            <button class="btn btn-primary" data-modal-target="#assignmentModal">+ Upload Assignment</button>
        </div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <?php if (empty($assignments)): ?>
            <div class="card"><div class="empty-state"><div class="icon">📝</div>No assignments uploaded yet.</div></div>
        <?php else: ?>
            <?php foreach ($assignments as $a): ?>
                <div class="card">
                    <div class="page-header" style="margin-bottom:.5rem;">
                        <div>
                            <h3 style="margin-bottom:.2rem;"><?= sanitize($a['title']) ?></h3>
                            <span style="color:var(--text-muted); font-size:.85rem;"><?= sanitize($a['course_name']) ?> (<?= sanitize($a['course_code']) ?>) &middot; Due: <?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : 'N/A' ?></span>
                        </div>
                        <div class="table-actions">
                            <span class="badge badge-info"><?= (int) $a['submission_count'] ?> / <?= (int) $a['enrolled_count'] ?> submitted</span>
                            <form method="POST" action="assignments.php" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this assignment?">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p><?= sanitize((string) $a['description']) ?></p>

                    <?php
                    $subStmt = $pdo->prepare("SELECT s.*, st.full_name, st.roll_no FROM assignment_submissions s
                        JOIN students st ON st.id = s.student_id WHERE s.assignment_id = :aid ORDER BY s.submitted_at DESC");
                    $subStmt->execute(['aid' => $a['id']]);
                    $submissions = $subStmt->fetchAll();
                    ?>
                    <?php if (!empty($submissions)): ?>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><th>Student</th><th>Submitted</th><th>File</th><th>Grade</th><th>Action</th></tr></thead>
                                <tbody>
                                <?php foreach ($submissions as $s): ?>
                                    <tr>
                                        <td><?= sanitize($s['full_name']) ?> (<?= sanitize($s['roll_no']) ?>)</td>
                                        <td><?= date('d M Y, h:i A', strtotime($s['submitted_at'])) ?></td>
                                        <td><a href="<?= UPLOAD_URL_ASSIGNMENTS . sanitize($s['file_path']) ?>" target="_blank">View File</a></td>
                                        <td><?= $s['grade'] ? '<span class="badge badge-success">' . sanitize($s['grade']) . '</span>' : '<span class="badge badge-warning">Ungraded</span>' ?></td>
                                        <td>
                                            <button class="btn btn-secondary btn-sm" onclick='openGradeModal(<?= json_encode($s) ?>)'>Grade</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<div class="modal-overlay" id="assignmentModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3>Upload Assignment</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="assignments.php" enctype="multipart/form-data" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="course_id">Course</label>
                <select id="course_id" name="course_id" class="form-control" data-rule="required">
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= sanitize($c['course_code'] . ' - ' . $c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" data-rule="required">
                <div class="form-error"></div>
            </div>
            <div class="form-group">
                <label for="description">Description / Instructions</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" class="form-control">
            </div>
            <div class="form-group">
                <label class="file-input-wrap">📎 Optional: attach a reference file
                    <input type="file" name="attachment" style="margin-top:.5rem;">
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Upload Assignment</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="gradeModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3>Grade Submission</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="assignments.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="grade_submission">
            <input type="hidden" name="submission_id" id="grade_submission_id">
            <div class="form-group">
                <label for="grade">Grade</label>
                <input type="text" id="grade" name="grade" class="form-control" placeholder="e.g. A, B+, 85/100">
            </div>
            <div class="form-group">
                <label for="feedback">Feedback</label>
                <textarea id="feedback" name="feedback" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Grade</button>
        </form>
    </div>
</div>

<script>
function openGradeModal(sub) {
    document.getElementById('grade_submission_id').value = sub.id;
    document.getElementById('grade').value = sub.grade || '';
    document.getElementById('feedback').value = sub.feedback || '';
    document.getElementById('gradeModal').classList.add('open');
}
</script>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
