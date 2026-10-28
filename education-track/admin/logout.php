<?php
require_once __DIR__ . '/../includes/session.php';

unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
session_regenerate_id(true);

setFlash('success', 'You have been logged out successfully.');
header('Location: index.php');
exit;
