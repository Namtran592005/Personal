<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$profile = ['name' => 'Admin'];
if ($dbAvailable) {
    try {
        $dbp = $pdo->query("SELECT * FROM profile WHERE id = 1")->fetch();
        if ($dbp) $profile = $dbp;
    } catch (PDOException $e) {}
}

$msg = null;

// Export
if (isset($_GET['export'])) {
    if (!validateCsrfToken($_GET['_csrf'] ?? null)) { http_response_code(403); exit; }
    $data = [];
    $tables = ['profile','skills','projects','experiences','messages','faqs','analytics','settings'];
    foreach ($tables as $t) {
        try { $data[$t] = $pdo->query("SELECT * FROM \"$t\"")->fetchAll(); } catch (PDOException $e) { $data[$t] = []; }
    }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="export-' . date('Y-m-d') . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

$csrfOk = validateCsrfToken($_POST['_csrf'] ?? null);

// Toggle settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!$csrfOk) { $msg = 'Invalid request.'; } else {
    $keys = ['show_skills','show_projects','show_faq','show_contact','enable_analytics','enable_contact_form'];
    foreach ($keys as $k) {
        $v = isset($_POST[$k]) ? '1' : '0';
        $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")->execute([$k, $v]);
        $settings[$k] = $v;
    }
    $msg = 'Settings saved.';
    }
}

// Reset GitHub cache
if (isset($_POST['reset_github_cache'])) {
    if (!$csrfOk) { $msg = 'Invalid request.'; } else {
    $cacheFile = __DIR__ . '/../cache/github_repos.json';
    if (file_exists($cacheFile)) @unlink($cacheFile);
    $msg = 'GitHub cache cleared.';
    }
}

// Reset analytics
if (isset($_POST['reset_analytics'])) {
    if (!$csrfOk) { $msg = 'Invalid request.'; } else {
    try { $pdo->exec("DELETE FROM analytics"); } catch (PDOException $e) {}
    $msg = 'Analytics data cleared.';
    }
}

// Reset all database
if (isset($_POST['reset_all'])) {
    if (!$csrfOk) { $msg = 'Invalid request.'; } else {
    try {
        $pdo->exec("DELETE FROM messages");
        $pdo->exec("DELETE FROM analytics");
        $pdo->exec("DELETE FROM faqs");
        $pdo->exec("DELETE FROM skills");
        $pdo->exec("DELETE FROM projects");
        $pdo->exec("DELETE FROM experiences");
        $pdo->exec("DELETE FROM profile");
        $pdo->prepare("INSERT INTO profile (name, title, email) VALUES (?, ?, ?)")
            ->execute(['Nam Trần', 'Developer & Designer', 'hello@namtran.dev']);
    } catch (PDOException $e) {}
    $msg = 'All data has been reset.';
    }
}

$page = 'settings';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Settings — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
    <style>
        .toggle-group { display: flex; flex-direction: column; gap: 16px; }
        .toggle-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; background: #fafafa; border-radius: 8px; border: 1px solid #e8e8ed;
        }
        .toggle-row .lbl { font-size: 14px; font-weight: 500; color: #1d1d1f; }
        .toggle-row .desc { font-size: 12px; color: #86868b; margin-top: 2px; }
        .switch {
            position: relative; width: 44px; height: 24px; flex-shrink: 0;
            background: #d2d2d7; border-radius: 12px; cursor: pointer; transition: background 0.2s;
        }
        .switch.on { background: #1d1d1f; }
        .switch::after {
            content: ''; position: absolute; top: 2px; left: 2px;
            width: 20px; height: 20px; background: #fff; border-radius: 50%;
            transition: transform 0.2s;
        }
        .switch.on::after { transform: translateX(20px); }
        .switch input { display: none; }
        .danger-zone { margin-top: 32px; padding-top: 24px; border-top: 1px solid #e8e8ed; }
        .danger-zone h3 { font-size: 14px; font-weight: 600; color: #dc2626; margin-bottom: 12px; }
        .danger-zone .desc { font-size: 13px; color: #86868b; margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Settings</h1>
                <div class="sub">Manage website features and data</div>
            </div>
        </div>

        <?php if ($msg): ?><div class="alert alert-ok"><?= h($msg) ?></div><?php endif; ?>

        <div class="form-card" style="max-width:600px">
            <div class="form-title">Feature Toggles</div>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <div class="toggle-group">
                    <?php
                    $toggles = [
                        'show_skills' => ['Skills Section', 'Show skills grid on homepage'],
                        'show_projects' => ['Projects Section', 'Show GitHub projects on homepage'],
                        'show_faq' => ['FAQ Section', 'Show frequently asked questions'],
                        'show_contact' => ['Contact Section', 'Show contact info and form'],
                        'enable_analytics' => ['Analytics Tracking', 'Record visitor data'],
                        'enable_contact_form' => ['Contact Form', 'Allow visitors to send messages'],
                    ];
                    foreach ($toggles as $key => $desc):
                        $on = ($settings[$key] ?? '1') === '1';
                    ?>
                    <div class="toggle-row">
                        <div>
                            <div class="lbl"><?= $desc[0] ?></div>
                            <div class="desc"><?= $desc[1] ?></div>
                        </div>
                        <label class="switch <?= $on ? 'on' : '' ?>">
                            <input type="checkbox" name="<?= $key ?>" value="1" <?= $on ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('on',this.checked)" />
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="btn-group" style="margin-top:20px">
                    <button type="submit" name="save_settings" class="btn btn-pri">Save Settings</button>
                </div>
            </form>
        </div>

        <div class="form-card" style="max-width:600px;margin-top:20px">
            <div class="form-title">Export Data</div>
            <p style="font-size:13px;color:#86868b;margin-bottom:14px">Download all data as a JSON file.</p>
            <a href="?export=1&_csrf=<?= generateCsrfToken() ?>" class="btn btn-out"><i class="ph ph-download"></i> Export JSON</a>
        </div>

        <div class="form-card" style="max-width:600px;margin-top:20px">
            <div class="form-title">GitHub Cache</div>
            <p style="font-size:13px;color:#86868b;margin-bottom:14px">Repos are cached for 30 minutes. Clear to fetch latest immediately.</p>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <button type="submit" name="reset_github_cache" class="btn btn-out"><i class="ph ph-arrow-clockwise"></i> Clear Cache</button>
            </form>
        </div>

        <div class="danger-zone form-card" style="max-width:600px">
            <h3><i class="ph ph-warning"></i> Danger Zone</h3>
            <div style="display:flex;flex-direction:column;gap:12px">
                <form method="POST" onsubmit="return confirm('Clear all analytics data?')" style="display:flex;align-items:center;justify-content:space-between">
                        <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                    <div>
                        <div class="lbl" style="font-size:14px;font-weight:500">Reset Analytics</div>
                        <div class="desc" style="font-size:12px;color:#86868b">Delete all visitor tracking data.</div>
                    </div>
                    <button type="submit" name="reset_analytics" class="btn btn-dan btn-sm">Reset</button>
                </form>
                <form method="POST" onsubmit="return confirm('This will delete ALL data except users and settings. Are you sure?')" style="display:flex;align-items:center;justify-content:space-between">
                        <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                    <div>
                        <div class="lbl" style="font-size:14px;font-weight:500">Reset All Database</div>
                        <div class="desc" style="font-size:12px;color:#86868b">Clear all content, messages, and analytics.</div>
                    </div>
                    <button type="submit" name="reset_all" class="btn btn-dan btn-sm">Reset All</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
