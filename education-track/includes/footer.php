<?php
/**
 * Reusable page footer.
 * Expects (optionally) before include: $extraJs - array of extra js filenames in assets/js/
 */
$extraJs = $extraJs ?? [];
?>
    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> <?= sanitize(SITE_NAME) ?>. All rights reserved.</p>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    <?php foreach ($extraJs as $js): ?>
    <script src="<?= BASE_URL ?>/assets/js/<?= sanitize($js) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
