    <nav id="nav">
        <div class="nav-container">
            <div class="nav-links" id="navLinks">
                <?php if (($navHomeOnly ?? false) === true): ?>
                <a href="<?= BASE_PATH ?>/index.php">Trang chủ</a>
                <?php else: ?>
                <?php if (($settings['show_skills'] ?? '1') === '1' && count($skills) > 0): ?>
                <a href="#skills">Skills</a>
                <?php endif; ?>
                <?php if (($settings['show_projects'] ?? '1') === '1' && count($projects) > 0): ?>
                <a href="#projects">Projects</a>
                <?php endif; ?>
                <?php if (($settings['show_pricing'] ?? '1') === '1'): ?>
                <a href="#pricing">Pricing</a>
                <?php endif; ?>
                <?php if (($settings['show_faq'] ?? '1') === '1' && count($faqs) > 0): ?>
                <a href="#faq">FAQ</a>
                <?php endif; ?>
                <?php if (($settings['show_contact'] ?? '1') === '1'): ?>
                <a href="#contact">Contact</a>
                <?php endif; ?>
                <?php endif; ?>
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
