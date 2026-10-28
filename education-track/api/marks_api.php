<?php
/**
 * JSON marks endpoint (student-facing).
 * GET api/marks_api.php -> returns the logged-in student's marks records
 */
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isStudentLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT m.exam_type, m.marks_obtained, m.total_marks, m.grade, c.course_name, c.course_code
    FROM marks m JOIN courses c ON c.id = m.course_id WHERE m.student_id = :id ORDER BY c.course_name');
$stmt->execute(['id' => currentStudentId()]);

echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
