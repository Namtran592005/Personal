// Static-site app: client-side VI/EN switch + live GitHub projects.
// No backend: language is stored in localStorage, projects load from the
// public GitHub API with a bundled JSON snapshot as offline fallback.
(function () {
    'use strict';

    var STRINGS = {
        vi: {
            nav_home: 'Trang chủ',
            nav_projects: 'Projects',
            nav_faq: 'FAQ',
            nav_contact: 'Contact',
            hero_greeting: "Hi, I'm",
            hero_contact_me: 'Liên hệ với tôi',
            hero_view_projects: 'Xem dự án',
            hero_scroll: 'Cuộn',
            sec_projects_label: 'Projects',
            sec_projects_title: 'Dự án tiêu biểu',
            shuffle_effect: 'Hiệu ứng xáo trộn',
            view_on_github: 'Xem trên GitHub',
            show_more: 'Xem thêm',
            projects_loading: 'Đang tải dự án từ GitHub…',
            projects_error: 'Không tải được GitHub API — hiển thị bản lưu.',
            projects_cached: 'Bản lưu (cập nhật {date})',
            sec_faq_label: 'FAQ',
            sec_faq_title: 'Câu hỏi thường gặp',
            sec_contact_label: 'Contact',
            sec_contact_title: 'Liên hệ',
            contact_intro: 'Liên hệ qua các kênh bên dưới, tôi thường phản hồi trong vòng 24 giờ.',
            footer_rights: 'Bảo lưu mọi quyền.',
            footer_privacy: 'Chính Sách Quyền Riêng Tư',
            footer_terms: 'Điều Khoản Sử Dụng',
            footer_sitemap: 'Bản đồ trang web',
            call_fab_aria: 'Gọi liên hệ',
            meta_desc: 'Trang cá nhân của Nam Trần.',
            legal_updated: 'Cập nhật lần cuối:',
            privacy_label: 'Privacy Policy',
            privacy_title: 'Chính Sách Quyền Riêng Tư',
            terms_label: 'Terms of Service',
            terms_title: 'Điều Khoản Sử Dụng'
        },
        en: {
            nav_home: 'Home',
            nav_projects: 'Projects',
            nav_faq: 'FAQ',
            nav_contact: 'Contact',
            hero_greeting: "Hi, I'm",
            hero_contact_me: 'Contact Me',
            hero_view_projects: 'View Projects',
            hero_scroll: 'Scroll',
            sec_projects_label: 'Projects',
            sec_projects_title: 'Featured Projects',
            shuffle_effect: 'Shuffle effect',
            view_on_github: 'View on GitHub',
            show_more: 'Show More',
            projects_loading: 'Loading projects from GitHub…',
            projects_error: 'GitHub API unreachable — showing cached list.',
            projects_cached: 'Cached snapshot ({date})',
            sec_faq_label: 'FAQ',
            sec_faq_title: 'Frequently Asked Questions',
            sec_contact_label: 'Contact',
            sec_contact_title: 'Get in Touch',
            contact_intro: 'Reach me through the channels below — I typically respond within 24 hours.',
            footer_rights: 'All rights reserved.',
            footer_privacy: 'Privacy Policy',
            footer_terms: 'Terms of Service',
            footer_sitemap: 'Sitemap',
            call_fab_aria: 'Call now',
            meta_desc: "Nam Tran's personal website.",
            legal_updated: 'Last updated:',
            privacy_label: 'Privacy Policy',
            privacy_title: 'Privacy Policy',
            terms_label: 'Terms of Service',
            terms_title: 'Terms of Service'
        }
    };

    var PAGE_TITLES = {
        index: { vi: 'Nam Trần — Personal Website', en: 'Nam Tran — Personal Website' },
        privacy: { vi: 'Chính Sách Quyền Riêng Tư — Nam Trần', en: 'Privacy Policy — Nam Tran' },
        terms: { vi: 'Điều Khoản Sử Dụng — Nam Trần', en: 'Terms of Service — Nam Tran' },
        sitemap: { vi: 'Bản đồ trang web — Nam Trần', en: 'Sitemap — Nam Tran' },
        notfound: { vi: 'Không tìm thấy trang — Nam Trần', en: 'Page not found — Nam Tran' }
    };

    var GH_USER = 'namtran592005';
    var GH_API = 'https://api.github.com/users/' + GH_USER + '/repos?per_page=50&sort=updated';
    var CACHE_KEY = 'gh_repos_cache_v1';
    var CACHE_TTL = 30 * 60 * 1000; // 30 minutes, mirrors the PHP cache TTL
    var INITIAL = 6;

    function currentLang() {
        var l = null;
        try { l = localStorage.getItem('lang'); } catch (e) {}
        return l === 'en' ? 'en' : 'vi';
    }

    function setLang(lang) {
        lang = lang === 'en' ? 'en' : 'vi';
        try { localStorage.setItem('lang', lang); } catch (e) {}
        var s = STRINGS[lang];
        document.documentElement.lang = lang;

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n]'), function (el) {
            var k = el.getAttribute('data-i18n');
            if (s[k] !== undefined) el.textContent = s[k];
        });

        Array.prototype.forEach.call(document.querySelectorAll('[data-i18n-aria]'), function (el) {
            var k = el.getAttribute('data-i18n-aria');
            if (s[k] !== undefined) el.setAttribute('aria-label', s[k]);
        });

        var meta = document.querySelector('meta[name="description"]');
        if (meta) meta.setAttribute('content', s.meta_desc);

        var page = document.body.getAttribute('data-page') || 'index';
        if (PAGE_TITLES[page]) document.title = PAGE_TITLES[page][lang];

        Array.prototype.forEach.call(document.querySelectorAll('.lang-switch a'), function (a) {
            var on = (a.getAttribute('data-lang') === lang);
            if (on) a.classList.add('on');
            else a.classList.remove('on');
        });

        Array.prototype.forEach.call(document.querySelectorAll('.lang-block'), function (b) {
            b.hidden = (b.getAttribute('data-lang-block') !== lang);
        });

        // Re-render dynamic project buttons in the new language.
        if (window.__projects) renderProjects(window.__projects, window.__projectsIsFallback === true);
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function cardHtml(p, lang) {
        var s = STRINGS[lang];
        var stars = p.stars > 0
            ? '<span class="project-stars"><i class="ph ph-star"></i> ' + p.stars + '</span>' : '';
        var tech = p.tech_stack
            ? '<div class="project-tech"><span>' + esc(p.tech_stack) + '</span></div>' : '';
        var link = p.github_url
            ? '<a href="' + esc(p.github_url) + '" class="project-github" target="_blank" rel="noopener">' +
              '<i class="ph ph-github-logo"></i> ' + esc(s.view_on_github) + '</a>' : '';
        return '<div class="project-card fade-in visible"><div class="project-body">' +
            '<div class="project-head"><h4>' + esc(p.title) + '</h4>' + stars + '</div>' +
            '<p>' + esc(p.description) + '</p>' + tech + link + '</div></div>';
    }

    function renderProjects(list, isFallback) {
        var grid = document.getElementById('projects-grid');
        if (!grid) return;
        window.__projects = list;
        window.__projectsIsFallback = isFallback;
        var lang = currentLang();
        var s = STRINGS[lang];
        var html = '';
        for (var i = 0; i < list.length; i++) {
            var hidden = i >= INITIAL ? ' hidden' : '';
            html += cardHtml(list[i], lang).replace('project-card fade-in visible', 'project-card fade-in visible' + hidden);
        }
        grid.innerHTML = html;

        var oldWrap = document.querySelector('.show-more-wrap');
        if (oldWrap && oldWrap.parentNode) oldWrap.parentNode.removeChild(oldWrap);
        var oldNote = document.getElementById('projects-note');
        if (oldNote && oldNote.parentNode) oldNote.parentNode.removeChild(oldNote);

        if (isFallback) {
            var note = document.createElement('p');
            note.id = 'projects-note';
            note.className = 'projects-note';
            var label = s.projects_cached.replace('{date}', window.__projectsDate || '');
            note.textContent = s.projects_error + (window.__projectsDate ? ' ' + label : '');
            grid.parentNode.insertBefore(note, grid);
        }

        if (list.length > INITIAL) {
            var wrap = document.createElement('div');
            wrap.className = 'show-more-wrap';
            var btn = document.createElement('button');
            btn.id = 'show-more-btn';
            btn.className = 'btn btn-outline';
            btn.textContent = s.show_more + ' (' + (list.length - INITIAL) + ')';
            btn.addEventListener('click', function () {
                Array.prototype.forEach.call(grid.querySelectorAll('.project-card.hidden'), function (c) {
                    c.classList.remove('hidden');
                });
                if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
            });
            wrap.appendChild(btn);
            grid.parentNode.insertBefore(wrap, grid.nextSibling);
        }

        if (typeof window.bindFadeIn === 'function') window.bindFadeIn();
    }

    function loadProjects() {
        var grid = document.getElementById('projects-grid');
        if (!grid) return;
        var lang = currentLang();
        grid.innerHTML = '<p class="projects-note">' + esc(STRINGS[lang].projects_loading) + '</p>';

        function fromCache() {
            try {
                var raw = localStorage.getItem(CACHE_KEY);
                if (!raw) return null;
                var c = JSON.parse(raw);
                if (!c || !c.time || !c.repos) return null;
                if (Date.now() - c.time > CACHE_TTL) return null;
                return c.repos;
            } catch (e) { return null; }
        }

        function useFallback(reason) {
            var cached = fromCache();
            if (cached && cached.length) {
                renderProjects(cached, true);
                return;
            }
            fetch('data/projects-fallback.json', { cache: 'no-cache' })
                .then(function (r) { return r.json(); })
                .then(function (repos) { renderProjects(normalize(repos), true); })
                .catch(function () {
                    grid.innerHTML = '<p class="projects-note">' + esc(STRINGS[currentLang()].projects_error) + '</p>';
                });
        }

        function normalize(repos) {
            return repos
                .filter(function (r) { return r && !r.fork; })
                .map(function (r) {
                    return {
                        title: r.name || r.title || '',
                        description: r.description || '',
                        tech_stack: r.language || r.tech_stack || '',
                        github_url: r.html_url || r.github_url || '',
                        stars: r.stargazers_count != null ? r.stargazers_count : (r.stars || 0)
                    };
                });
        }

        var cached = fromCache();
        if (cached && cached.length) {
            renderProjects(cached, false);
        }

        var ctrl = null;
        try { ctrl = new AbortController(); } catch (e) {}
        var timer = setTimeout(function () { if (ctrl) ctrl.abort(); }, 10000);

        fetch(GH_API, {
            headers: { Accept: 'application/vnd.github+json' },
            signal: ctrl ? ctrl.signal : undefined
        })
            .then(function (r) { if (!r.ok) throw new Error('github ' + r.status); return r.json(); })
            .then(function (repos) {
                clearTimeout(timer);
                var list = normalize(repos);
                if (!list.length) throw new Error('empty');
                try { localStorage.setItem(CACHE_KEY, JSON.stringify({ time: Date.now(), repos: list })); } catch (e) {}
                renderProjects(list, false);
            })
            .catch(function () {
                clearTimeout(timer);
                if (!(cached && cached.length)) useFallback();
            });
    }

    function bindLangSwitch() {
        Array.prototype.forEach.call(document.querySelectorAll('.lang-switch a'), function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                setLang(a.getAttribute('data-lang'));
            });
        });
    }

    function boot() {
        var year = document.getElementById('year');
        if (year) year.textContent = new Date().getFullYear();
        bindLangSwitch();
        setLang(currentLang());
        loadProjects();
    }

    window.setLang = setLang;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
