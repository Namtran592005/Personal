    <section id="contact" class="section">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="deco-heart" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet">
                <path fill="none" stroke="#ff6b9d" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round" d="M50 92 C36 80 8 60 8 33 C8 18 18 8 30 8 C38 8 46 13 50 20 C54 13 62 8 70 8 C82 8 92 18 92 33 C92 60 64 80 50 92 Z"/>
                <g fill="none" stroke="#ff6b9d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                   transform="translate(16 36) scale(0.36)">
                    <path d="M10 4 C10 4 8 22 8 30 C8 35 11 38 15 36"/>
                    <path d="M40 26 C40 33 35 38 28 38 C21 38 16 33 16 26 C16 19 21 14 28 14 C35 14 40 19 40 26 Z"/>
                    <path d="M46 15 L54 37 L62 15"/>
                    <path d="M67 27 L87 27 C87 19 81 14 74 14 C67 14 62 20 63 28 C64 35 70 40 77 39 C82 38 85 36 85 33"/>
                    <path d="M102 15 L110 33 M110 33 L118 15 M110 33 C108 39 106 44 101 44 C98 44 96 42 97 39"/>
                    <path d="M146 26 C146 33 141 38 134 38 C127 38 122 33 122 26 C122 19 127 14 134 14 C141 14 146 19 146 26 Z"/>
                    <path d="M152 15 L152 30 C152 35 156 38 160 38 C164 38 168 35 168 30 L168 15"/>
                </g>
            </svg>
        </div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label"><?= t('sec_contact_label') ?></p>
                <h2><?= t('sec_contact_title') ?></h2>
            </div>
            <div class="contact-grid no-form">
                <div class="contact-info fade-in">
                    <p class="contact-intro"><?= t('contact_intro') ?></p>
                    <div class="contact-links">
                        <div class="contact-item">
                            <i class="ph ph-envelope"></i>
                            <a href="mailto:<?= h($profile['email'] ?? '') ?>"><?= h($profile['email'] ?? '') ?></a>
                        </div>
                        <?php if (!empty($profile['phone'])): ?>
                        <div class="contact-item">
                            <i class="ph ph-phone"></i>
                            <span><?= h($profile['phone']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="contact-socials">
                        <?php
                        $socials = [
                            'facebook'  => ['label' => 'Facebook',  'icon' => 'ph-facebook-logo'],
                            'instagram' => ['label' => 'Instagram', 'icon' => 'ph-instagram-logo'],
                            'threads'   => ['label' => 'Threads',   'icon' => 'ph-threads-logo'],
                            'tiktok'    => ['label' => 'TikTok',    'icon' => 'ph-tiktok-logo'],
                            'github'    => ['label' => 'GitHub',    'icon' => 'ph-github-logo'],
                            'linkedin'  => ['label' => 'LinkedIn',  'icon' => 'ph-linkedin-logo'],
                        ];
                        foreach ($socials as $key => $s):
                            $url = $profile['social_' . $key] ?? '';
                            if (empty($url)) continue;
                        ?>
                        <a href="<?= h($url) ?>" target="_blank" rel="noopener" class="social-link">
                            <i class="ph <?= $s['icon'] ?>"></i>
                            <span><?= $s['label'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
