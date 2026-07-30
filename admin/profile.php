<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$profile = ['name'=>'','title'=>'','bio'=>'','email'=>'','phone'=>'','location'=>'',
    'social_github'=>'','social_linkedin'=>'','social_twitter'=>'','social_dribbble'=>'',
    'social_facebook'=>'','social_instagram'=>'','social_threads'=>'','social_tiktok'=>''];
$success = null;

if ($dbAvailable) {
    try {
        $db = $pdo->query("SELECT * FROM profile WHERE id = 1")->fetch();
        if ($db) $profile = array_merge($profile, $db);
    } catch (PDOException $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if (!validateCsrfToken($_POST['_csrf'] ?? null)) { /* fail silently */ } else {
    $fields = ['name','title','bio','email','phone','location',
               'social_github','social_linkedin','social_twitter','social_dribbble',
               'social_facebook','social_instagram','social_threads','social_tiktok'];
    $sets = implode('=?, ', $fields) . '=?';
    $vals = array_map(fn($f) => trim($_POST[$f] ?? ''), $fields);
    $vals[] = 1;
    try {
        $pdo->prepare("UPDATE profile SET $sets WHERE id=?")->execute($vals);
        $db = $pdo->query("SELECT * FROM profile WHERE id = 1")->fetch();
        if ($db) $profile = $db;
        $success = 'Profile saved.';
    } catch (PDOException $e) { $success = null; }
    $ghUser = trim($_POST['github_username'] ?? '');
    if ($ghUser) {
        $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('github_username', ?)")->execute([$ghUser]);
    }
    }
}

$page = 'profile';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Profile — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Profile</h1>
                <div class="sub">Manage your public information</div>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>

        <form method="POST" class="form-card">
            <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
            <div class="fg-row">
                <div class="fg">
                    <label class="fg-label">Name</label>
                    <input class="fg-input" type="text" name="name" value="<?= h($profile['name']) ?>" />
                </div>
                <div class="fg">
                    <label class="fg-label">Title</label>
                    <input class="fg-input" type="text" name="title" value="<?= h($profile['title']) ?>" />
                </div>
            </div>
            <div class="fg">
                <label class="fg-label">Bio</label>
                <textarea name="bio"><?= h($profile['bio']) ?></textarea>
            </div>
            <div class="fg-row">
                <div class="fg">
                    <label class="fg-label">Email</label>
                    <input class="fg-input" type="email" name="email" value="<?= h($profile['email']) ?>" />
                </div>
                <div class="fg">
                    <label class="fg-label">Phone</label>
                    <input class="fg-input" type="text" name="phone" value="<?= h($profile['phone']) ?>" />
                </div>
            </div>
            <div class="fg">
                <label class="fg-label">Location</label>
                <input class="fg-input" type="text" name="location" value="<?= h($profile['location']) ?>" />
            </div>

            <div style="font-size:14px;font-weight:600;margin:24px 0 14px">Social Links</div>
            <div class="fg-row">
                <div class="fg">
                    <label class="fg-label">GitHub</label>
                    <input class="fg-input" type="url" name="social_github" value="<?= h($profile['social_github']) ?>" placeholder="https://github.com/..." />
                </div>
                <div class="fg">
                    <label class="fg-label">LinkedIn</label>
                    <input class="fg-input" type="url" name="social_linkedin" value="<?= h($profile['social_linkedin']) ?>" placeholder="https://linkedin.com/in/..." />
                </div>
            </div>
            <div class="fg-row">
                <div class="fg">
                    <label class="fg-label">Twitter</label>
                    <input class="fg-input" type="url" name="social_twitter" value="<?= h($profile['social_twitter']) ?>" placeholder="https://twitter.com/..." />
                </div>
                <div class="fg">
                    <label class="fg-label">Dribbble</label>
                    <input class="fg-input" type="url" name="social_dribbble" value="<?= h($profile['social_dribbble']) ?>" placeholder="https://dribbble.com/..." />
                </div>
            </div>
            <div class="fg-row">
                <div class="fg">
                    <label class="fg-label">Facebook</label>
                    <input class="fg-input" type="url" name="social_facebook" value="<?= h($profile['social_facebook']) ?>" placeholder="https://facebook.com/..." />
                </div>
                <div class="fg">
                    <label class="fg-label">Instagram</label>
                    <input class="fg-input" type="url" name="social_instagram" value="<?= h($profile['social_instagram']) ?>" placeholder="https://instagram.com/..." />
                </div>
            </div>
            <div class="fg-row">
                <div class="fg">
                    <label class="fg-label">Threads</label>
                    <input class="fg-input" type="url" name="social_threads" value="<?= h($profile['social_threads']) ?>" placeholder="https://threads.net/..." />
                </div>
                <div class="fg">
                    <label class="fg-label">TikTok</label>
                    <input class="fg-input" type="url" name="social_tiktok" value="<?= h($profile['social_tiktok']) ?>" placeholder="https://tiktok.com/..." />
                </div>
            </div>
            <div style="font-size:14px;font-weight:600;margin:24px 0 14px">GitHub Projects</div>
            <div class="fg">
                <label class="fg-label">GitHub Username</label>
                <input class="fg-input" type="text" name="github_username" value="<?= h($settings['github_username'] ?? 'namtran592005') ?>" placeholder="Your GitHub username" />
            </div>
            <div class="btn-group" style="margin-top:20px">
                <button type="submit" name="save" class="btn btn-pri">Save Changes</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
