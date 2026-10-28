<?php
/**
 * Top navigation bar - adapts to guest / student / admin context.
 */
$isAdminArea = isAdminLoggedIn();
$isStudentArea = isStudentLoggedIn();
$navBase = $isAdminArea ? BASE_URL . '/admin' : BASE_URL;
?>
<header class="topnav">
    <div class="topnav-inner">
        <a class="brand" href="<?= $isAdminArea ? $navBase . '/dashboard.php' : BASE_URL . '/index.php' ?>">
            <span class="brand-icon">🎓</span>
            <span class="brand-text">Education Track</span>
        </a>

        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <nav class="topnav-links" id="topnavLinks">
            <?php if ($isStudentArea): ?>
                <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
                <span class="nav-user">👋 <?= sanitize(currentStudentName()) ?></span>
                <a href="<?= BASE_URL ?>/logout.php" class="btn-nav-logout">Logout</a>
            <?php elseif ($isAdminArea): ?>
                <a href="<?= $navBase ?>/dashboard.php">Dashboard</a>
                <a href="<?= $navBase ?>/notifications.php">Notifications</a>
                <span class="nav-user">🛡️ <?= sanitize(currentAdminName()) ?></span>
                <a href="<?= $navBase ?>/logout.php" class="btn-nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php">Home</a>
                <a href="<?= BASE_URL ?>/login.php">Student Login</a>
                <a href="<?= BASE_URL ?>/register.php">Register</a>
                <a href="<?= BASE_URL ?>/admin/index.php" class="btn-nav-admin">Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
