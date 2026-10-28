<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();
$errors = [];

// Handle assignment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission.';
    } else {
        $assignmentId = (int) $_POST['assignment_id'];

        if (empty($_FILES['submission_file']['name'])) {
            setFlash('error', 'Please choose a file to submit.');
        } else {
            $file = $_FILES['submission_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file['error'] !== UPLOAD_ERR_OK) {
                setFlash('error', 'File upload failed. Please try again.');
            } elseif (!in_array($ext, ALLOWED_DOC_TYPES, true)) {
                setFlash('error', 'File type not allowed. Allowed: ' . implode(', ', ALLOWED_DOC_TYPES));
            } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
                setFlash('error', 'File must be smaller than 5MB.');
            } else {
                $newFileName = 'sub_' . $studentId . '_' . $assignmentId . '_' . time() . '.' . $ext;
                $destination = UPLOAD_DIR_ASSIGNMENTS . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $stmt = $pdo->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, file_path)
                        VALUES (:aid, :sid, :file)
                        ON DUPLICATE KEY UPDATE file_path = :file2, submitted_at = NOW()");
                    $stmt->execute(['aid' => $assignmentId, 'sid' => $studentId, 'file' => $newFileName, 'file2' => $newFileName]);
                    setFlash('success', 'Assignment submitted successfully.');
                } else {
                    setFlash('error', 'Could not save uploaded file.');
                }
            }
        }
        header('Location: assignments.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT a.*, c.course_name, c.course_code,
        s.file_path AS submitted_file, s.submitted_at, s.grade AS submission_grade, s.feedback
    FROM assignments a
    JOIN enrollments e ON e.course_id = a.course_id AND e.student_id = :id
    JOIN courses c ON c.id = a.course_id
    LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = :id2
    ORDER BY a.due_date ASC");
$stmt->execute(['id' => $studentId, 'id2' => $studentId]);
$assignments = $stmt->fetchAll();

$flash = getFlash();
$pageTitle = 'Assignments';
$extraCss = ['dashboard.css', 'forms.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'assignments'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Assignments</h1></div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <?php if (empty($assignments)): ?>
            <div class="card"><div class="empty-state"><div class="icon">📝</div>No assignments available for your enrolled courses.</div></div>
        <?php else: ?>
            <?php foreach ($assignments as $a): ?>
                <?php
                    $isSubmitted = !empty($a['submitted_file']);
                    $isOverdue = !$isSubmitted && strtotime($a['due_date']) < strtotime('today');
                ?>
                <div class="card">
                    <div class="page-header" style="margin-bottom:.5rem;">
                        <div>
                            <h3 style="margin-bottom:.2rem;"><?= sanitize($a['title']) ?></h3>
                            <span style="color:var(--text-muted); font-size:.85rem;"><?= sanitize($a['course_name']) ?> (<?= sanitize($a['course_code']) ?>)</span>
                        </div>
                        <div>
                            <?php if ($isSubmitted): ?>
                                <span class="badge badge-success">Submitted</span>
                            <?php elseif ($isOverdue): ?>
                                <span class="badge badge-danger">Overdue</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p><?= sanitize((string) $a['description']) ?></p>
                    <p style="font-size:.85rem;"><strong>Due:</strong> <?= date('d M Y', strtotime($a['due_date'])) ?></p>

                    <?php if ($isSubmitted): ?>
                        <p style="font-size:.85rem;">Submitted on <?= date('d M Y, h:i A', strtotime($a['submitted_at'])) ?>
                            <?php if ($a['submission_grade']): ?> &mdash; Grade: <span class="badge badge-info"><?= sanitize($a['submission_grade']) ?></span><?php endif; ?>
                        </p>
                        <?php if ($a['feedback']): ?><p style="font-size:.85rem;"><strong>Feedback:</strong> <?= sanitize($a['feedback']) ?></p><?php endif; ?>
                        <a href="<?= UPLOAD_URL_ASSIGNMENTS . sanitize($a['submitted_file']) ?>" target="_blank" class="btn btn-secondary btn-sm">View Submission</a>
                        <button class="btn btn-primary btn-sm" data-modal-target="#modal-<?= $a['id'] ?>">Resubmit</button>
                    <?php else: ?>
                        <button class="btn btn-primary btn-sm" data-modal-target="#modal-<?= $a['id'] ?>">Submit Assignment</button>
                    <?php endif; ?>
                </div>

                <div class="modal-overlay" id="modal-<?= $a['id'] ?>">
                    <div class="modal-box">
                        <div class="modal-box-header">
                            <h3>Submit: <?= sanitize($a['title']) ?></h3>
                            <button class="modal-close" data-modal-close>&times;</button>
                        </div>
                        <form method="POST" action="assignments.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                            <div class="form-group">
                                <label class="file-input-wrap">
                                    📎 Click to choose a file (PDF, DOC, ZIP, images — max 5MB)
                                    <input type="file" name="submission_file" required style="margin-top:.5rem;">
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Upload Submission</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
