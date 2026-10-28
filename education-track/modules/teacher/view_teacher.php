<?php
/** Standalone module: View a single teacher's profile with assigned courses. Usage: view_teacher.php?id=2 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
$stmt->execute(['id' => $id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    setFlash('error', 'Teacher not found.');
    header('Location: ' . BASE_URL . '/admin/teachers.php');
    exit;
}

$coursesStmt = $pdo->prepare('SELECT course_name, course_code FROM courses WHERE teacher_id = :id');
$coursesStmt->execute(['id' => $id]);
$courses = $coursesStmt->fetchAll();

$pageTitle = 'View Teacher';
$extraCss = ['admin.css'];
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>
<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'teachers'; require __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1><?= sanitize($teacher['full_name']) ?></h1>
            <a href="<?= BASE_URL ?>/admin/teachers.php" class="btn btn-secondary">← Back</a>
        </div>
        <div class="card">
            <p><strong>Email:</strong> <?= sanitize($teacher['email']) ?></p>
            <p><strong>Phone:</strong> <?= sanitize($teacher['phone']) ?></p>
            <p><strong>Specialization:</strong> <?= sanitize($teacher['specialization']) ?></p>
            <p><strong>Status:</strong> <span class="badge <?= $teacher['status']==='active' ? 'badge-success':'badge-danger' ?>"><?= ucfirst($teacher['status']) ?></span></p>
        </div>
        <div class="card">
            <div class="card-title">Assigned Courses</div>
            <?php if (empty($courses)): ?>
                <div class="empty-state">No courses assigned.</div>
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
