<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$errors = [];

// ---------- CREATE / UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['create', 'update'], true)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Invalid form submission.');
        header('Location: students.php');
        exit;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $gender   = trim($_POST['gender'] ?? 'Male');
    $address  = trim($_POST['address'] ?? '');
    $status   = trim($_POST['status'] ?? 'active');

    if ($fullName === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

    if (empty($errors)) {
        if ($_POST['action'] === 'create') {
            $check = $pdo->prepare('SELECT id FROM students WHERE email = :email');
            $check->execute(['email' => $email]);
            if ($check->fetch()) {
                setFlash('error', 'A student with this email already exists.');
            } else {
                $rollNo = 'STU' . date('Y') . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
                $defaultPassword = password_hash('student123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO students (roll_no, full_name, email, password, phone, gender, address, status)
                    VALUES (:roll_no, :full_name, :email, :password, :phone, :gender, :address, :status)');
                $stmt->execute([
                    'roll_no' => $rollNo, 'full_name' => $fullName, 'email' => $email,
                    'password' => $defaultPassword, 'phone' => $phone, 'gender' => $gender,
                    'address' => $address, 'status' => $status,
                ]);
                setFlash('success', "Student added successfully. Roll No: $rollNo (default password: student123)");
            }
        } else {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('UPDATE students SET full_name = :full_name, email = :email, phone = :phone,
                gender = :gender, address = :address, status = :status WHERE id = :id');
            $stmt->execute([
                'full_name' => $fullName, 'email' => $email, 'phone' => $phone,
                'gender' => $gender, 'address' => $address, 'status' => $status, 'id' => $id,
            ]);
            setFlash('success', 'Student updated successfully.');
        }
    } else {
        setFlash('error', implode(' ', $errors));
    }
    header('Location: students.php');
    exit;
}

// ---------- DELETE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Student deleted successfully.');
    }
    header('Location: students.php');
    exit;
}

// ---------- LIST / SEARCH ----------
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT * FROM students WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (full_name LIKE :s1 OR email LIKE :s2 OR roll_no LIKE :s3)';
    $params['s1'] = "%$search%";
    $params['s2'] = "%$search%";
    $params['s3'] = "%$search%";
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$flash = getFlash();
$pageTitle = 'Manage Students';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'students'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Students</h1>
            <button class="btn btn-primary" data-modal-target="#studentModal" onclick="openStudentModal()">+ Add Student</button>
        </div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <div class="card">
            <form method="GET" class="filter-bar">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, roll no..." value="<?= sanitize($search) ?>">
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Search</button>
                <?php if ($search !== ''): ?><a href="students.php" class="btn btn-sm" style="color:var(--text-muted);">Clear</a><?php endif; ?>
            </form>

            <?php if (empty($students)): ?>
                <div class="empty-state"><div class="icon">🎓</div>No students found.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Roll No</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?= sanitize($s['roll_no']) ?></td>
                                <td><?= sanitize($s['full_name']) ?></td>
                                <td><?= sanitize($s['email']) ?></td>
                                <td><?= sanitize($s['phone']) ?></td>
                                <td><?= sanitize($s['gender']) ?></td>
                                <td><span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($s['status']) ?></span></td>
                                <td class="table-actions">
                                    <button class="btn btn-secondary btn-sm" onclick='openStudentModal(<?= json_encode($s) ?>)'>Edit</button>
                                    <form method="POST" action="students.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this student permanently?">Delete</button>
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

<div class="modal-overlay" id="studentModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3 id="studentModalTitle">Add Student</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="students.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" id="studentFormAction" value="create">
            <input type="hidden" name="id" id="studentFormId" value="">

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" data-rule="required">
                <div class="form-error"></div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" data-rule="required|email">
                <div class="form-error"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" data-rule="phone">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" class="form-control">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <p style="font-size:.78rem; color:var(--text-muted);">New students get default password: <strong>student123</strong></p>
            <button type="submit" class="btn btn-primary btn-block">Save Student</button>
        </form>
    </div>
</div>

<script>
function openStudentModal(student) {
    const modal = document.getElementById('studentModal');
    const title = document.getElementById('studentModalTitle');
    const form = modal.querySelector('form');

    form.reset();
    document.querySelectorAll('#studentModal .invalid').forEach(el => el.classList.remove('invalid'));

    if (student && student.id) {
        title.textContent = 'Edit Student';
        document.getElementById('studentFormAction').value = 'update';
        document.getElementById('studentFormId').value = student.id;
        document.getElementById('full_name').value = student.full_name;
        document.getElementById('email').value = student.email;
        document.getElementById('phone').value = student.phone || '';
        document.getElementById('gender').value = student.gender;
        document.getElementById('address').value = student.address || '';
        document.getElementById('status').value = student.status;
    } else {
        title.textContent = 'Add Student';
        document.getElementById('studentFormAction').value = 'create';
        document.getElementById('studentFormId').value = '';
    }
    modal.classList.add('open');
}
</script>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
