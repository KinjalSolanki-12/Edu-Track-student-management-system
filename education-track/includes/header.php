<?php
/**
 * Reusable page header.
 * Expects (optionally) before include:
 *   $pageTitle  - string, page title
 *   $extraCss   - array of extra css filenames in assets/css/
 *   $bodyClass  - string, extra class for <body>
 */
$pageTitle = $pageTitle ?? SITE_NAME;
$extraCss  = $extraCss ?? [];
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($pageTitle) ?> | <?= sanitize(SITE_NAME) ?></title>
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/logo.png">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<?php foreach ($extraCss as $css): ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= sanitize($css) ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
</head>
<body class="<?= sanitize($bodyClass) ?>">
