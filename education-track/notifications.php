<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM notifications WHERE target_audience IN ('all','students') ORDER BY created_at DESC");
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
$extraCss = ['dashboard.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'notifications'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>Notifications</h1></div>

        <div class="card">
            <?php if (empty($notifications)): ?>
                <div class="empty-state"><div class="icon">🔔</div>You have no notifications.</div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-item">
                        <div class="notif-title"><?= sanitize($n['title']) ?></div>
                        <div><?= sanitize($n['message']) ?></div>
                        <div class="notif-date">By <?= sanitize($n['created_by'] ?? 'Admin') ?> &middot; <?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
