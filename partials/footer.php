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

    <script src="<?= BASE_PATH ?>/assets/js/gsap.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/Flip.min.js"></script>
    <script>
    (function() {
        var grid = document.getElementById('projects-grid');
        if (!grid) return;
        var cards = [];
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var hasGsap = !!(window.gsap && window.Flip);
        var mobileMq = window.matchMedia('(max-width: 768px)');
        var isMobile = mobileMq.matches;
        var timer = null;
        var busy = false;
        var shuffleOn = true;

        function visible() {
            return cards.filter(function(c) { return !c.classList.contains('hidden'); });
        }
        function swapNodes(a, b) {
            var ra = document.createTextNode('');
            var rb = document.createTextNode('');
            a.replaceWith(ra);
            b.replaceWith(rb);
            ra.replaceWith(b);
            rb.replaceWith(a);
        }
        function cleanup(involved) {
            involved.forEach(function(c) {
                c.style.transition = '';
                c.style.transform = '';
                c.style.filter = '';
                c.style.willChange = '';
                c.style.zIndex = '';
            });
        }
        function motionBlur(tween, involved) {
            tween.eventCallback('onUpdate', function() {
                var p = tween.progress();
                var blur = Math.round(Math.sin(p * Math.PI) * 3);
                for (var i = 0; i < involved.length; i++) {
                    involved[i].style.willChange = 'filter';
                    involved[i].style.filter = 'blur(' + blur + 'px)';
                }
            });
        }
        function run() {
            cards = Array.prototype.slice.call(grid.querySelectorAll('.project-card'));
            if (busy) { schedule(); return; }
            var items = visible();
            if (items.length < 2) { schedule(); return; }

            var n = items.length;
            var allowed = [4, 3, 2].filter(function(g) { return g < n; });
            if (allowed.length === 0) { schedule(); return; }
            var gap = allowed[Math.floor(Math.random() * allowed.length)];
            var start = Math.floor(Math.random() * (n - gap));
            var a = items[start];
            var b = items[start + gap];
            var involved = [a, b];

            if (reduceMotion) {
                swapNodes(a, b);
                schedule();
                return;
            }

            if (hasGsap) {
                gsap.killTweensOf(cards);
                cards.forEach(function(c) { gsap.set(c, { clearProps: 'transform,filter,zIndex,willChange,transition' }); });
                busy = true;
                involved.forEach(function(c) { c.style.transition = 'none'; c.style.zIndex = '10'; });
                var state = Flip.getState(involved);
                swapNodes(a, b);
                var flight = Flip.from(state, {
                    duration: 2.4,
                    ease: 'power3.inOut',
                    onComplete: function() {
                        involved.forEach(function(c) { gsap.set(c, { clearProps: 'transform,filter,zIndex,willChange,transition' }); });
                        busy = false;
                    }
                });
                motionBlur(flight, involved);
            } else {
                var ra = a.getBoundingClientRect();
                var rb = b.getBoundingClientRect();
                var dax = ra.left - rb.left;
                var day = ra.top - rb.top;
                busy = true;
                swapNodes(a, b);
                involved.forEach(function(c, k) {
                    c.style.transition = 'none';
                    c.style.transform = 'translate(' + (k === 0 ? dax : -dax) + 'px,' + (k === 0 ? day : -day) + 'px)';
                    c.style.filter = 'blur(3px)';
                    c.style.zIndex = '10';
                });
                void a.offsetWidth;
                involved.forEach(function(c) {
                    c.style.transition = 'transform 1.4s cubic-bezier(0.6, 0, 0.4, 1), filter 1.4s ease';
                    c.style.transform = 'translate(0,0)';
                    c.style.filter = 'blur(0)';
                });
                setTimeout(function() {
                    cleanup(involved);
                    busy = false;
                }, 1500);
            }
            schedule();
        }
        function schedule() {
            if (timer) clearTimeout(timer);
            timer = (isMobile || !shuffleOn) ? null : setTimeout(run, 3500 + Math.random() * 4000);
        }

        var showMoreBtn = document.getElementById('show-more-btn');
        if (showMoreBtn) {
            showMoreBtn.addEventListener('click', function() {
                setTimeout(function() {
                    cards = Array.prototype.slice.call(grid.querySelectorAll('.project-card'));
                    schedule();
                }, 350);
            });
        }

        var shuffleCheck = document.getElementById('shuffleCheck');
        if (shuffleCheck) {
            shuffleCheck.addEventListener('change', function() {
                shuffleOn = shuffleCheck.checked;
                if (shuffleOn) {
                    schedule();
                } else {
                    if (timer) { clearTimeout(timer); timer = null; }
                }
            });
        }

        if (mobileMq.addEventListener) {
            mobileMq.addEventListener('change', function(e) {
                isMobile = e.matches;
                if (isMobile) {
                    if (timer) { clearTimeout(timer); timer = null; }
                } else {
                    schedule();
                }
            });
        } else if (mobileMq.addListener) {
            mobileMq.addListener(function(e) {
                isMobile = e.matches;
                if (isMobile) {
                    if (timer) { clearTimeout(timer); timer = null; }
                } else {
                    schedule();
                }
            });
        }

        schedule();
    })();
    </script>
</body>
</html>
