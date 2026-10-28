<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
            ON DUPLICATE KEY UPDATE setting_value = :v2');
        foreach (['site_name', 'academic_year', 'institution_name', 'contact_email', 'max_upload_size_mb'] as $key) {
            $value = trim($_POST[$key] ?? '');
            $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
        }
        setFlash('success', 'Settings updated successfully.');
    }
    header('Location: settings.php');
    exit;
}

// Admin password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $stmt = $pdo->prepare('SELECT password FROM admins WHERE id = :id');
        $stmt->execute(['id' => $_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new_password'] ?? '';

        if (!password_verify($current, $admin['password'])) {
            setFlash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            setFlash('error', 'New password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            setFlash('error', 'New passwords do not match.');
        } else {
            $update = $pdo->prepare('UPDATE admins SET password = :password WHERE id = :id');
            $update->execute(['password' => password_hash($new, PASSWORD_DEFAULT), 'id' => $_SESSION['admin_id']]);
            setFlash('success', 'Password changed successfully.');
        }
    }
    header('Location: settings.php');
    exit;
}

$rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
$settings = [];
foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];

$flash = getFlash();
$pageTitle = 'System Settings';
$extraCss = ['admin.css', 'forms.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'admin'; $activePage = 'settings'; require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>System Settings</h1></div>

        <?php if (!empty($flash['success'])): ?><div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div><?php endif; ?>
        <?php if (!empty($flash['error'])): ?><div class="alert alert-error" data-autohide><?= sanitize($flash['error']) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-title">General Settings</div>
            <form method="POST" action="settings.php" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="save_settings" value="1">

                <div class="form-row">
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" class="form-control" value="<?= sanitize($settings['site_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <input type="text" id="academic_year" name="academic_year" class="form-control" value="<?= sanitize($settings['academic_year'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="institution_name">Institution Name</label>
                        <input type="text" id="institution_name" name="institution_name" class="form-control" value="<?= sanitize($settings['institution_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" data-rule="email" value="<?= sanitize($settings['contact_email'] ?? '') ?>">
                        <div class="form-error"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="max_upload_size_mb">Max Upload Size (MB)</label>
                    <input type="number" id="max_upload_size_mb" name="max_upload_size_mb" class="form-control" value="<?= sanitize($settings['max_upload_size_mb'] ?? '5') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">Change Admin Password</div>
            <form method="POST" action="settings.php" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="change_password" value="1">

                <div class="form-row">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" data-rule="required">
                        <div class="form-error"></div>
                    </div>
                    <div></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" data-rule="required|min:6">
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" data-rule="required|match:#new_password">
                        <div class="form-error"></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary">Update Password</button>
            </form>
        </div>
    </main>
</div>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
