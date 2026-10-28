<?php
/** Standalone module: View full timetable (read-only, all courses). Admin-facing. */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/timetable.php');
exit;
