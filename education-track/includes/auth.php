<?php
/**
 * Authentication helpers & route guards
 * Requires session.php to already be included.
 */

function isStudentLoggedIn(): bool
{
    return isset($_SESSION['student_id']);
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']);
}

function requireStudentLogin(): void
{
    if (!isStudentLoggedIn()) {
        setFlash('error', 'Please login to continue.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        setFlash('error', 'Please login as admin to continue.');
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

function currentStudentId(): ?int
{
    return $_SESSION['student_id'] ?? null;
}

function currentStudentName(): string
{
    return $_SESSION['student_name'] ?? 'Student';
}

function currentAdminName(): string
{
    return $_SESSION['admin_name'] ?? 'Admin';
}

/**
 * Attempt student login. Returns true on success.
 */
function attemptStudentLogin(string $email, string $password): bool
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, full_name, email, password, status FROM students WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $student = $stmt->fetch();

    if ($student && $student['status'] === 'active' && password_verify($password, $student['password'])) {
        session_regenerate_id(true);
        $_SESSION['student_id']   = $student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        $_SESSION['student_email'] = $student['email'];
        return true;
    }
    return false;
}

/**
 * Attempt admin login. Returns true on success.
 */
function attemptAdminLogin(string $email, string $password): bool
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, username, full_name, email, password FROM admins WHERE email = :email OR username = :username LIMIT 1');
    $stmt->execute(['email' => $email, 'username' => $email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_email'] = $admin['email'];
        return true;
    }
    return false;
}
