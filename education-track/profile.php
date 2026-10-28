<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
requireStudentLogin();

$pdo = getDBConnection();
$studentId = currentStudentId();
$errors = [];

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute(['id' => $studentId]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $dob      = trim($_POST['dob'] ?? '');
        $gender   = trim($_POST['gender'] ?? '');
        $address  = trim($_POST['address'] ?? '');

        if ($fullName === '') $errors[] = 'Full name is required.';
        if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Phone number must be exactly 10 digits.';

        $profileImage = $student['profile_image'];

        // Handle profile picture upload
        if (!empty($_FILES['profile_image']['name'])) {
            $file = $_FILES['profile_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'File upload failed. Please try again.';
            } elseif (!in_array($ext, ALLOWED_IMAGE_TYPES, true)) {
                $errors[] = 'Only JPG, JPEG, PNG and GIF images are allowed.';
            } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
                $errors[] = 'Image must be smaller than 5MB.';
            } else {
                $newFileName = 'student_' . $studentId . '_' . time() . '.' . $ext;
                $destination = UPLOAD_DIR_PROFILE . $newFileName;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $profileImage = $newFileName;
                } else {
                    $errors[] = 'Could not save uploaded image.';
                }
            }
        }

        if (empty($errors)) {
            $update = $pdo->prepare('UPDATE students SET full_name = :full_name, phone = :phone, dob = :dob,
                                      gender = :gender, address = :address, profile_image = :profile_image WHERE id = :id');
            $update->execute([
                'full_name'     => $fullName,
                'phone'         => $phone,
                'dob'           => $dob ?: null,
                'gender'        => $gender,
                'address'       => $address,
                'profile_image' => $profileImage,
                'id'            => $studentId,
            ]);

            $_SESSION['student_name'] = $fullName;
            setFlash('success', 'Profile updated successfully.');
            header('Location: profile.php');
            exit;
        }
    }
}

// Password change (separate form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        if (!password_verify($currentPassword, $student['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $errors[] = 'New passwords do not match.';
        } else {
            $update = $pdo->prepare('UPDATE students SET password = :password WHERE id = :id');
            $update->execute(['password' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $studentId]);
            setFlash('success', 'Password changed successfully.');
            header('Location: profile.php');
            exit;
        }
    }
}

$flash = getFlash();
$pageTitle = 'My Profile';
$extraCss = ['dashboard.css', 'forms.css'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="app-layout">
    <?php $sidebarType = 'student'; $activePage = 'profile'; require __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><h1>My Profile</h1></div>

        <?php if (!empty($flash['success'])): ?>
            <div class="alert alert-success" data-autohide><?= sanitize($flash['success']) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php foreach ($errors as $e): ?><div>• <?= sanitize($e) ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">Basic Information</div>
            <form method="POST" action="profile.php" enctype="multipart/form-data" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="upload-preview">
                    <img id="profilePreview" class="avatar" style="width:70px;height:70px;"
                         src="<?= ($student['profile_image'] && $student['profile_image'] !== 'default.png') ? UPLOAD_URL_PROFILE . sanitize($student['profile_image']) : 'https://ui-avatars.com/api/?background=1e5eff&color=fff&name=' . urlencode($student['full_name']) ?>" alt="Profile photo">
                    <div>
                        <label for="profile_image" class="btn btn-secondary btn-sm">Change Photo</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" class="js-image-input" data-preview="profilePreview" style="display:none;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Roll Number</label>
                        <input type="text" class="form-control" value="<?= sanitize($student['roll_no']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="text" class="form-control" value="<?= sanitize($student['email']) ?>" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" data-rule="required" value="<?= sanitize($student['full_name']) ?>">
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" data-rule="required|phone" value="<?= sanitize($student['phone']) ?>">
                        <div class="form-error"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control" value="<?= sanitize((string) $student['dob']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $student['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?= sanitize($student['address']) ?>">
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">Change Password</div>
            <form method="POST" action="profile.php" data-validate novalidate>
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
require_once __DIR__ . '/includes/footer.php';
?>
