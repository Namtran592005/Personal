    <?php if (($settings['show_call_fab'] ?? '1') === '1' && !empty($profile['phone'])): ?>
    <a class="call-fab" href="tel:<?= h(preg_replace('/[^\d+]/', '', $profile['phone'])) ?>" aria-label="<?= h(t('call_fab_aria')) ?>"><i class="ph ph-phone-call"></i></a>
    <?php endif; ?>
    <?php if (($settings['show_back_top'] ?? '1') === '1'): ?>
    <button class="back-top-fab" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="ph ph-caret-up"></i></button>
    <?php endif; ?>
    <div id="toast" aria-live="polite"></div>
    <footer>
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> <?= h($profile['name'] ?? '') ?>. <?= t('footer_rights') ?></p>
            <div class="footer-right">
                <a href="<?= BASE_PATH ?>/privacy.php" class="footer-link"><?= t('footer_privacy') ?></a>
                <a href="<?= BASE_PATH ?>/terms.php" class="footer-link"><?= t('footer_terms') ?></a>
                <a href="<?= BASE_PATH ?>/sitemap.php" class="footer-link"><?= t('footer_sitemap') ?></a>
                <a href="<?= BASE_PATH ?>/admin/login.php" class="footer-admin"><?= t('footer_admin') ?></a>
            </div>
        </div>
    </footer>

    <script>
        window.SITE = {
            basePath: '<?= BASE_PATH ?>',
            smoothLang: <?= ($settings['smooth_lang_switch'] ?? '1') === '1' ? 'true' : 'false' ?>,
            shuffleMobile: <?= ($settings['shuffle_on_mobile'] ?? '1') === '1' ? 'true' : 'false' ?>,
            beaconMax: <?= BEACON_MAX_SECONDS ?>
        };
    </script>
    <?php if (($settings['enable_analytics'] ?? '1') === '1' && ($_GET['ajax'] ?? '0') !== '1'): ?>
    <script src="<?= BASE_PATH ?>/assets/js/analytics.js"></script>
    <?php endif; ?>
    <script src="<?= BASE_PATH ?>/assets/js/gsap.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/Flip.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/ui.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/lang-switch.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/shuffle.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/main.js"></script>
</body>
</html>
