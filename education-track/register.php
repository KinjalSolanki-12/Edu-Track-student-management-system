<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => '', 'dob' => '', 'gender' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $old['full_name'] = trim($_POST['full_name'] ?? '');
        $old['email']     = trim($_POST['email'] ?? '');
        $old['phone']     = trim($_POST['phone'] ?? '');
        $old['dob']       = trim($_POST['dob'] ?? '');
        $old['gender']    = trim($_POST['gender'] ?? '');
        $old['address']   = trim($_POST['address'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirmPassword  = $_POST['confirm_password'] ?? '';

        // Server-side validation
        if ($old['full_name'] === '') $errors[] = 'Full name is required.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if (!preg_match('/^[0-9]{10}$/', $old['phone'])) $errors[] = 'Phone number must be exactly 10 digits.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
        if (!in_array($old['gender'], ['Male', 'Female', 'Other'], true)) $errors[] = 'Please select a gender.';

        if (empty($errors)) {
            $pdo = getDBConnection();

            $check = $pdo->prepare('SELECT id FROM students WHERE email = :email');
            $check->execute(['email' => $old['email']]);
            if ($check->fetch()) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $rollNo = 'STU' . date('Y') . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare('INSERT INTO students (roll_no, full_name, email, password, phone, dob, gender, address)
                                        VALUES (:roll_no, :full_name, :email, :password, :phone, :dob, :gender, :address)');
                $stmt->execute([
                    'roll_no'   => $rollNo,
                    'full_name' => $old['full_name'],
                    'email'     => $old['email'],
                    'password'  => $hashedPassword,
                    'phone'     => $old['phone'],
                    'dob'       => $old['dob'] ?: null,
                    'gender'    => $old['gender'],
                    'address'   => $old['address'],
                ]);

                setFlash('success', 'Registration successful! Your roll number is ' . $rollNo . '. Please login.');
                header('Location: login.php');
                exit;
            }
        }
    }
}

$pageTitle = 'Student Registration';
$extraCss = ['login.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-box wide">
        <div class="auth-logo"><span class="brand-icon">🎓</span></div>
        <h1>Create Student Account</h1>
        <p class="auth-sub">Join Education Track to manage your academic journey.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div>• <?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" data-rule="required|min:2" value="<?= sanitize($old['full_name']) ?>">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" data-rule="required|email" value="<?= sanitize($old['email']) ?>">
                    <div class="form-error"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" data-rule="required|phone" value="<?= sanitize($old['phone']) ?>">
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control" value="<?= sanitize($old['dob']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control" data-rule="required">
                        <option value="">Select</option>
                        <option value="Male" <?= $old['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $old['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= $old['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?= sanitize($old['address']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group password-toggle">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" data-rule="required|min:6">
                    <span class="toggle-eye">👁️</span>
                    <div class="form-error"></div>
                </div>
                <div class="form-group password-toggle">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" data-rule="required|match:#password">
                    <span class="toggle-eye">👁️</span>
                    <div class="form-error"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <div class="auth-footer-link">Already have an account? <a href="login.php">Login here</a></div>
    </div>
</div>

<?php
$extraJs = ['validation.js'];
require_once __DIR__ . '/includes/footer.php';
?>
