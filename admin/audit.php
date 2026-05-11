<?php
/**
 * Page:      admin/audit.php
 * Component: Admin Panel — Audit Log Viewer (Anonymized)
 * Developer: Bibek Timsena (Audit & History Log)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Filters
$filter_action = trim($_GET['action_type'] ?? '');
$filter_from   = trim($_GET['date_from']   ?? '');
$filter_to     = trim($_GET['date_to']     ?? '');

$where  = 'WHERE 1=1';
$params = [];

if (in_array($filter_action, ['CREATE', 'UPDATE', 'DELETE'])) {
    $where    .= ' AND a.action_type = ?';
    $params[]  = $filter_action;
}
if ($filter_from !== '') {
    $where    .= ' AND a.action_date >= ?';
    $params[]  = $filter_from;
}
if ($filter_to !== '') {
    $where    .= ' AND a.action_date <= ?';
    $params[]  = $filter_to;
}

// Anonymized query: No user joins, no old_value selected
$stmt = $pdo->prepare(
    "SELECT a.log_id, a.expense_id, a.action_type, a.action_date, a.is_reviewed
     FROM tblAuditLog a
     $where
     ORDER BY a.action_date DESC, a.log_id DESC"
);
$stmt->execute($params);
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Audit Log</h1>
    <a href="/smartspend/admin/dashboard.php" class="btn-secondary">← Admin Home</a>
</div>

<div class="alert alert-info">
    <strong>Privacy Notice:</strong> To comply with privacy principles, user identities and specific expense data snapshots have been removed from this audit view.
</div>

<!-- Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-top:20px;">
    <div class="form-group">
        <label for="filter-action">Action</label>
        <select id="filter-action" name="action_type">
            <option value="">All actions</option>
            <option value="CREATE" <?= $filter_action === 'CREATE' ? 'selected' : '' ?>>CREATE</option>
            <option value="UPDATE" <?= $filter_action === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
            <option value="DELETE" <?= $filter_action === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
        </select>
    </div>
    <div class="form-group">
        <label for="filter-from">From</label>
        <input type="date" id="filter-from" name="date_from" value="<?= htmlspecialchars($filter_from, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="filter-to">To</label>
        <input type="date" id="filter-to" name="date_to" value="<?= htmlspecialchars($filter_to, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group" style="flex:0;">
        <label>&nbsp;</label>
        <button type="submit" class="btn-primary">Apply</button>
    </div>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Expense ID</th>
                <th>Action</th>
                <th>Date</th>
                <th>Reviewed</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No log entries found.</td></tr>
            <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= $log['log_id'] ?></td>
                <td><?= $log['expense_id'] ?></td>
                <td>
                    <?php
                        $badge_class = match($log['action_type']) {
                            'CREATE' => 'badge-create',
                            'UPDATE' => 'badge-update',
                            'DELETE' => 'badge-delete',
                            default  => 'badge-inactive',
                        };
                    ?>
                    <span class="badge <?= $badge_class ?>">
                        <?= htmlspecialchars($log['action_type'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($log['action_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ((int)$log['is_reviewed'] === 1): ?>
                        <span class="badge badge-reviewed">Reviewed</span>
                    <?php else: ?>
                        <form method="POST" action="/smartspend/admin/review_log.php"
                              id="form-review-<?= $log['log_id'] ?>">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-secondary btn-sm"
                                    id="btn-review-<?= $log['log_id'] ?>">Mark reviewed</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
