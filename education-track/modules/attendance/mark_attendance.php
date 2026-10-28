<?php
/** Standalone module: Mark Attendance - delegates to admin/attendance.php (POST save_attendance) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/attendance.php');
exit;
