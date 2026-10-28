<?php
require_once __DIR__ . '/includes/session.php';

// Destroy the student session
unset($_SESSION['student_id'], $_SESSION['student_name'], $_SESSION['student_email']);
session_regenerate_id(true);

setFlash('success', 'You have been logged out successfully.');
header('Location: login.php');
exit;
