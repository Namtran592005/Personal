// Core UI: toast, nav, fade-in reveal, and the initPage() boot
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

    window.initPage = function initPage(forceFade) {
        bindGlobal();
        bindNav();
        bindFadeIn(!!forceFade);
        if (typeof window.bindLangSwitch === 'function') bindLangSwitch();
        if (typeof window.initShuffle === 'function') initShuffle();
    };
})();
