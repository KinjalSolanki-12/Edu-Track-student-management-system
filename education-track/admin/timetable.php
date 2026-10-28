<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('INSERT INTO timetable (course_id, day_of_week, start_time, end_time, room)
            VALUES (:cid, :day, :start, :end, :room)');
        $stmt->execute([
            'cid' => (int) $_POST['course_id'], 'day' => $_POST['day_of_week'],
            'start' => $_POST['start_time'], 'end' => $_POST['end_time'], 'room' => trim($_POST['room'] ?? ''),
        ]);
        setFlash('success', 'Timetable entry added.');
    }
    header('Location: timetable.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM timetable WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Timetable entry removed.');
    }
    header('Location: timetable.php');
    exit;
}

$entries = $pdo->query("SELECT t.*, c.course_name, c.course_code FROM timetable t
    JOIN courses c ON c.id = t.course_id ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time")->fetchAll();
$courses = $pdo->query('SELECT id, course_name, course_code FROM courses ORDER BY course_name')->fetchAll();

$flash = getFlash();
$pageTitle = 'Manage Timetable';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'timetable'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Timetable</h1>
            <button class="btn btn-primary" data-modal-target="#ttModal">+ Add Entry</button>
        </div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>

        <div class="card">
            <?php if (empty($entries)): ?>
                <div class="empty-state"><div class="icon">🗓️</div>No timetable entries yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Day</th><th>Time</th><th>Course</th><th>Room</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($entries as $t): ?>
                            <tr>
                                <td><?= sanitize($t['day_of_week']) ?></td>
                                <td><?= substr($t['start_time'], 0, 5) ?> - <?= substr($t['end_time'], 0, 5) ?></td>
                                <td><?= sanitize($t['course_code']) ?> - <?= sanitize($t['course_name']) ?></td>
                                <td><?= sanitize($t['room']) ?></td>
                                <td>
                                    <form method="POST" action="timetable.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Remove this entry?">Delete</button>
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

<div class="modal-overlay" id="ttModal">
    <div class="modal-box">
        <div class="modal-box-header">
            <h3>Add Timetable Entry</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="timetable.php" data-validate novalidate>
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
                <label for="day_of_week">Day</label>
                <select id="day_of_week" name="day_of_week" class="form-control">
                    <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $d): ?>
                        <option value="<?= $d ?>"><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" class="form-control" data-rule="required">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" data-rule="required">
                    <div class="form-error"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="room">Room</label>
                <input type="text" id="room" name="room" class="form-control" placeholder="e.g. Room 101">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Entry</button>
        </form>
    </div>
</div>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
