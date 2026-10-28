<?php
/**
 * Standalone module: Export attendance report for a course to CSV.
 * Usage: export.php?course_id=1
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

$pdo = getDBConnection();
$courseId = (int) ($_GET['course_id'] ?? 0);

if (!$courseId) {
    setFlash('error', 'Please select a course to export.');
    header('Location: ' . BASE_URL . '/admin/attendance.php');
    exit;
}

$stmt = $pdo->prepare("SELECT s.roll_no, s.full_name, a.attendance_date, a.status FROM attendance a
    JOIN students s ON s.id = a.student_id WHERE a.course_id = :cid ORDER BY a.attendance_date, s.full_name");
$stmt->execute(['cid' => $courseId]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Roll No', 'Full Name', 'Date', 'Status']);
foreach ($rows as $r) {
    fputcsv($out, [$r['roll_no'], $r['full_name'], $r['attendance_date'], $r['status']]);
}
fclose($out);
exit;
