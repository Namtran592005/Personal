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

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$ok = null; $err = null;

$csrfOk = validateCsrfToken($_POST['_csrf'] ?? $_GET['_csrf'] ?? null);

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrfOk) { $err = 'Invalid request.'; } else {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $vis = (int)($_POST['visible'] ?? 1);
    if (empty($question) || empty($answer)) {
        $err = 'Question and answer are required.';
    } elseif ($dbAvailable) {
        try {
            $pdo->prepare("INSERT INTO faqs (question, answer, sort_order, visible) VALUES (?,?,?,?)")
                ->execute([$question, $answer, $sort, $vis]);
            $ok = 'FAQ added.';
            $action = 'list';
        } catch (PDOException $e) { $err = 'Database error.'; }
    }
    }
}

if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrfOk) { $err = 'Invalid request.'; } else {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $vis = (int)($_POST['visible'] ?? 1);
    if (empty($question) || empty($answer)) {
        $err = 'Question and answer are required.';
    } elseif ($dbAvailable) {
        try {
            $pdo->prepare("UPDATE faqs SET question=?, answer=?, sort_order=?, visible=? WHERE id=?")
                ->execute([$question, $answer, $sort, $vis, $id]);
            $ok = 'FAQ updated.';
            $action = 'list';
        } catch (PDOException $e) { $err = 'Database error.'; }
    }
    }
}

if ($action === 'delete' && $id && $dbAvailable) {
    if (!$csrfOk) { $err = 'Invalid request.'; } else {
    try {
        $pdo->prepare("DELETE FROM faqs WHERE id=?")->execute([$id]);
        $ok = 'FAQ deleted.';
    } catch (PDOException $e) { $err = 'Database error.'; }
    $action = 'list';
    }
}

$faqs = [];
if ($dbAvailable) {
    try {
        $faqs = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id DESC")->fetchAll();
    } catch (PDOException $e) {}
}

$editItem = null;
if ($action === 'edit' && $id && $dbAvailable) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id=?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
}
$page = 'faqs';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>FAQs — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Frequently Asked Questions</h1>
                <div class="sub">Manage your FAQ entries</div>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="btn btn-pri"><i class="ph ph-plus"></i> Add FAQ</a>
            <?php endif; ?>
        </div>

        <?php if ($ok): ?><div class="alert alert-ok"><?= h($ok) ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-no"><?= h($err) ?></div><?php endif; ?>

        <?php if ($action === 'add' || ($action === 'edit' && $editItem)): ?>
        <div class="form-card">
            <div class="form-title"><?= $action === 'add' ? 'Add FAQ' : 'Edit FAQ' ?></div>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <div class="fg">
                    <label class="fg-label">Question</label>
                    <input class="fg-input" type="text" name="question" required
                           value="<?= h($editItem['question'] ?? '') ?>" />
                </div>
                <div class="fg">
                    <label class="fg-label">Answer</label>
                    <textarea name="answer" rows="5" required><?= h($editItem['answer'] ?? '') ?></textarea>
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Sort Order</label>
                        <input class="fg-input" type="number" name="sort_order" value="<?= (int)($editItem['sort_order'] ?? 0) ?>" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">&nbsp;</label>
                        <label class="fg-check">
                            <input type="checkbox" name="visible" value="1" <?= !isset($editItem['visible']) || $editItem['visible'] ? 'checked' : '' ?> />
                            Visible
                        </label>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-pri"><?= $action === 'add' ? 'Add' : 'Update' ?></button>
                    <a href="?" class="btn btn-out">Cancel</a>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="tbl tbl-faqs">
            <table>
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($faqs) > 0): ?>
                        <?php foreach ($faqs as $f): ?>
                        <tr>
                            <td><strong><?= h(truncate($f['question'], 60)) ?></strong></td>
                            <td class="txt-sm txt-muted"><?= (int)$f['sort_order'] ?></td>
                            <td><?= $f['visible'] ? '<span class="bdg bdg-on">Visible</span>' : '<span class="bdg bdg-off">Hidden</span>' ?></td>
                            <td class="txt-sm txt-muted"><?= date('M j, Y', strtotime($f['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="?action=edit&id=<?= $f['id'] ?>" class="btn btn-out btn-sm">Edit</a>
                                    <a href="?action=delete&id=<?= $f['id'] ?>&_csrf=<?= generateCsrfToken() ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete this FAQ?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="empty">No FAQs yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
