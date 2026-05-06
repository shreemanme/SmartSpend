<?php
/**
 * Page:      admin/reports.php
 * Component: Admin Panel — Reports Overview (Read-only)
 * Developer: Suraj Rai (Reporting & Analytics)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$filter_user = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;

$where  = 'WHERE 1=1';
$params = [];

if ($filter_user !== null) {
    $where    .= ' AND r.user_id = ?';
    $params[]  = $filter_user;
}

$stmt = $pdo->prepare(
    "SELECT r.*, u.full_name
     FROM tblReport r
     JOIN tblUser u ON r.user_id = u.user_id
     $where
     ORDER BY r.generated_date DESC"
);
$stmt->execute($params);
$reports = $stmt->fetchAll();

$users = $pdo->query('SELECT user_id, full_name FROM tblUser ORDER BY full_name')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>All Reports</h1>
    <a href="/smartspend/admin/dashboard.php" class="btn-secondary">← Admin Home</a>
</div>

<!-- Filter -->
<form method="GET" action="" class="filter-bar">
    <div class="form-group">
        <label for="filter-user">User</label>
        <select id="filter-user" name="user_id">
            <option value="">All users</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['user_id'] ?>" <?= $filter_user === (int)$u['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="flex:0;">
        <label>&nbsp;</label>
        <button type="submit" class="btn-primary">Filter</button>
    </div>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Report Name</th>
                <th>From</th>
                <th>To</th>
                <th>Generated</th>
                <th>Exported</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reports)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No reports found.</td></tr>
            <?php else: ?>
            <?php foreach ($reports as $r): ?>
            <tr>
                <td><?= $r['report_id'] ?></td>
                <td><?= htmlspecialchars($r['full_name'],    ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['report_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['date_from'],   ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['date_to'],     ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['generated_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ((int)$r['is_exported'] === 1): ?>
                        <span class="badge badge-success">Exported</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Not exported</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
