<?php
/**
 * Standalone module: Add Student
 * This is a thin wrapper kept for the modular folder structure required by the spec.
 * The actual, fully-working implementation lives in admin/students.php (POST action=create),
 * which handles validation, duplicate-checking and password generation with prepared statements.
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/students.php');
exit;
