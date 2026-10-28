<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isStudentLoggedIn()) {
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
            $errors[] = 'Please enter both email and password.';
        } elseif (attemptStudentLogin($email, $password)) {
            setFlash('success', 'Welcome back, ' . currentStudentName() . '!');
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password, or account inactive.';
        }
    }
}

$flash = getFlash();
$pageTitle = 'Student Login';
$extraCss = ['login.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-logo"><span class="brand-icon">🎓</span></div>
        <h1>Student Login</h1>
        <p class="auth-sub">Welcome back! Please login to your account.</p>

        <?php if (!empty($flash['success'])): ?>
            <div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?><div>• <?= sanitize($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" data-rule="required|email" value="<?= sanitize($email) ?>">
                <div class="form-error"></div>
            </div>

            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" data-rule="required">
                <span class="toggle-eye">👁️</span>
                <div class="form-error"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div class="auth-footer-link">Don't have an account? <a href="register.php">Register here</a></div>
        <div class="auth-footer-link"><a href="admin/index.php">Login as Admin instead →</a></div>
    </div>
</div>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/includes/footer.php';
?>
