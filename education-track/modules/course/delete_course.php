<?php
/** Standalone module: Delete Course - delegates to admin/courses.php (POST action=delete) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/courses.php');
exit;
