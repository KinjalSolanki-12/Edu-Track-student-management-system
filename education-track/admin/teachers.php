<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['create', 'update'], true)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        header('Location: teachers.php');
        exit;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $spec     = trim($_POST['specialization'] ?? '');
    $status   = trim($_POST['status'] ?? 'active');

    if ($fullName === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

    if (empty($errors)) {
        if ($_POST['action'] === 'create') {
            $check = $pdo->prepare('SELECT id FROM teachers WHERE email = :email');
            $check->execute(['email' => $email]);
            if ($check->fetch()) {
                setFlash('error', 'A teacher with this email already exists.');
            } else {
                $defaultPassword = password_hash('teacher123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO teachers (full_name, email, password, phone, specialization, status)
                    VALUES (:full_name, :email, :password, :phone, :specialization, :status)');
                $stmt->execute([
                    'full_name' => $fullName, 'email' => $email, 'password' => $defaultPassword,
                    'phone' => $phone, 'specialization' => $spec, 'status' => $status,
                ]);
                setFlash('success', 'Teacher added successfully (default password: teacher123).');
            }
        } else {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('UPDATE teachers SET full_name = :full_name, email = :email, phone = :phone,
                specialization = :specialization, status = :status WHERE id = :id');
            $stmt->execute([
                'full_name' => $fullName, 'email' => $email, 'phone' => $phone,
                'specialization' => $spec, 'status' => $status, 'id' => $id,
            ]);
            setFlash('success', 'Teacher updated successfully.');
        }
    } else {
        setFlash('error', implode(' ', $errors));
    }
    header('Location: teachers.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM teachers WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Teacher deleted successfully.');
    }
    header('Location: teachers.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$sql = 'SELECT * FROM teachers WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (full_name LIKE :s1 OR email LIKE :s2 OR specialization LIKE :s3)';
    $params['s1'] = "%$search%"; $params['s2'] = "%$search%"; $params['s3'] = "%$search%";
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$teachers = $stmt->fetchAll();

$flash = getFlash();
$pageTitle = 'Manage Teachers';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'teachers'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Teachers</h1>
            <button class="btn btn-primary" onclick="openTeacherModal()">+ Add Teacher</button>
        </div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <div class="card">
            <form method="GET" class="filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, specialization..." value="<?= sanitize($search) ?>">
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Search</button>
                <?php if ($search !== ''): ?><a href="teachers.php" class="btn btn-sm" style="color:var(--text-muted);">Clear</a><?php endif; ?>
            </form>

            <?php if (empty($teachers)): ?>
                <div class="empty-state"><div class="icon">🧑‍🏫</div>No teachers found.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Specialization</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($teachers as $t): ?>
                            <tr>
                                <td><?= sanitize($t['full_name']) ?></td>
                                <td><?= sanitize($t['email']) ?></td>
                                <td><?= sanitize($t['phone']) ?></td>
                                <td><?= sanitize($t['specialization']) ?></td>
                                <td><span class="badge <?= $t['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($t['status']) ?></span></td>
                                <td class="table-actions">
                                    <button class="btn btn-secondary btn-sm" onclick='openTeacherModal(<?= json_encode($t) ?>)'>Edit</button>
                                    <form method="POST" action="teachers.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this teacher permanently?">Delete</button>
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

<div class="modal-overlay" id="teacherModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3 id="teacherModalTitle">Add Teacher</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="teachers.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" id="teacherFormAction" value="create">
            <input type="hidden" name="id" id="teacherFormId" value="">

            <div class="form-group">
                <label for="t_full_name">Full Name</label>
                <input type="text" id="t_full_name" name="full_name" class="form-control" data-rule="required">
                <div class="form-error"></div>
            </div>
            <div class="form-group">
                <label for="t_email">Email</label>
                <input type="email" id="t_email" name="email" class="form-control" data-rule="required|email">
                <div class="form-error"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="t_phone">Phone</label>
                    <input type="text" id="t_phone" name="phone" class="form-control" data-rule="phone">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="t_status">Status</label>
                    <select id="t_status" name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="t_spec">Specialization</label>
                <input type="text" id="t_spec" name="specialization" class="form-control" placeholder="e.g. Computer Science">
            </div>
            <p style="font-size:.78rem; color:var(--text-muted);">New teachers get default password: <strong>teacher123</strong></p>
            <button type="submit" class="btn btn-primary btn-block">Save Teacher</button>
        </form>
    </div>
</div>

<script>
function openTeacherModal(teacher) {
    const modal = document.getElementById('teacherModal');
    const title = document.getElementById('teacherModalTitle');
    modal.querySelector('form').reset();
    document.querySelectorAll('#teacherModal .invalid').forEach(el => el.classList.remove('invalid'));

    if (teacher && teacher.id) {
        title.textContent = 'Edit Teacher';
        document.getElementById('teacherFormAction').value = 'update';
        document.getElementById('teacherFormId').value = teacher.id;
        document.getElementById('t_full_name').value = teacher.full_name;
        document.getElementById('t_email').value = teacher.email;
        document.getElementById('t_phone').value = teacher.phone || '';
        document.getElementById('t_status').value = teacher.status;
        document.getElementById('t_spec').value = teacher.specialization || '';
    } else {
        title.textContent = 'Add Teacher';
        document.getElementById('teacherFormAction').value = 'create';
        document.getElementById('teacherFormId').value = '';
    }
    modal.classList.add('open');
}
</script>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
