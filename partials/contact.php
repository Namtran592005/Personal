    <section id="contact" class="section">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="deco-heart" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet">
                <path fill="none" stroke="#ff6b9d" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round" d="M50 92 C36 80 8 60 8 33 C8 18 18 8 30 8 C38 8 46 13 50 20 C54 13 62 8 70 8 C82 8 92 18 92 33 C92 60 64 80 50 92 Z"/>
                <g fill="none" stroke="#ff6b9d" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" transform="translate(48 52) scale(0.6) translate(-48 -52)">
                    <path d="M11 56 C10 48 10 38 11 30 C12 23 15 19 17 22 C19 25 18 31 17 37 C16 43 15 49 15 55 C15 58 17 60 20 58"/>
                    <path d="M22 57 C25 55 27 52 27 48 C27 43 24 40 20 41 C16 42 15 46 16 50 C17 54 20 57 24 56 C26 55 27 52 27 50"/>
                    <path d="M29 46 C32 52 35 58 38 59 C40 59 41 56 41 52 C41 49 42 46 44 44"/>
                    <path d="M45 45 C47 42 50 41 52 43 C54 45 53 48 50 49 C47 50 45 47 46 51 C47 55 49 58 52 57 C54 56 55 54 56 52"/>
                    <path d="M59 46 C61 42 64 40 66 43 C68 46 67 50 65 51 C63 52 61 51 62 56 C63 62 64 69 63 75 C62 80 58 83 54 82 C51 81 50 78 51 75 C52 71 54 69 56 70"/>
                    <path d="M70 52 C73 51 76 53 76 57 C76 62 72 65 68 64 C65 63 64 59 65 56 C66 52 69 50 73 51 C75 52 76 54 76 57"/>
                    <path d="M78 53 C80 56 83 59 84 64 C85 68 82 72 78 72 C74 72 72 68 72 63 C72 58 75 52 78 48 C80 45 83 44 85 45 C87 46 88 47 88 49"/>
                </g>
            </svg>
        </div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label"><?= t('sec_contact_label') ?></p>
                <h2><?= t('sec_contact_title') ?></h2>
            </div>
            <div class="contact-grid">
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
                <div class="contact-form-wrap fade-in">
                    <h3><?= t('send_message') ?></h3>
                    <form class="contact-form" id="contactForm">
                        <div class="anon-toggle">
                            <label class="anon-switch">
                                <input type="checkbox" name="anonymous" id="anonCheck" onchange="toggleAnon()">
                                <span class="anon-slider"></span>
                                <span class="anon-label"><?= t('send_anon') ?></span>
                            </label>
                        </div>
                        <div id="contactFields">
                            <div class="fg-group">
                                <input type="text" name="name" placeholder="<?= h(t('name_ph')) ?>" required />
                                <input type="email" name="email" placeholder="<?= h(t('email_ph')) ?>" required />
                            </div>
                            <input type="text" name="subject" placeholder="<?= h(t('subject_ph')) ?>" />
                        </div>
                        <textarea name="message" placeholder="<?= h(t('message_ph')) ?>" required></textarea>
                        <button type="submit" class="btn btn-primary"><?= t('send_btn') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </section>
