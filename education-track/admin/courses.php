<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'], true)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        header('Location: courses.php');
        exit;
    }

    $code = trim($_POST['course_code'] ?? '');
    $name = trim($_POST['course_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $credits = (int) ($_POST['credits'] ?? 3);
    $teacherId = $_POST['teacher_id'] !== '' ? (int) $_POST['teacher_id'] : null;

    if ($code === '' || $name === '') $errors[] = 'Course code and name are required.';

    if (empty($errors)) {
        if ($_POST['action'] === 'create') {
            $check = $pdo->prepare('SELECT id FROM courses WHERE course_code = :code');
            $check->execute(['code' => $code]);
            if ($check->fetch()) {
                setFlash('error', 'A course with this code already exists.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO courses (course_code, course_name, description, credits, teacher_id)
                    VALUES (:code, :name, :desc, :credits, :teacher_id)');
                $stmt->execute(['code' => $code, 'name' => $name, 'desc' => $desc, 'credits' => $credits, 'teacher_id' => $teacherId]);
                setFlash('success', 'Course added successfully.');
            }
        } else {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('UPDATE courses SET course_code = :code, course_name = :name, description = :desc,
                credits = :credits, teacher_id = :teacher_id WHERE id = :id');
            $stmt->execute(['code' => $code, 'name' => $name, 'desc' => $desc, 'credits' => $credits, 'teacher_id' => $teacherId, 'id' => $id]);
            setFlash('success', 'Course updated successfully.');
        }
    } else {
        setFlash('error', implode(' ', $errors));
    }
    header('Location: courses.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Course deleted successfully.');
    }
    header('Location: courses.php');
    exit;
}

$courses = $pdo->query("SELECT c.*, t.full_name AS teacher_name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolled_count
    FROM courses c LEFT JOIN teachers t ON t.id = c.teacher_id ORDER BY c.course_name")->fetchAll();
$teachers = $pdo->query('SELECT id, full_name FROM teachers WHERE status = "active" ORDER BY full_name')->fetchAll();

$flash = getFlash();
$pageTitle = 'Manage Courses & Subjects';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'courses'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Courses & Subjects</h1>
            <button class="btn btn-primary" onclick="openCourseModal()">+ Add Course</button>
        </div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <div class="card">
            <?php if (empty($courses)): ?>
                <div class="empty-state"><div class="icon">📚</div>No courses found.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Code</th><th>Course Name</th><th>Instructor</th><th>Credits</th><th>Enrolled</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td><?= sanitize($c['course_code']) ?></td>
                                <td><?= sanitize($c['course_name']) ?></td>
                                <td><?= sanitize($c['teacher_name'] ?? 'Unassigned') ?></td>
                                <td><?= (int) $c['credits'] ?></td>
                                <td><span class="badge badge-info"><?= (int) $c['enrolled_count'] ?> students</span></td>
                                <td class="table-actions">
                                    <button class="btn btn-secondary btn-sm" onclick='openCourseModal(<?= json_encode($c) ?>)'>Edit</button>
                                    <form method="POST" action="courses.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this course permanently?">Delete</button>
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

<div class="modal-overlay" id="courseModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3 id="courseModalTitle">Add Course</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="courses.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" id="courseFormAction" value="create">
            <input type="hidden" name="id" id="courseFormId" value="">

            <div class="form-row">
                <div class="form-group">
                    <label for="course_code">Course Code</label>
                    <input type="text" id="course_code" name="course_code" class="form-control" data-rule="required" placeholder="e.g. CS101">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="credits">Credits</label>
                    <input type="number" id="credits" name="credits" class="form-control" min="1" max="10" value="3">
                </div>
            </div>
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" id="course_name" name="course_name" class="form-control" data-rule="required">
                <div class="form-error"></div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="teacher_id">Assign Instructor</label>
                <select id="teacher_id" name="teacher_id" class="form-control">
                    <option value="">Unassigned</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= sanitize($t['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Course</button>
        </form>
    </div>
</div>

<script>
function openCourseModal(course) {
    const modal = document.getElementById('courseModal');
    modal.querySelector('form').reset();
    document.querySelectorAll('#courseModal .invalid').forEach(el => el.classList.remove('invalid'));

    if (course && course.id) {
        document.getElementById('courseModalTitle').textContent = 'Edit Course';
        document.getElementById('courseFormAction').value = 'update';
        document.getElementById('courseFormId').value = course.id;
        document.getElementById('course_code').value = course.course_code;
        document.getElementById('course_name').value = course.course_name;
        document.getElementById('credits').value = course.credits;
        document.getElementById('description').value = course.description || '';
        document.getElementById('teacher_id').value = course.teacher_id || '';
    } else {
        document.getElementById('courseModalTitle').textContent = 'Add Course';
        document.getElementById('courseFormAction').value = 'create';
        document.getElementById('courseFormId').value = '';
    }
    modal.classList.add('open');
}
</script>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
