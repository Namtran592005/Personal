<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$success = null; $error = null; $projects = []; $edit = null;

if ($dbAvailable) {
    if (isset($_GET['delete'])) {
        if (!validateCsrfToken($_GET['_csrf'] ?? null)) { $error = 'Invalid request.'; }
        else try {
            $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([(int)$_GET['delete']]);
            $success = 'Project deleted.';
        } catch (PDOException $e) { $error = 'Delete failed.'; }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        if (!validateCsrfToken($_POST['_csrf'] ?? null)) { $error = 'Invalid request.'; }
        else {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $tech_stack = trim($_POST['tech_stack'] ?? '');
        $github_url = trim($_POST['github_url'] ?? '');
        $live_url = trim($_POST['live_url'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;
        if (empty($title)) { $error = 'Title is required.'; }
        else {
            try {
                if ($id > 0) {
                    $pdo->prepare("UPDATE projects SET title=?,description=?,tech_stack=?,github_url=?,live_url=?,image=?,sort_order=?,visible=? WHERE id=?")
                        ->execute([$title,$description,$tech_stack,$github_url,$live_url,$image,$sort_order,$visible,$id]);
                    $success = 'Project updated.';
                } else {
                    $pdo->prepare("INSERT INTO projects (title,description,tech_stack,github_url,live_url,image,sort_order,visible) VALUES (?,?,?,?,?,?,?,?)")
                        ->execute([$title,$description,$tech_stack,$github_url,$live_url,$image,$sort_order,$visible]);
                    $success = 'Project added.';
                }
            } catch (PDOException $e) { $error = 'Save failed.'; }
        }
    }
    }

    try { $projects = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC")->fetchAll(); } catch (PDOException $e) {}
    if (isset($_GET['edit'])) {
        try {
            $s = $pdo->prepare("SELECT * FROM projects WHERE id=?");
            $s->execute([(int)$_GET['edit']]);
            $edit = $s->fetch();
        } catch (PDOException $e) {}
    }
}

$page = 'projects';
$showForm = isset($_GET['new']) || !empty($edit);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Projects — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Projects</h1>
                <div class="sub">Manage your featured projects</div>
            </div>
            <a href="<?= BASE_PATH ?>/admin/projects.php?new=1" class="btn btn-pri <?= $showForm ? 'style=display:none' : '' ?>"><i class="ph ph-plus"></i> Add New</a>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <?php if (!$showForm): ?>
        <div class="tbl tbl-projects" style="margin-bottom:28px">
            <table>
                <thead>
                    <tr><th>Title</th><th>Description</th><th>Tech Stack</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><strong><?= h($p['title']) ?></strong></td>
                            <td class="txt-sm txt-muted"><?= h(truncate($p['description'], 70)) ?></td>
                            <td class="txt-sm txt-muted"><?= h($p['tech_stack']) ?></td>
                            <td><?= $p['visible'] ? '<span class="bdg bdg-on">On</span>' : '<span class="bdg bdg-off">Off</span>' ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= BASE_PATH ?>/admin/projects.php?edit=<?= $p['id'] ?>" class="btn btn-out btn-sm">Edit</a>
                                    <a href="<?= BASE_PATH ?>/admin/projects.php?delete=<?= $p['id'] ?>&_csrf=<?= generateCsrfToken() ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="empty">No projects yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="form-card" <?= $showForm ? '' : 'style=display:none' ?>>
            <div class="form-title"><?= $edit ? 'Edit Project' : 'Add Project' ?></div>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Title</label>
                        <input class="fg-input" type="text" name="title" value="<?= h($edit['title'] ?? '') ?>" required />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Sort Order</label>
                        <input class="fg-input" type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>" />
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">Description</label>
                    <textarea name="description"><?= h($edit['description'] ?? '') ?></textarea>
                </div>
                <div class="fg">
                    <label class="fg-label">Tech Stack (comma-separated)</label>
                    <input class="fg-input" type="text" name="tech_stack" value="<?= h($edit['tech_stack'] ?? '') ?>" placeholder="React,Node.js" />
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">GitHub URL</label>
                        <input class="fg-input" type="url" name="github_url" value="<?= h($edit['github_url'] ?? '') ?>" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Live URL</label>
                        <input class="fg-input" type="url" name="live_url" value="<?= h($edit['live_url'] ?? '') ?>" />
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">Image Path</label>
                    <input class="fg-input" type="text" name="image" value="<?= h($edit['image'] ?? '') ?>" placeholder="/uploads/project.jpg" />
                </div>
                <div class="fg">
                    <label class="fg-check">
                        <input type="checkbox" name="visible" <?= !isset($edit) || $edit['visible'] ? 'checked' : '' ?> />
                        Visible on public site
                    </label>
                </div>
                <div class="btn-group" style="margin-top:16px">
                    <button type="submit" name="save" class="btn btn-pri">Save</button>
                    <a href="<?= BASE_PATH ?>/admin/projects.php" class="btn btn-out">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
