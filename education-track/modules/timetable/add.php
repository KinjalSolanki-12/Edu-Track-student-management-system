<?php
/** Standalone module: Add Timetable Entry - delegates to admin/timetable.php (POST action=create) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/timetable.php');
exit;
