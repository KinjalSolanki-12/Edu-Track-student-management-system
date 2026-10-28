<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = 'Please enter both email/username and password.';
        } elseif (attemptAdminLogin($email, $password)) {
            setFlash('success', 'Welcome back, ' . currentAdminName() . '!');
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}

$pageTitle = 'Admin Login';
$extraCss = ['login.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-logo"><span class="brand-icon">🛡️</span></div>
        <h1>Admin Login</h1>
        <p class="auth-sub">Sign in to manage Education Track.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><div>• <?= sanitize($e) ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="email">Email or Username</label>
                <input type="text" id="email" name="email" class="form-control" data-rule="required" value="<?= sanitize($email) ?>">
                <div class="form-error"></div>
            </div>

            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" data-rule="required">
                <span class="toggle-eye">👁️</span>
                <div class="form-error"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login as Admin</button>
        </form>

        <div class="auth-footer-link"><a href="../login.php">← Back to Student Login</a></div>
        <p class="text-center" style="font-size:.78rem; margin-top:1rem;">Demo credentials: admin@edutrack.com / admin123</p>
    </div>
</div>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
