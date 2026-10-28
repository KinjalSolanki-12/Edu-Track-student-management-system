<?php
/** Standalone module: Add Course - delegates to admin/courses.php (POST action=create) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/courses.php');
exit;
