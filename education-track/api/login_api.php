<?php
/**
 * JSON login endpoint - accepts POST {email, password, role} and returns JSON.
 * Useful for AJAX-based login forms or mobile clients.
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$role = $input['role'] ?? 'student';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

if ($role === 'admin') {
    $ok = attemptAdminLogin($email, $password);
} else {
    $ok = attemptStudentLogin($email, $password);
}

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Login successful.', 'csrf_token' => csrfToken()]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
}
