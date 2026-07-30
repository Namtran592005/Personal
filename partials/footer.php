    <button class="back-top-fab" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="ph ph-caret-up"></i></button>
    <footer>
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> <?= h($profile['name'] ?? 'Nam Trần') ?>. All rights reserved.</p>
            <div class="footer-right">
                <a href="<?= BASE_PATH ?>/admin/login.php" class="footer-admin">Admin</a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('contactForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const msg = document.getElementById('contactMsg');
            const btn = this.querySelector('button');
            const data = new FormData(this);
            btn.disabled = true; btn.textContent = 'Sending...';
            try {
                const r = await fetch('<?= BASE_PATH ?>/includes/contact-handler.php', { method: 'POST', body: data });
                const j = await r.json();
                msg.innerHTML = '<div class="form-msg ' + (j.ok ? 'ok' : 'err') + '">' + j.msg + '</div>';
                if (j.ok) { this.reset(); setTimeout(() => { msg.innerHTML = ''; }, 3000); }
            } catch(e) {
                msg.innerHTML = '<div class="form-msg err">Failed to send. Please try again.</div>';
            }
            btn.disabled = false; btn.textContent = 'Send Message';
        });

        const nav = document.getElementById('nav');
        const backFab = document.querySelector('.back-top-fab');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 40);
            if (backFab) backFab.classList.toggle('show', window.scrollY > 300);
        });

        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');
        const navOverlay = document.getElementById('navOverlay');
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            navOverlay.classList.toggle('open');
            const icon = navToggle.querySelector('i');
            icon.className = navLinks.classList.contains('open') ? 'ph ph-x' : 'ph ph-list-dashes';
        });
        navOverlay.addEventListener('click', () => {
            navLinks.classList.remove('open');
            navOverlay.classList.remove('open');
            navToggle.querySelector('i').className = 'ph ph-list-dashes';
        });
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('open');
                navOverlay.classList.remove('open');
                navToggle.querySelector('i').className = 'ph ph-list-dashes';
            });
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
