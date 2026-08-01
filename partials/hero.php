    <section id="hero">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="<?= BASE_PATH ?>/assets/video/hero.mp4" type="video/mp4" />
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <img src="<?= BASE_PATH ?>/<?= h($profile['avatar'] ?: 'media/avt.png') ?>" alt="<?= h($profile['name'] ?? 'Avatar') ?>" class="hero-avatar" onerror="this.src='<?= BASE_PATH ?>/media/avt.png'" />
            <p class="hero-greeting"><?= t('hero_greeting') ?></p>
            <h1 class="hero-name"><?= h($profile['name'] ?? 'Nam Trần') ?></h1>
            <p class="hero-title"><?= h($profile['title'] ?? 'Developer & Designer') ?></p>
            <?php if (!empty($profile['bio'])): ?>
            <p class="hero-desc"><?= h($profile['bio']) ?></p>
            <?php endif; ?>
            <div class="hero-cta">
                <a href="#contact" class="btn btn-primary">
                    <?= t('hero_contact_me') ?> <i class="ph ph-paper-plane-right"></i>
                </a>
                <a href="#projects" class="btn btn-outline">
                    <?= t('hero_view_projects') ?>
                </a>
            </div>
        </div>
        <div class="hero-scroll">
            <span><?= t('hero_scroll') ?></span>
            <i class="ph ph-caret-down"></i>
        </div>
    </section>
