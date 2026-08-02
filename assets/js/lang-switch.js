// Smooth language switching (AJAX). No-ops when window.SITE.smoothLang is
// false, leaving the lang links to do a normal full-page navigation.
(function() {
    function isSmooth() {
        return !!(window.SITE && window.SITE.smoothLang);
    }

    function refreshSiteConfig(doc) {
        Array.prototype.forEach.call(doc.querySelectorAll('script'), function(s) {
            var t = s.textContent || '';
            if (t.indexOf('window.SITE') !== -1 && t.indexOf('basePath') !== -1) {
                try { (new Function(t))(); } catch (e) {}
            }
        });
    }

    window.swapLang = function swapLang(url, lang) {
        var sep = url.indexOf('?') === -1 ? '?' : '&';
        fetch(url + sep + 'ajax=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { if (!r.ok) throw new Error('lang'); return r.text(); })
            .then(function(html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                if (!doc.body || !doc.body.innerHTML) throw new Error('empty');
                refreshSiteConfig(doc);
                document.documentElement.lang = doc.documentElement.lang || lang;
                document.title = doc.title || document.title;
                document.body.innerHTML = doc.body.innerHTML;
                document.cookie = 'lang=' + lang + ';path=/;max-age=' + (60 * 60 * 24 * 365) + ';SameSite=Lax';
                history.replaceState({}, '', url);
                document.body.classList.remove('lang-anim');
                void document.body.offsetWidth;
                document.body.classList.add('lang-anim');
                initPage(true);
            })
            .catch(function() { window.location.href = url; });
    };

    window.bindLangSwitch = function bindLangSwitch() {
        if (!isSmooth()) return;
        Array.prototype.forEach.call(document.querySelectorAll('.lang-switch a'), function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                var url = a.getAttribute('href') || '';
                var lang = a.textContent.trim().toLowerCase();
                if (!url || !lang || lang === document.documentElement.lang) return;
                a.classList.add('switching');
                swapLang(url, lang);
            });
        });
    };
})();
