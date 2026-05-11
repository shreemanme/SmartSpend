<?php
/**
 * Page:      admin/expenses.php
 * Component: Admin Panel — Platform Expense Statistics (Anonymized)
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Filters
$filter_category = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$filter_from     = trim($_GET['date_from']   ?? '');
$filter_to       = trim($_GET['date_to']     ?? '');

$where  = 'WHERE e.is_deleted = 0';
$params = [];

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

// Anonymized Aggregate Query
$stmt = $pdo->prepare(
    "SELECT e.expense_date, c.category_name, COUNT(e.expense_id) as total_transactions, SUM(e.amount) as total_amount
     FROM tblExpense e
     JOIN tblCategory c ON e.category_id = c.category_id
     $where
     GROUP BY e.expense_date, c.category_name
     ORDER BY e.expense_date DESC, c.category_name ASC"
);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

// Dropdowns
$categories = $pdo->query('SELECT category_id, category_name FROM tblCategory ORDER BY category_name')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Platform Activity Statistics</h1>
    <a href="/smartspend/admin/dashboard.php" class="btn-secondary">← Admin Home</a>
</div>

<div class="alert alert-info">
    <strong>Privacy Notice:</strong> To comply with data minimization and privacy principles, individual user expenses are no longer accessible. This view shows anonymized, aggregated platform statistics.
</div>

<!-- Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-top:20px;">
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
    <div class="form-group" style="flex:0;">
        <label>&nbsp;</label>
        <button type="submit" class="btn-primary">Apply</button>
    </div>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Total Transactions</th>
                <th>Total Volume (£)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($expenses)): ?>
            <tr><td colspan="4" class="text-center text-muted" style="padding:20px;">No platform activity found for this period.</td></tr>
            <?php else: ?>
            <?php foreach ($expenses as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['expense_date'],  ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$row['total_transactions'] ?></td>
                <td>£<?= number_format((float)$row['total_amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

