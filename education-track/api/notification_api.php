<?php
/**
 * JSON notifications endpoint.
 * GET api/notification_api.php -> returns notifications relevant to the current logged-in user.
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isStudentLoggedIn() && !isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$pdo = getDBConnection();

if (isAdminLoggedIn()) {
    $stmt = $pdo->query('SELECT * FROM notifications ORDER BY created_at DESC');
} else {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE target_audience IN ('all','students') ORDER BY created_at DESC");
    $stmt->execute();
}

echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
