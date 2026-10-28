<?php
/** Standalone module: Submit Assignment (student) - delegates to student-facing assignments.php */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
requireStudentLogin();
header('Location: ' . BASE_URL . '/assignments.php');
exit;
