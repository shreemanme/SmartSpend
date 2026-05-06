<?php
/**
 * Page:      admin/expenses.php
 * Component: Admin Panel — Expense Overview (Read-only)
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Filters
$filter_user     = isset($_GET['user_id'])     && $_GET['user_id']     !== '' ? (int)$_GET['user_id']     : null;
$filter_category = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$filter_from     = trim($_GET['date_from']   ?? '');
$filter_to       = trim($_GET['date_to']     ?? '');
$show_deleted    = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';

$where  = 'WHERE 1=1';
$params = [];

if (!$show_deleted) {
    $where .= ' AND e.is_deleted = 0';
}
if ($filter_user !== null) {
    $where    .= ' AND e.user_id = ?';
    $params[]  = $filter_user;
}
if ($filter_category !== null) {
    $where    .= ' AND e.category_id = ?';
    $params[]  = $filter_category;
}
if ($filter_from !== '') {
    $where    .= ' AND e.expense_date >= ?';
    $params[]  = $filter_from;
}
if ($filter_to !== '') {
    $where    .= ' AND e.expense_date <= ?';
    $params[]  = $filter_to;
}

$stmt = $pdo->prepare(
    "SELECT e.*, u.full_name, c.category_name
     FROM tblExpense e
     JOIN tblUser u     ON e.user_id     = u.user_id
     JOIN tblCategory c ON e.category_id = c.category_id
     $where
     ORDER BY e.expense_date DESC, e.expense_id DESC"
);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

// Dropdowns
$users      = $pdo->query('SELECT user_id, full_name FROM tblUser ORDER BY full_name')->fetchAll();
$categories = $pdo->query('SELECT category_id, category_name FROM tblCategory ORDER BY category_name')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>All Expenses</h1>
    <a href="/smartspend/admin/dashboard.php" class="btn-secondary">← Admin Home</a>
</div>

<!-- Filter Bar -->
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
    <div class="form-group">
        <label for="filter-cat">Category</label>
        <select id="filter-cat" name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['category_id'] ?>" <?= $filter_category === (int)$c['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['category_name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
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
    <div class="form-group">
        <label>
            <input type="checkbox" name="show_deleted" value="1" <?= $show_deleted ? 'checked' : '' ?>>
            Show deleted
        </label>
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
                <th>User</th>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($expenses)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:20px;">No expenses found.</td></tr>
            <?php else: ?>
            <?php foreach ($expenses as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name'],     ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['expense_date'],  ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>£<?= number_format((float)$row['amount'], 2) ?></td>
                <td>
                    <?php if ((int)$row['is_deleted'] === 1): ?>
                        <span class="badge badge-danger">Deleted</span>
                    <?php else: ?>
                        <span class="badge badge-success">Active</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
