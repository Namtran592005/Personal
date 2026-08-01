    <nav id="nav">
        <div class="nav-container">
            <div class="nav-links" id="navLinks">
                <?php if (($navHomeOnly ?? false) === true): ?>
                <a href="<?= BASE_PATH ?>/index.php"><?= t('nav_home') ?></a>
                <?php else: ?>
                <?php if (($settings['show_skills'] ?? '1') === '1' && count($skills) > 0): ?>
                <a href="#skills"><?= t('nav_skills') ?></a>
                <?php endif; ?>
                <?php if (($settings['show_experience'] ?? '1') === '1' && count($experiences) > 0): ?>
                <a href="#experience"><?= t('nav_experience') ?></a>
                <?php endif; ?>
                <?php if (($settings['show_projects'] ?? '1') === '1' && count($projects) > 0): ?>
                <a href="#projects"><?= t('nav_projects') ?></a>
                <?php endif; ?>
                <?php if (($settings['show_pricing'] ?? '1') === '1'): ?>
                <a href="#pricing"><?= t('nav_pricing') ?></a>
                <?php endif; ?>
                <?php if (($settings['show_faq'] ?? '1') === '1' && count($faqs) > 0): ?>
                <a href="#faq"><?= t('nav_faq') ?></a>
                <?php endif; ?>
                <?php if (($settings['show_contact'] ?? '1') === '1'): ?>
                <a href="#contact"><?= t('nav_contact') ?></a>
                <?php endif; ?>
                <?php endif; ?>
                <span class="lang-switch" id="langSwitch">
                    <a href="<?= langUrl('vi') ?>" class="<?= $LANG === 'vi' ? 'on' : '' ?>">VI</a>
                    <span class="sep">|</span>
                    <a href="<?= langUrl('en') ?>" class="<?= $LANG === 'en' ? 'on' : '' ?>">EN</a>
                </span>
            </div>
            <button class="dark-toggle" onclick="toggleDark()" aria-label="Toggle dark mode">
                <i class="ph ph-moon"></i>
                <i class="ph ph-sun"></i>
            </button>
            <button class="nav-toggle" id="navToggle" aria-label="Menu">
                <i class="ph ph-list-dashes"></i>
            </button>
        </div>
        <div class="nav-overlay" id="navOverlay"></div>
    </nav>
