<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$success = null; $error = null; $skills = []; $edit = null;

if ($dbAvailable) {
    if (isset($_GET['delete'])) {
        if (!validateCsrfToken($_GET['_csrf'] ?? null)) { $error = 'Invalid request.'; }
        else try {
            $pdo->prepare("DELETE FROM skills WHERE id=?")->execute([(int)$_GET['delete']]);
            $success = 'Skill deleted.';
        } catch (PDOException $e) { $error = 'Delete failed.'; }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        if (!validateCsrfToken($_POST['_csrf'] ?? null)) { $error = 'Invalid request.'; }
        else {
        $id = (int)($_POST['id'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $icon = trim($_POST['icon'] ?? 'ph-code');
        $description = trim($_POST['description'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;
        if (empty($category)) { $error = 'Category is required.'; }
        else {
            try {
                if ($id > 0) {
                    $pdo->prepare("UPDATE skills SET category=?,icon=?,description=?,tags=?,sort_order=?,visible=? WHERE id=?")
                        ->execute([$category,$icon,$description,$tags,$sort_order,$visible,$id]);
                    $success = 'Skill updated.';
                } else {
                    $pdo->prepare("INSERT INTO skills (category,icon,description,tags,sort_order,visible) VALUES (?,?,?,?,?,?)")
                        ->execute([$category,$icon,$description,$tags,$sort_order,$visible]);
                    $success = 'Skill added.';
                }
            } catch (PDOException $e) { $error = 'Save failed.'; }
        }
    }
    }

    try { $skills = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC")->fetchAll(); } catch (PDOException $e) {}
    if (isset($_GET['edit'])) {
        try {
            $s = $pdo->prepare("SELECT * FROM skills WHERE id=?");
            $s->execute([(int)$_GET['edit']]);
            $edit = $s->fetch();
        } catch (PDOException $e) {}
    }
}

$page = 'skills';
$showForm = isset($_GET['new']) || !empty($edit);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Skills — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Skills</h1>
                <div class="sub">Manage your professional skills</div>
            </div>
            <a href="<?= BASE_PATH ?>/admin/skills.php?new=1" class="btn btn-pri <?= $showForm ? 'style=display:none' : '' ?>"><i class="ph ph-plus"></i> Add New</a>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <?php if (!$showForm): ?>
        <div class="tbl tbl-skills" style="margin-bottom:28px">
            <table>
                <thead>
                    <tr><th>Order</th><th>Icon</th><th>Category</th><th>Description</th><th>Tags</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (count($skills) > 0): ?>
                        <?php foreach ($skills as $s): ?>
                        <tr>
                            <td class="txt-sm txt-muted"><?= (int)$s['sort_order'] ?></td>
                            <td><i class="ph <?= h($s['icon']) ?>" style="font-size:18px"></i></td>
                            <td><strong><?= h($s['category']) ?></strong></td>
                            <td class="txt-sm txt-muted"><?= h(truncate($s['description'], 50)) ?></td>
                            <td class="txt-sm txt-muted"><?= h(truncate($s['tags'], 30)) ?></td>
                            <td><?= $s['visible'] ? '<span class="bdg bdg-on">On</span>' : '<span class="bdg bdg-off">Off</span>' ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= BASE_PATH ?>/admin/skills.php?edit=<?= $s['id'] ?>" class="btn btn-out btn-sm">Edit</a>
                                    <a href="<?= BASE_PATH ?>/admin/skills.php?delete=<?= $s['id'] ?>&_csrf=<?= generateCsrfToken() ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty">No skills yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="form-card" <?= $showForm ? '' : 'style=display:none' ?>>
            <div class="form-title"><?= $edit ? 'Edit Skill' : 'Add Skill' ?></div>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Category</label>
                        <input class="fg-input" type="text" name="category" value="<?= h($edit['category'] ?? '') ?>" required />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Icon (Phosphor class)</label>
                        <input class="fg-input" type="text" name="icon" value="<?= h($edit['icon'] ?? 'ph-code') ?>" />
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">Description</label>
                    <textarea name="description"><?= h($edit['description'] ?? '') ?></textarea>
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Tags (comma-separated)</label>
                        <input class="fg-input" type="text" name="tags" value="<?= h($edit['tags'] ?? '') ?>" placeholder="React,Node.js" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Sort Order</label>
                        <input class="fg-input" type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>" />
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-check">
                        <input type="checkbox" name="visible" <?= !isset($edit) || $edit['visible'] ? 'checked' : '' ?> />
                        Visible on public site
                    </label>
                </div>
                <div class="btn-group" style="margin-top:16px">
                    <button type="submit" name="save" class="btn btn-pri">Save</button>
                    <a href="<?= BASE_PATH ?>/admin/skills.php" class="btn btn-out">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
