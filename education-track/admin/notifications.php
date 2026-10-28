<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $audience = $_POST['target_audience'] ?? 'all';

        if ($title === '' || $message === '') {
            setFlash('error', 'Title and message are required.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO notifications (title, message, target_audience, created_by)
                VALUES (:title, :message, :audience, :by)');
            $stmt->execute(['title' => $title, 'message' => $message, 'audience' => $audience, 'by' => currentAdminName()]);
            setFlash('success', 'Notification sent successfully.');
        }
    }
    header('Location: notifications.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('DELETE FROM notifications WHERE id = :id');
        $stmt->execute(['id' => (int) $_POST['id']]);
        setFlash('success', 'Notification deleted.');
    }
    header('Location: notifications.php');
    exit;
}

$notifications = $pdo->query('SELECT * FROM notifications ORDER BY created_at DESC')->fetchAll();

$flash = getFlash();
$pageTitle = 'Send Notifications';
$extraCss = ['admin.css', 'dashboard.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'notifications'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Send Notifications</h1></div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-title">Compose Notification</div>
            <form method="POST" action="notifications.php" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" data-rule="required">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="notifMessage">Message</label>
                    <textarea id="notifMessage" name="message" class="form-control" data-rule="required" rows="4"></textarea>
                    <div class="form-error"></div>
                    <div id="notifMessageCounter" style="font-size:.78rem; color:var(--text-muted); margin-top:.25rem;">0 characters</div>
                </div>
                <div class="form-group">
                    <label for="target_audience">Send To</label>
                    <select id="target_audience" name="target_audience" class="form-control">
                        <option value="all">Everyone</option>
                        <option value="students">Students Only</option>
                        <option value="teachers">Teachers Only</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Send Notification</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">Sent Notifications</div>
            <?php if (empty($notifications)): ?>
                <div class="empty-state"><div class="icon">🔔</div>No notifications sent yet.</div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-item d-flex" style="justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div class="notif-title"><?= sanitize($n['title']) ?> <span class="badge badge-info"><?= ucfirst($n['target_audience']) ?></span></div>
                            <div><?= sanitize($n['message']) ?></div>
                            <div class="notif-date"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></div>
                        </div>
                        <form method="POST" action="notifications.php">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this notification?">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php
$extraJs = ['validation.js', 'notifications.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
