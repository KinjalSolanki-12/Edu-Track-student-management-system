<?php
/**
 * Standalone module: View a single assignment's details and submission list (admin-facing).
 * Usage: view.php?id=3
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, c.course_name, c.course_code FROM assignments a
    JOIN courses c ON c.id = a.course_id WHERE a.id = :id");
$stmt->execute(['id' => $id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    setFlash('error', 'Assignment not found.');
    header('Location: ' . BASE_URL . '/admin/assignments.php');
    exit;
}

$subStmt = $pdo->prepare("SELECT s.*, st.full_name, st.roll_no FROM assignment_submissions s
    JOIN students st ON st.id = s.student_id WHERE s.assignment_id = :id ORDER BY s.submitted_at DESC");
$subStmt->execute(['id' => $id]);
$submissions = $subStmt->fetchAll();

$pageTitle = 'View Assignment';
$extraCss = ['admin.css'];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'assignments'; require __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1><?= sanitize($assignment['title']) ?></h1>
            <a href="<?= BASE_URL ?>/admin/assignments.php" class="btn btn-secondary">← Back</a>
        </div>
        <div class="card">
            <p><strong>Course:</strong> <?= sanitize($assignment['course_code'].' - '.$assignment['course_name']) ?></p>
            <p><strong>Due Date:</strong> <?= $assignment['due_date'] ? date('d M Y', strtotime($assignment['due_date'])) : 'N/A' ?></p>
            <p><?= sanitize((string)$assignment['description']) ?></p>
        </div>
        <div class="card">
            <div class="card-title">Submissions (<?= count($submissions) ?>)</div>
            <?php if (empty($submissions)): ?>
                <div class="empty-state">No submissions yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Student</th><th>Submitted</th><th>File</th><th>Grade</th></tr></thead>
                        <tbody>
                        <?php foreach ($submissions as $s): ?>
                            <tr>
                                <td><?= sanitize($s['full_name']).' ('.sanitize($s['roll_no']).')' ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($s['submitted_at'])) ?></td>
                                <td><a href="<?= UPLOAD_URL_ASSIGNMENTS . sanitize($s['file_path']) ?>" target="_blank">View</a></td>
                                <td><?= sanitize((string)$s['grade']) ?: '-' ?></td>
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
