<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$skillCount = 0; $projectCount = 0; $unread = 0; $totalMsg = 0;
$faqCount = 0; $visitCount = 0;
$recent = [];
$profile = ['name' => 'Admin'];

if ($dbAvailable) {
    try {
        $skillCount = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
        $projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $unread = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
        $totalMsg = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
        $faqCount = $pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
        $visitCount = $pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();
        $recent = $pdo->query("SELECT id, name, email, subject, created_at, is_read FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $dbProf = $pdo->query("SELECT * FROM profile WHERE id = 1")->fetch();
        if ($dbProf) $profile = $dbProf;
    } catch (PDOException $e) {}
}

$page = 'dashboard';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Overview — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Overview</h1>
                <div class="sub">Welcome back, <?= h($profile['name'] ?? 'Admin') ?></div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="val"><?= $skillCount ?></div>
                <div class="lbl">Skills</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $projectCount ?></div>
                <div class="lbl">Projects</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $unread ?></div>
                <div class="lbl">Unread Messages</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $totalMsg ?></div>
                <div class="lbl">Total Messages</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $faqCount ?></div>
                <div class="lbl">FAQs</div>
            </div>
            <div class="stat-card">
                <div class="val"><?= $visitCount ?></div>
                <div class="lbl">Visits</div>
            </div>
        </div>

        <h2 style="font-size:16px;font-weight:600;margin-bottom:12px;letter-spacing:-0.01em">Recent Messages</h2>
        <div class="tbl tbl-dashboard">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent) > 0): ?>
                        <?php foreach ($recent as $m): ?>
                        <tr>
                            <td><strong><?= h($m['name']) ?></strong></td>
                            <td class="txt-sm txt-muted"><?= h($m['email']) ?></td>
                            <td class="txt-sm txt-muted"><?= h(truncate($m['subject'], 30)) ?: '<span class="txt-muted">—</span>' ?></td>
                            <td class="txt-sm txt-muted"><?= date('M j, g:i A', strtotime($m['created_at'])) ?></td>
                            <td><?= $m['is_read'] ? '<span class="bdg bdg-off">Read</span>' : '<span class="bdg bdg-new">New</span>' ?></td>
                            <td><a href="<?= BASE_PATH ?>/admin/messages.php?id=<?= $m['id'] ?>" class="btn btn-out btn-sm">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="empty">No messages yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
