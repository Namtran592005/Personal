<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$success = null; $error = null; $messages = []; $view = null;
$perPage = 15;
$p = max(1, (int)($_GET['page'] ?? 1));
$status = $_GET['status'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$total = 0;
$pages = 1;

$where = [];
$params = [];
if ($status === 'read') { $where[] = "is_read = 1"; }
if ($status === 'unread') { $where[] = "is_read = 0"; }
if ($q !== '') {
    $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like, $like);
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

if ($dbAvailable) {
    if (isset($_GET['delete'])) {
        try {
            $pdo->prepare("DELETE FROM messages WHERE id=?")->execute([(int)$_GET['delete']]);
            $success = 'Message deleted.';
        } catch (PDOException $e) { $error = 'Delete failed.'; }
    }

    if (isset($_GET['id'])) {
        try {
            $s = $pdo->prepare("SELECT * FROM messages WHERE id=?");
            $s->execute([(int)$_GET['id']]);
            $view = $s->fetch();
            if ($view && !$view['is_read']) {
                $pdo->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([$view['id']]);
                $view['is_read'] = 1;
            }
        } catch (PDOException $e) {}
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages$whereSql");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        $p = min($p, $pages);
        $offset = ($p - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM messages$whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $messages = $stmt->fetchAll();
    } catch (PDOException $e) {}
}

$page = 'messages';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Messages — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Messages</h1>
                <div class="sub">Contact form submissions</div>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <?php if ($view): ?>
        <a href="<?= BASE_PATH ?>/admin/messages.php" class="btn btn-out btn-sm mb-2">&larr; Back</a>
        <div class="msg-detail">
            <div class="f">
                <div class="fl">Name</div>
                <div class="fv"><?= h($view['name']) ?> <?= !empty($view['is_anonymous']) ? '<span class="bdg bdg-off" style="background:#f0f0f2">Anonymous</span>' : '' ?></div>
            </div>
            <div class="f">
                <div class="fl">Email</div>
                <div class="fv"><?= $view['is_anonymous'] ? '<span class="txt-muted">—</span>' : '<a href="mailto:'.h($view['email']).'" style="text-decoration:underline">'.h($view['email']).'</a>' ?></div>
            </div>
            <div class="f">
                <div class="fl">Subject</div>
                <div class="fv"><?= $view['is_anonymous'] ? '<span class="txt-muted">—</span>' : h($view['subject'] ?: '(no subject)') ?></div>
            </div>
            <div class="f">
                <div class="fl">Date</div>
                <div class="fv txt-sm txt-muted"><?= date('F j, Y \a\t g:i A', strtotime($view['created_at'])) ?></div>
            </div>
            <div class="f">
                <div class="fl">Message</div>
                <div class="fv body"><?= h($view['message']) ?></div>
            </div>
            <div class="btn-group" style="margin-top:16px">
                <a href="<?= BASE_PATH ?>/admin/messages.php?delete=<?= $view['id'] ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete?')">Delete</a>
            </div>
        </div>
        <?php else: ?>

        <form method="GET" class="filter-bar" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
            <input class="fg-input" type="text" name="q" value="<?= h($q) ?>" placeholder="Search name, email, subject..." style="max-width:280px" />
            <select class="fg-input" name="status" style="max-width:160px">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                <option value="unread" <?= $status === 'unread' ? 'selected' : '' ?>>Unread</option>
                <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Read</option>
            </select>
            <button type="submit" class="btn btn-out btn-sm">Filter</button>
            <?php if ($status !== 'all' || $q !== ''): ?>
            <a href="<?= BASE_PATH ?>/admin/messages.php" class="btn btn-out btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <div class="tbl tbl-messages">
                    <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $m): ?>
                        <tr>
                            <td><strong><?= h($m['name']) ?></strong><?= !empty($m['is_anonymous']) ? ' <span class="bdg bdg-off" style="background:#f0f0f2;font-size:10px">Anon</span>' : '' ?></td>
                            <td class="txt-sm txt-muted"><?= $m['is_anonymous'] ? '<span class="txt-muted">—</span>' : h($m['email']) ?></td>
                            <td class="txt-sm txt-muted"><?= $m['is_anonymous'] ? '<span class="txt-muted">—</span>' : (h(truncate($m['subject'], 35)) ?: '<span class="txt-muted">—</span>') ?></td>
                            <td class="txt-sm txt-muted"><?= date('M j, Y', strtotime($m['created_at'])) ?></td>
                            <td><?= $m['is_read'] ? '<span class="bdg bdg-off">Read</span>' : '<span class="bdg bdg-new">New</span>' ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= BASE_PATH ?>/admin/messages.php?id=<?= $m['id'] ?>" class="btn btn-out btn-sm">View</a>
                                    <a href="<?= BASE_PATH ?>/admin/messages.php?delete=<?= $m['id'] ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="empty">No messages yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total > $perPage): ?>
        <div class="pagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px">
            <?php
            $qs = function (int $p) use ($status, $q) {
                $parts = ['page' => $p];
                if ($status !== 'all') $parts['status'] = $status;
                if ($q !== '') $parts['q'] = $q;
                return http_build_query($parts);
            };
            for ($i = 1; $i <= $pages; $i++): ?>
            <a href="<?= BASE_PATH ?>/admin/messages.php?<?= $qs($i) ?>" class="btn btn-out btn-sm <?= $i === $p ? 'pagination-current' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
