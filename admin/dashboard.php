<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$skillCount = 0; $projectCount = 0;
$faqCount = 0; $visitCount = 0; $expCount = 0;
$profile = ['name' => 'Admin'];

if ($dbAvailable) {
    try {
        $skillCount = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
        $projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $expCount = $pdo->query("SELECT COUNT(*) FROM experiences")->fetchColumn();
        $faqCount = $pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
        $visitCount = $pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();
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
                <div class="val"><?= $expCount ?></div>
                <div class="lbl">Experiences</div>
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
    </main>
</div>
</body>
</html>
