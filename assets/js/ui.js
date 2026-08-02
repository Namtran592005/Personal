// Core UI: toast, contact form, nav, fade-in reveal, and the initPage() boot
// that re-binds listeners after a smooth language-switch body swap.
(function() {
    window.showToast = function showToast(text, ok) {
        var t = document.getElementById('toast');
        if (!t) return;
        t.textContent = text;
        t.className = ok ? 'ok' : 'err';
        void t.offsetWidth;
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(function() { t.classList.remove('show'); }, 3000);
    };

    // Anonymous contact form helper (used by inline onchange, survives body swaps).
    window.toggleAnon = function toggleAnon() {
        var anon = document.getElementById('anonCheck');
        var fields = document.getElementById('contactFields');
        if (!anon || !fields) return;
        var inputs = fields.querySelectorAll('input');
        fields.classList.toggle('collapsed', anon.checked);
        Array.prototype.forEach.call(inputs, function(i) {
            if (i.name === 'subject') { i.required = false; return; }
            i.required = !anon.checked;
        });
    };

    window.fitContact = function fitContact() {
        var anon = document.getElementById('anonCheck');
        var form = document.getElementById('contactForm');
        if (anon && form && !anon.checked) form.style.minHeight = form.offsetHeight + 'px';
    };

    // Global (once-only) window listeners that always query live DOM nodes.
    var __boundGlobal = false;
    window.bindGlobal = function bindGlobal() {
        if (__boundGlobal) return;
        __boundGlobal = true;
        window.addEventListener('scroll', function() {
            var nav = document.getElementById('nav');
            var backFab = document.querySelector('.back-top-fab');
            if (nav) nav.classList.toggle('scrolled', window.scrollY > 40);
            if (backFab) backFab.classList.toggle('show', window.scrollY > 300);
        });
        window.addEventListener('resize', fitContact);
    };

    var __fadeObserver = null;
    window.bindFadeIn = function bindFadeIn(force) {
        if (__fadeObserver) __fadeObserver.disconnect();
        __fadeObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach(function(el) {
            if (force) { el.classList.add('visible'); return; }
            var r = el.getBoundingClientRect();
            if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('visible');
            __fadeObserver.observe(el);
        });
    };

    window.bindNav = function bindNav() {
        var navToggle = document.getElementById('navToggle');
        var navLinks = document.getElementById('navLinks');
        var navOverlay = document.getElementById('navOverlay');
        if (!navToggle || !navLinks) return;
        function closeMenu() {
            navLinks.classList.remove('open');
            if (navOverlay) navOverlay.classList.remove('open');
            var icon = navToggle.querySelector('i');
            if (icon) icon.className = 'ph ph-list-dashes';
        }
        navToggle.addEventListener('click', function() {
            var open = navLinks.classList.toggle('open');
            if (navOverlay) navOverlay.classList.toggle('open', open);
            var icon = navToggle.querySelector('i');
            if (icon) icon.className = open ? 'ph ph-x' : 'ph ph-list-dashes';
        });
        if (navOverlay) navOverlay.addEventListener('click', closeMenu);
        Array.prototype.forEach.call(document.querySelectorAll('.nav-links a'), function(link) {
            link.addEventListener('click', closeMenu);
        });
    };

    window.bindContact = function bindContact() {
        var form = document.getElementById('contactForm');
        if (!form) return;
        var btn = form.querySelector('button');
        var langUI = (window.SITE && window.SITE.langUI) || {};
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var data = new FormData(form);
            btn.disabled = true; btn.textContent = langUI.sending || 'Sending…';
            fetch(window.SITE.basePath + '/includes/contact-handler.php', { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .catch(function() { return { ok: false }; })
                .then(function(j) {
                    showToast(j.ok ? langUI.sendSuccess : langUI.sendFail, !!j.ok);
                    if (j.ok) form.reset();
                    btn.disabled = false; btn.textContent = langUI.sendBtn || 'Send';
                });
        });
        fitContact();
    };

    window.initPage = function initPage(forceFade) {
        bindGlobal();
        bindNav();
        bindContact();
        bindFadeIn(!!forceFade);
        if (typeof window.bindLangSwitch === 'function') bindLangSwitch();
        if (typeof window.initShuffle === 'function') initShuffle();
    };
})();
