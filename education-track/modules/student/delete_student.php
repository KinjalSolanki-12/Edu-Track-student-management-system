<?php
/** Standalone module: Delete Student - delegates to admin/students.php (POST action=delete) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/students.php');
exit;
