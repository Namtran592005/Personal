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
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $priceNote = trim($_POST['price_note'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $buttonText = trim($_POST['button_text'] ?? '');
    $popular = (int)(isset($_POST['popular']) ? 1 : 0);
    $sort = (int)($_POST['sort_order'] ?? 0);
    $vis = (int)(isset($_POST['visible']) ? 1 : 0);
    if (empty($title) || empty($price)) {
        $err = 'Title and price are required.';
    } elseif ($dbAvailable) {
        try {
            $pdo->prepare("INSERT INTO pricing_plans (title, price, price_note, badge, features, button_text, popular, sort_order, visible) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$title, $price, $priceNote, $badge, $features, $buttonText, $popular, $sort, $vis]);
            $ok = 'Plan added.';
            $action = 'list';
        } catch (PDOException $e) { $err = 'Database error.'; }
    }
    }
}

if ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrfOk) { $err = 'Invalid request.'; } else {
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $priceNote = trim($_POST['price_note'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $buttonText = trim($_POST['button_text'] ?? '');
    $popular = (int)(isset($_POST['popular']) ? 1 : 0);
    $sort = (int)($_POST['sort_order'] ?? 0);
    $vis = (int)(isset($_POST['visible']) ? 1 : 0);
    if (empty($title) || empty($price)) {
        $err = 'Title and price are required.';
    } elseif ($dbAvailable) {
        try {
            $pdo->prepare("UPDATE pricing_plans SET title=?, price=?, price_note=?, badge=?, features=?, button_text=?, popular=?, sort_order=?, visible=? WHERE id=?")
                ->execute([$title, $price, $priceNote, $badge, $features, $buttonText, $popular, $sort, $vis, $id]);
            $ok = 'Plan updated.';
            $action = 'list';
        } catch (PDOException $e) { $err = 'Database error.'; }
    }
    }
}

if ($action === 'delete' && $id && $dbAvailable) {
    if (!$csrfOk) { $err = 'Invalid request.'; } else {
    try {
        $pdo->prepare("DELETE FROM pricing_plans WHERE id=?")->execute([$id]);
        $ok = 'Plan deleted.';
    } catch (PDOException $e) { $err = 'Database error.'; }
    $action = 'list';
    }
}

$plans = [];
if ($dbAvailable) {
    try {
        $plans = $pdo->query("SELECT * FROM pricing_plans ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (PDOException $e) {}
}

$editItem = null;
if ($action === 'edit' && $id && $dbAvailable) {
    $stmt = $pdo->prepare("SELECT * FROM pricing_plans WHERE id=?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
}
$page = 'pricing';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Pricing — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Pricing Plans</h1>
                <div class="sub">Manage your services &amp; pricing plans</div>
            </div>
            <?php if ($action === 'list'): ?>
            <a href="?action=add" class="btn btn-pri"><i class="ph ph-plus"></i> Add Plan</a>
            <?php endif; ?>
        </div>

        <?php if ($ok): ?><div class="alert alert-ok"><?= h($ok) ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-no"><?= h($err) ?></div><?php endif; ?>

        <?php if ($action === 'add' || ($action === 'edit' && $editItem)): ?>
        <div class="form-card">
            <div class="form-title"><?= $action === 'add' ? 'Add Plan' : 'Edit Plan' ?></div>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Title</label>
                        <input class="fg-input" type="text" name="title" required
                               value="<?= h($editItem['title'] ?? '') ?>" placeholder="Landing Page" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Price</label>
                        <input class="fg-input" type="text" name="price" required
                               value="<?= h($editItem['price'] ?? '') ?>" placeholder="5.500.000đ" />
                    </div>
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Price Note</label>
                        <input class="fg-input" type="text" name="price_note"
                               value="<?= h($editItem['price_note'] ?? '') ?>" placeholder="trọn gói" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Badge (optional)</label>
                        <input class="fg-input" type="text" name="badge"
                               value="<?= h($editItem['badge'] ?? '') ?>" placeholder="Phổ biến nhất" />
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">Features (one per line)</label>
                    <textarea name="features" rows="7" placeholder="Thiết kế responsive&#10;Tối ưu tốc độ &amp; SEO"><?= h($editItem['features'] ?? '') ?></textarea>
                </div>
                <div class="fg">
                    <label class="fg-label">Button Text</label>
                    <input class="fg-input" type="text" name="button_text"
                           value="<?= h($editItem['button_text'] ?? '') ?>" placeholder="Bắt đầu ngay" />
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Sort Order</label>
                        <input class="fg-input" type="number" name="sort_order" value="<?= (int)($editItem['sort_order'] ?? 0) ?>" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">&nbsp;</label>
                        <div style="display:flex;gap:16px">
                            <label class="fg-check">
                                <input type="checkbox" name="popular" value="1" <?= !empty($editItem['popular']) ? 'checked' : '' ?> />
                                Popular
                            </label>
                            <label class="fg-check">
                                <input type="checkbox" name="visible" value="1" <?= !isset($editItem['visible']) || $editItem['visible'] ? 'checked' : '' ?> />
                                Visible
                            </label>
                        </div>
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
                        <th>Title</th>
                        <th>Price</th>
                        <th>Badge</th>
                        <th>Popular</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($plans) > 0): ?>
                        <?php foreach ($plans as $p): ?>
                        <tr>
                            <td><strong><?= h($p['title']) ?></strong></td>
                            <td class="txt-sm"><?= h($p['price']) ?></td>
                            <td class="txt-sm txt-muted"><?= h($p['badge'] ?: '—') ?></td>
                            <td><?= $p['popular'] ? '<span class="bdg bdg-on">Popular</span>' : '<span class="txt-muted">—</span>' ?></td>
                            <td class="txt-sm txt-muted"><?= (int)$p['sort_order'] ?></td>
                            <td><?= $p['visible'] ? '<span class="bdg bdg-on">Visible</span>' : '<span class="bdg bdg-off">Hidden</span>' ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-out btn-sm">Edit</a>
                                    <a href="?action=delete&id=<?= $p['id'] ?>&_csrf=<?= generateCsrfToken() ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete this plan?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty">No plans yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
