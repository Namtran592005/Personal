    <section id="contact" class="section">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label">Contact</p>
                <h2>Liên hệ</h2>
            </div>
            <div class="contact-grid">
                <div class="contact-info fade-in">
                    <p class="contact-intro">Liên hệ qua các kênh bên dưới hoặc gửi tin nhắn trực tiếp.</p>
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
                    <h3>Send a Message</h3>
                    <div id="contactMsg"></div>
                    <form class="contact-form" id="contactForm">
                        <div class="anon-toggle">
                            <label class="anon-switch">
                                <input type="checkbox" name="anonymous" id="anonCheck" onchange="toggleAnon()">
                                <span class="anon-slider"></span>
                                <span class="anon-label">Send anonymously</span>
                            </label>
                        </div>
                        <div id="contactFields">
                            <div class="fg-group">
                                <input type="text" name="name" placeholder="Tên của bạn" required />
                                <input type="email" name="email" placeholder="Email của bạn" required />
                            </div>
                            <input type="text" name="subject" placeholder="Chủ đề (không bắt buộc)" />
                        </div>
                        <textarea name="message" placeholder="Nội dung tin nhắn..." required></textarea>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                <style>
                .anon-toggle { margin-bottom: 12px; }
                .anon-switch { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; }
                .anon-switch input { display: none; }
                .anon-slider {
                    position: relative; width: 40px; height: 22px; background: #d2d2d7; border-radius: 11px; transition: background 0.2s; flex-shrink: 0;
                }
                .anon-slider::after {
                    content: ''; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: transform 0.2s;
                }
                .anon-switch input:checked + .anon-slider { background: #1d1d1f; }
                .anon-switch input:checked + .anon-slider::after { transform: translateX(18px); }
                .anon-label { font-size: 13px; color: #86868b; font-weight: 500; }
                html.dark .anon-label { color: #a1a1a6; }
                html.dark .anon-switch input:checked + .anon-slider { background: #f5f5f7; }
                html.dark .anon-switch input:checked + .anon-slider::after { background: #1d1d1f; }
                </style>
                <script>
                function toggleAnon() {
                    const anon = document.getElementById('anonCheck').checked;
                    const fields = document.getElementById('contactFields');
                    const inputs = fields.querySelectorAll('input');
                    fields.style.display = anon ? 'none' : '';
                    inputs.forEach(i => i.required = !anon);
                }
                </script>
            </div>
        </div>
    </section>
