<?php
/**
 * Standalone module: View a single student's full profile (admin-facing read view).
 * Usage: view_student.php?id=5
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute(['id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('error', 'Student not found.');
    header('Location: ' . BASE_URL . '/admin/students.php');
    exit;
}

$enrollStmt = $pdo->prepare("SELECT c.course_name, c.course_code FROM courses c
    JOIN enrollments e ON e.course_id = c.id WHERE e.student_id = :id");
$enrollStmt->execute(['id' => $id]);
$courses = $enrollStmt->fetchAll();

$pageTitle = 'View Student';
$extraCss = ['admin.css'];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'students'; require __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1><?= sanitize($student['full_name']) ?></h1>
            <a href="<?= BASE_URL ?>/admin/students.php" class="btn btn-secondary">← Back</a>
        </div>
        <div class="card">
            <p><strong>Roll No:</strong> <?= sanitize($student['roll_no']) ?></p>
            <p><strong>Email:</strong> <?= sanitize($student['email']) ?></p>
            <p><strong>Phone:</strong> <?= sanitize($student['phone']) ?></p>
            <p><strong>Gender:</strong> <?= sanitize($student['gender']) ?></p>
            <p><strong>Address:</strong> <?= sanitize($student['address']) ?></p>
            <p><strong>Status:</strong> <span class="badge <?= $student['status']==='active' ? 'badge-success':'badge-danger' ?>"><?= ucfirst($student['status']) ?></span></p>
        </div>
        <div class="card">
            <div class="card-title">Enrolled Courses</div>
            <?php if (empty($courses)): ?>
                <div class="empty-state">No courses enrolled.</div>
            <?php else: ?>
                <ul>
                <?php foreach ($courses as $c): ?>
                    <li>• <?= sanitize($c['course_code']) ?> - <?= sanitize($c['course_name']) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
