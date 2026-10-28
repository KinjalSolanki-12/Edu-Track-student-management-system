<?php
/** Standalone module: Edit Student - delegates to admin/students.php (POST action=update) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/students.php');
exit;
