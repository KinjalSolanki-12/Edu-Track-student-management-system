<?php
/**
 * JSON attendance endpoint (student-facing).
 * GET api/attendance_api.php?course_id=1  -> returns the logged-in student's attendance records
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
$studentId = currentStudentId();
$courseId = (int) ($_GET['course_id'] ?? 0);

$sql = 'SELECT a.attendance_date, a.status, c.course_name FROM attendance a
        JOIN courses c ON c.id = a.course_id WHERE a.student_id = :id';
$params = ['id' => $studentId];
if ($courseId) {
    $sql .= ' AND a.course_id = :cid';
    $params['cid'] = $courseId;
}
$sql .= ' ORDER BY a.attendance_date DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
