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

$thisYear = date('Y');
$perPage = 20;
$vPage = max(1, (int)($_GET['vpage'] ?? 1));
$visitorTotal = 0;
$visitorPages = 1;

$totalVisitors = 0; $totalVisits = 0; $todayVisits = 0; $activeWeek = 0; $totalSeconds = 0;
$daily = []; $browsers = []; $langs = [];
$visitors = [];

if ($dbAvailable) {
    try {
        $totalVisitors = $pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();
        $totalVisits = $pdo->query("SELECT COALESCE(SUM(visits), 0) FROM analytics")->fetchColumn();
        $todayVisits = $pdo->query("SELECT COALESCE(SUM(visits), 0) FROM analytics WHERE date(last_seen) = date('now')")->fetchColumn();
        $activeWeek = $pdo->query("SELECT COUNT(*) FROM analytics WHERE last_seen >= datetime('now', '-7 days')")->fetchColumn();
        $totalSeconds = (int)$pdo->query("SELECT COALESCE(SUM(time_spent), 0) FROM analytics")->fetchColumn();

        $stmtD = $pdo->prepare("SELECT date(last_seen) as d, COUNT(*) as c FROM analytics WHERE strftime('%Y', last_seen) = ? GROUP BY d ORDER BY d ASC");
        $stmtD->execute([$thisYear]);
        $daily = $stmtD->fetchAll();

        $langs = $pdo->query("SELECT language, COUNT(*) as c FROM analytics WHERE language != '' GROUP BY language ORDER BY c DESC LIMIT 10")->fetchAll();

        $agents = $pdo->query("SELECT user_agent, visits FROM analytics WHERE user_agent != ''")->fetchAll();
        foreach ($agents as $a) {
            $ua = $a['user_agent'];
            if (strpos($ua, 'Chrome') !== false && strpos($ua, 'Edg') === false) $bn = 'Chrome';
            elseif (strpos($ua, 'Firefox') !== false) $bn = 'Firefox';
            elseif (strpos($ua, 'Safari') !== false && strpos($ua, 'Chrome') === false) $bn = 'Safari';
            elseif (strpos($ua, 'Edg') !== false) $bn = 'Edge';
            else $bn = 'Other';
            if (!isset($browsers[$bn])) $browsers[$bn] = 0;
            $browsers[$bn] += (int)$a['visits'];
        }

        $visitorTotal = (int)$pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();
        $visitorPages = max(1, (int)ceil($visitorTotal / $perPage));
        $vPage = min($vPage, $visitorPages);
        $vOffset = ($vPage - 1) * $perPage;
        $visitors = $pdo->query("SELECT * FROM analytics ORDER BY visits DESC, last_seen DESC LIMIT $perPage OFFSET $vOffset")->fetchAll();
    } catch (PDOException $e) {}
}

$page = 'analytics';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Analytics — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <script src="<?= BASE_PATH ?>/assets/js/chart.umd.min.js"></script>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Analytics</h1>
                <div class="sub">Visitors overview</div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card"><div class="val"><?= $totalVisitors ?></div><div class="lbl">Unique Visitors</div></div>
            <div class="stat-card"><div class="val"><?= $totalVisits ?></div><div class="lbl">Total Visits</div></div>
            <div class="stat-card"><div class="val"><?= $todayVisits ?></div><div class="lbl">Visits Today</div></div>
            <div class="stat-card"><div class="val"><?= $activeWeek ?></div><div class="lbl">Active This Week</div></div>
        </div>

        <div class="stats">
            <div class="stat-card"><div class="val"><?= formatDuration($totalSeconds) ?></div><div class="lbl">Total Time Spent</div></div>
            <div class="stat-card"><div class="val"><?= $totalVisitors > 0 ? formatDuration((int)round($totalSeconds / $totalVisitors)) : '—' ?></div><div class="lbl">Avg / Visitor</div></div>
            <div class="stat-card"><div class="val"><?= $totalVisits > 0 ? formatDuration((int)round($totalSeconds / $totalVisits)) : '—' ?></div><div class="lbl">Avg / Visit</div></div>
        </div>

        <div style="margin-bottom:24px;width:100%;max-width:100%">
            <canvas id="chartDaily" height="100"></canvas>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
            <div class="form-card" style="max-width:100%">
                <div class="form-title">Browsers</div>
                <div class="tbl">
                    <table>
                        <thead><tr><th>Browser</th><th>Visits</th></tr></thead>
                        <tbody>
                            <?php foreach ($browsers as $bn => $bc): ?>
                            <tr><td><?= h($bn) ?></td><td><strong><?= $bc ?></strong></td></tr>
                            <?php endforeach; ?>
                            <?php if (!count($browsers)): ?><tr><td colspan="2" class="empty">No data</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-card" style="max-width:100%">
                <div class="form-title">Languages</div>
                <div class="tbl">
                    <table>
                        <thead><tr><th>Language</th><th>Visitors</th></tr></thead>
                        <tbody>
                            <?php foreach ($langs as $l): ?>
                            <tr><td><?= h($l['language']) ?></td><td><strong><?= $l['c'] ?></strong></td></tr>
                            <?php endforeach; ?>
                            <?php if (!count($langs)): ?><tr><td colspan="2" class="empty">No data</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-card" style="max-width:100%">
            <div class="form-title">Visitors</div>
            <div class="tbl tbl-analytics">
                <table>
                    <thead><tr><th>IP</th><th>Visits</th><th>Time</th><th>First Seen</th><th>Last Seen</th><th>Pages</th><th>Browser</th></tr></thead>
                    <tbody>
                        <?php foreach ($visitors as $row): ?>
                        <tr>
                            <td class="txt-sm"><?= h($row['ip']) ?></td>
                            <td><strong><?= (int)$row['visits'] ?></strong></td>
                            <td class="txt-sm txt-muted"><?= formatDuration((int)$row['time_spent']) ?></td>
                            <td class="txt-sm txt-muted"><?= date('M j, Y', strtotime($row['first_seen'])) ?></td>
                            <td class="txt-sm txt-muted"><?= date('M j, g:i A', strtotime($row['last_seen'])) ?></td>
                            <td class="txt-sm txt-muted"><?php
                                $pageList = $row['pages'] ? explode(',', $row['pages']) : [];
                                if (count($pageList)) {
                                    echo h(count($pageList) . 'x ') . '<span class="page-hint" title="' . h($row['pages']) . '">' . h($pageList[0]) . '</span>';
                                } else {
                                    echo '—';
                                }
                            ?></td>
                            <td class="txt-sm txt-muted"><?= h(truncate($row['user_agent'], 30)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!count($visitors)): ?><tr><td colspan="7" class="empty">No visitors yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($visitorTotal > $perPage): ?>
            <div class="pagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px">
                <?php for ($i = 1; $i <= $visitorPages; $i++): ?>
                <a href="<?= BASE_PATH ?>/admin/analytics.php?vpage=<?= $i ?>" class="btn btn-out btn-sm <?= $i === $vPage ? 'pagination-current' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
const dailyData = <?= json_encode($daily) ?>;
if (dailyData.length && document.getElementById('chartDaily')) {
    new Chart(document.getElementById('chartDaily'), {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.d),
            datasets: [{
                label: 'Active Visitors',
                data: dailyData.map(d => d.c),
                borderColor: '#1d1d1f',
                backgroundColor: 'rgba(29,29,31,0.06)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: '#1d1d1f',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#86868b' } },
                y: { beginAtZero: true, grid: { color: '#f2f2f5' }, ticks: { font: { size: 11 }, color: '#86868b', stepSize: 1 } }
            }
        }
    });
}
</script>
</body>
</html>
