<?php
/**
 * Global application configuration
 */

// Base URL - update to match your deployment
define('BASE_URL', '/education-track');

define('SITE_NAME', 'Education Track');
define('APP_VERSION', '1.0.0');

// Upload paths
define('UPLOAD_DIR_PROFILE', __DIR__ . '/../uploads/profile/');
define('UPLOAD_DIR_ASSIGNMENTS', __DIR__ . '/../uploads/assignments/');
define('UPLOAD_DIR_DOCUMENTS', __DIR__ . '/../uploads/documents/');

define('UPLOAD_URL_PROFILE', BASE_URL . '/uploads/profile/');
define('UPLOAD_URL_ASSIGNMENTS', BASE_URL . '/uploads/assignments/');

// Allowed upload file types
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'zip', 'txt', 'jpg', 'jpeg', 'png']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB

date_default_timezone_set('Asia/Kolkata');

// Basic error display toggle - set to false in production
define('APP_DEBUG', true);
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
