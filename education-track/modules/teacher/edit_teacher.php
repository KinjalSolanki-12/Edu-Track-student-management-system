<?php
/** Standalone module: Edit Teacher - delegates to admin/teachers.php (POST action=update) */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
header('Location: ' . BASE_URL . '/admin/teachers.php');
exit;
