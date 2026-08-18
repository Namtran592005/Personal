    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle menu">
        <i class="ph ph-list"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-head">
            <h1>Dashboard</h1>
            <div class="sub">Admin Panel</div>
            <a href="<?= BASE_PATH ?>/index.php" class="sidebar-view-site" target="_blank" rel="noopener">View Site</a>        </div>
        <nav class="sidebar-nav">
            <a href="<?= BASE_PATH ?>/admin/dashboard.php" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                <i class="ph ph-chart-bar"></i> <span>Overview</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/profile.php" class="<?= $page === 'profile' ? 'active' : '' ?>">
                <i class="ph ph-user-circle"></i> <span>Profile</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/skills.php" class="<?= $page === 'skills' ? 'active' : '' ?>">
                <i class="ph ph-code"></i> <span>Skills</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/projects.php" class="<?= $page === 'projects' ? 'active' : '' ?>">
                <i class="ph ph-folder"></i> <span>Projects</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/experiences.php" class="<?= $page === 'experiences' ? 'active' : '' ?>">
                <i class="ph ph-briefcase"></i> <span>Experiences</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/pricing.php" class="<?= $page === 'pricing' ? 'active' : '' ?>">
                <i class="ph ph-currency-circle-dollar"></i> <span>Pricing</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/faqs.php" class="<?= $page === 'faqs' ? 'active' : '' ?>">
                <i class="ph ph-question"></i> <span>FAQs</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/analytics.php" class="<?= $page === 'analytics' ? 'active' : '' ?>">
                <i class="ph ph-graph"></i> <span>Analytics</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/pages.php" class="<?= $page === 'pages' ? 'active' : '' ?>">
                <i class="ph ph-file-text"></i> <span>Pages</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/videos.php" class="<?= $page === 'videos' ? 'active' : '' ?>">
                <i class="ph ph-video"></i> <span>Videos</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/settings.php" class="<?= $page === 'settings' ? 'active' : '' ?>">
                <i class="ph ph-gear"></i> <span>Settings</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/password.php" class="<?= $page === 'password' ? 'active' : '' ?>">
                <i class="ph ph-lock-key"></i> <span>Change Password</span>
            </a>
            <a href="<?= BASE_PATH ?>/admin/security.php" class="<?= $page === 'security' ? 'active' : '' ?>">
                <i class="ph ph-shield-check"></i> <span>Security</span>
            </a>
        </nav>
        <div class="sidebar-foot">
            <span class="user-name"><?= h($profile['name'] ?? 'Admin') ?></span>
            <a href="<?= BASE_PATH ?>/admin/logout.php" title="Sign out"><i class="ph ph-sign-out"></i></a>
        </div>
    </aside>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('open');
        document.getElementById('sidebarToggle').classList.toggle('open');
    }
    (function() {
        var nav = document.querySelector('.sidebar-nav');
        if (!nav) return;
        var KEY = 'adminSidebarScroll';
        var saved = sessionStorage.getItem(KEY);
        if (saved !== null) nav.scrollTop = parseInt(saved, 10) || 0;
        nav.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (a) sessionStorage.setItem(KEY, String(nav.scrollTop));
        });
    })();
    </script>
