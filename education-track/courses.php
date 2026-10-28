<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();

// Enroll action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_course_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $courseId = (int) $_POST['enroll_course_id'];
        try {
            $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, course_id) VALUES (:sid, :cid)');
            $stmt->execute(['sid' => $studentId, 'cid' => $courseId]);
            setFlash('success', 'Successfully enrolled in the course.');
        } catch (PDOException $e) {
            setFlash('error', 'You are already enrolled in this course.');
        }
    }
    header('Location: courses.php');
    exit;
}

// Unenroll action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unenroll_course_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $courseId = (int) $_POST['unenroll_course_id'];
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE student_id = :sid AND course_id = :cid');
        $stmt->execute(['sid' => $studentId, 'cid' => $courseId]);
        setFlash('success', 'Unenrolled from course.');
    }
    header('Location: courses.php');
    exit;
}

// Enrolled courses
$enrolledStmt = $pdo->prepare("SELECT c.*, t.full_name AS teacher_name FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    LEFT JOIN teachers t ON t.id = c.teacher_id
    WHERE e.student_id = :id ORDER BY c.course_name");
$enrolledStmt->execute(['id' => $studentId]);
$enrolledCourses = $enrolledStmt->fetchAll();
$enrolledIds = array_column($enrolledCourses, 'id');

// Available courses (not yet enrolled)
$availableStmt = $pdo->prepare("SELECT c.*, t.full_name AS teacher_name FROM courses c
    LEFT JOIN teachers t ON t.id = c.teacher_id
    WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = :id)
    ORDER BY c.course_name");
$availableStmt->execute(['id' => $studentId]);
$availableCourses = $availableStmt->fetchAll();

$flash = getFlash();
$pageTitle = 'Course Enrollment';
$extraCss = ['dashboard.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'courses'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Course Enrollment</h1></div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-title">My Enrolled Courses (<?= count($enrolledCourses) ?>)</div>
            <?php if (empty($enrolledCourses)): ?>
                <div class="empty-state"><div class="icon">📚</div>You are not enrolled in any courses yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Code</th><th>Course Name</th><th>Instructor</th><th>Credits</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($enrolledCourses as $c): ?>
                            <tr>
                                <td><?= sanitize($c['course_code']) ?></td>
                                <td><?= sanitize($c['course_name']) ?></td>
                                <td><?= sanitize($c['teacher_name'] ?? 'Unassigned') ?></td>
                                <td><?= (int) $c['credits'] ?></td>
                                <td>
                                    <form method="POST" action="courses.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="unenroll_course_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Unenroll from this course?">Unenroll</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">Available Courses</div>
            <?php if (empty($availableCourses)): ?>
                <div class="empty-state"><div class="icon">🎉</div>You're enrolled in all available courses!</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Code</th><th>Course Name</th><th>Instructor</th><th>Credits</th><th>Description</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($availableCourses as $c): ?>
                            <tr>
                                <td><?= sanitize($c['course_code']) ?></td>
                                <td><?= sanitize($c['course_name']) ?></td>
                                <td><?= sanitize($c['teacher_name'] ?? 'Unassigned') ?></td>
                                <td><?= (int) $c['credits'] ?></td>
                                <td><?= sanitize(strlen((string) $c['description']) > 60 ? substr((string) $c['description'], 0, 60) . '...' : (string) $c['description']) ?></td>
                                <td>
                                    <form method="POST" action="courses.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="enroll_course_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Enroll</button>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
