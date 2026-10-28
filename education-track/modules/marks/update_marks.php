<?php
/** Standalone module: Update Marks - delegates to admin/marks.php (POST action=update) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/marks.php');
exit;
