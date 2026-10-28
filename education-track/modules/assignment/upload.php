<?php
/** Standalone module: Upload Assignment (admin) - delegates to admin/assignments.php (POST action=create) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/assignments.php');
exit;
