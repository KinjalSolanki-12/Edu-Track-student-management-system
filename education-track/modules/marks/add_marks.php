<?php
/** Standalone module: Add Marks - delegates to admin/marks.php (POST action=create) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/marks.php');
exit;
