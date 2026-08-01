<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$success = null; $error = null; $experiences = []; $edit = null;

if ($dbAvailable) {
    if (isset($_GET['delete'])) {
        if (!validateCsrfToken($_GET['_csrf'] ?? null)) { $error = 'Invalid request.'; }
        else try {
            $pdo->prepare("DELETE FROM experiences WHERE id=?")->execute([(int)$_GET['delete']]);
            $success = 'Experience deleted.';
        } catch (PDOException $e) { $error = 'Delete failed.'; }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        if (!validateCsrfToken($_POST['_csrf'] ?? null)) { $error = 'Invalid request.'; }
        else {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $visible = isset($_POST['visible']) ? 1 : 0;
        if (empty($title) || empty($company)) { $error = 'Title and company are required.'; }
        else {
            try {
                if ($id > 0) {
                    $pdo->prepare("UPDATE experiences SET title=?,company=?,location=?,start_date=?,end_date=?,description=?,sort_order=?,visible=? WHERE id=?")
                        ->execute([$title,$company,$location,$start_date,$end_date,$description,$sort_order,$visible,$id]);
                    $success = 'Experience updated.';
                } else {
                    $pdo->prepare("INSERT INTO experiences (title,company,location,start_date,end_date,description,sort_order,visible) VALUES (?,?,?,?,?,?,?,?)")
                        ->execute([$title,$company,$location,$start_date,$end_date,$description,$sort_order,$visible]);
                    $success = 'Experience added.';
                }
            } catch (PDOException $e) { $error = 'Save failed.'; }
        }
    }
    }

    try { $experiences = $pdo->query("SELECT * FROM experiences ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (PDOException $e) {}
    if (isset($_GET['edit'])) {
        try {
            $s = $pdo->prepare("SELECT * FROM experiences WHERE id=?");
            $s->execute([(int)$_GET['edit']]);
            $edit = $s->fetch();
        } catch (PDOException $e) {}
    }
}

$page = 'experiences';
$showForm = isset($_GET['new']) || !empty($edit);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/assets/favicon.png" />
    <title>Experiences — Admin</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/icons/phosphor/style.css" />
    <link rel="stylesheet" href="<?= BASE_PATH ?>/admin-assets/admin.css" />
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-head">
            <div>
                <h1>Experiences</h1>
                <div class="sub">Manage your work history and timeline</div>
            </div>
            <a href="<?= BASE_PATH ?>/admin/experiences.php?new=1" class="btn btn-pri <?= $showForm ? 'style=display:none' : '' ?>"><i class="ph ph-plus"></i> Add New</a>
        </div>

        <?php if ($success): ?><div class="alert alert-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-no"><?= h($error) ?></div><?php endif; ?>

        <?php if (!$showForm): ?>
        <div class="tbl tbl-experiences" style="margin-bottom:28px">
            <table>
                <thead>
                    <tr><th>Order</th><th>Title</th><th>Company</th><th>Period</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (count($experiences) > 0): ?>
                        <?php foreach ($experiences as $e): ?>
                        <tr>
                            <td class="txt-sm txt-muted"><?= (int)$e['sort_order'] ?></td>
                            <td><strong><?= h($e['title']) ?></strong></td>
                            <td class="txt-sm txt-muted"><?= h($e['company']) ?></td>
                            <td class="txt-sm txt-muted"><?= h(($e['start_date'] ?: '?') . ' — ' . ($e['end_date'] ?: 'Present')) ?></td>
                            <td><?= $e['visible'] ? '<span class="bdg bdg-on">On</span>' : '<span class="bdg bdg-off">Off</span>' ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= BASE_PATH ?>/admin/experiences.php?edit=<?= $e['id'] ?>" class="btn btn-out btn-sm">Edit</a>
                                    <a href="<?= BASE_PATH ?>/admin/experiences.php?delete=<?= $e['id'] ?>&_csrf=<?= generateCsrfToken() ?>" class="btn btn-dan btn-sm" onclick="return confirm('Delete?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="empty">No experiences yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="form-card" <?= $showForm ? '' : 'style=display:none' ?>>
            <div class="form-title"><?= $edit ? 'Edit Experience' : 'Add Experience' ?></div>
            <form method="POST">
                <input type="hidden" name="_csrf" value="<?= generateCsrfToken() ?>">
                <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Title</label>
                        <input class="fg-input" type="text" name="title" value="<?= h($edit['title'] ?? '') ?>" placeholder="Software Engineer" required />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Company</label>
                        <input class="fg-input" type="text" name="company" value="<?= h($edit['company'] ?? '') ?>" required />
                    </div>
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Location</label>
                        <input class="fg-input" type="text" name="location" value="<?= h($edit['location'] ?? '') ?>" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">Sort Order</label>
                        <input class="fg-input" type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>" />
                    </div>
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">Start Date</label>
                        <input class="fg-input" type="month" name="start_date" value="<?= h($edit['start_date'] ?? '') ?>" />
                    </div>
                    <div class="fg">
                        <label class="fg-label">End Date (blank = Present)</label>
                        <input class="fg-input" type="month" name="end_date" value="<?= h($edit['end_date'] ?? '') ?>" />
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">Description</label>
                    <textarea name="description"><?= h($edit['description'] ?? '') ?></textarea>
                </div>
                <div class="fg">
                    <label class="fg-check">
                        <input type="checkbox" name="visible" <?= !isset($edit) || $edit['visible'] ? 'checked' : '' ?> />
                        Visible on public site
                    </label>
                </div>
                <div class="btn-group" style="margin-top:16px">
                    <button type="submit" name="save" class="btn btn-pri">Save</button>
                    <a href="<?= BASE_PATH ?>/admin/experiences.php" class="btn btn-out">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
