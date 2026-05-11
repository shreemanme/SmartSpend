<?php
/**
 * Page:      history/index.php
 * Component: Audit Log — User View
 * Developer: Bibek Timsena (Audit & History Log)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$uid = (int)$_SESSION['user_id'];

// Find & Filter
$search = trim($_GET['search'] ?? '');
$filter_action = trim($_GET['action'] ?? '');

$where = ['user_id = ?'];
$params = [$uid];

if ($search !== '') {
    $where[] = 'action_type LIKE ?';
    $params[] = "%$search%";
}
if ($filter_action !== '') {
    $where[] = 'action_type = ?';
    $params[] = $filter_action;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT * FROM tblAuditLog $whereClause ORDER BY action_date DESC, log_id DESC");
$stmt->execute($params);
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>My History Log</h1>
    <a href="/smartspend/history/add.php" class="btn-primary">+ Add Note</a>
</div>

<!-- Search and Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end;">
    <div class="form-group" style="flex: 1;">
        <label for="search">Find (Action)</label>
        <input type="text" id="search" name="search" placeholder="Search action..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="action">Filter by Action</label>
        <select id="action" name="action">
            <option value="">All Actions</option>
            <option value="CREATE" <?= $filter_action === 'CREATE' ? 'selected' : '' ?>>Create</option>
            <option value="UPDATE" <?= $filter_action === 'UPDATE' ? 'selected' : '' ?>>Update</option>
            <option value="DELETE" <?= $filter_action === 'DELETE' ? 'selected' : '' ?>>Delete</option>
            <option value="MANUAL" <?= $filter_action === 'MANUAL' ? 'selected' : '' ?>>Manual Note</option>
        </select>
    </div>
    <div class="form-group" style="flex: 0;">
        <button type="submit" class="btn-primary">Apply</button>
    </div>
    <?php if ($search !== '' || $filter_action !== ''): ?>
    <div class="form-group" style="flex: 0;">
        <a href="/smartspend/history/index.php" class="btn-secondary" style="display:inline-block; padding:8px 12px; text-decoration:none;">Clear</a>
    </div>
    <?php endif; ?>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Action</th>
                <th>Details</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['action_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="badge badge-secondary"><?= htmlspecialchars($log['action_type'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td><?= htmlspecialchars($log['old_value'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?= (int)$log['is_reviewed'] === 1 ? '<span class="badge badge-success">Reviewed</span>' : '<span class="badge badge-warning">Pending</span>' ?>
                </td>
                <td>
                    <div class="actions-cell">
                        <?php if ((int)$log['is_reviewed'] === 0): ?>
                        <form method="POST" action="/smartspend/history/edit.php" style="display:inline;">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-warning btn-sm">Acknowledge</button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" action="/smartspend/history/delete.php" style="display:inline;">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Delete this log entry?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
