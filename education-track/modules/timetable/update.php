<?php
/**
 * Standalone module: Update a Timetable Entry.
 * Usage: POST id, course_id, day_of_week, start_time, end_time, room, csrf_token
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('UPDATE timetable SET course_id = :cid, day_of_week = :day, start_time = :start, end_time = :end, room = :room WHERE id = :id');
    $stmt->execute([
        'cid' => (int) $_POST['course_id'], 'day' => $_POST['day_of_week'],
        'start' => $_POST['start_time'], 'end' => $_POST['end_time'],
        'room' => trim($_POST['room'] ?? ''), 'id' => (int) $_POST['id'],
    ]);
    setFlash('success', 'Timetable entry updated.');
}
header('Location: ' . BASE_URL . '/admin/timetable.php');
exit;
