<?php
/**
 * Page:      expenses/index.php
 * Component: Expense Entry Management — List View
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// ── Filter & Search inputs
$search          = trim($_GET['search'] ?? '');
$filter_category = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$filter_from     = trim($_GET['date_from'] ?? '');
$filter_to       = trim($_GET['date_to']   ?? '');

// ── Build WHERE clauses
$where  = 'WHERE e.user_id = ? AND e.is_deleted = 0';
$params = [$uid];

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
if ($search !== '') {
    $where    .= ' AND c.category_name LIKE ?';
    $params[]  = "%$search%";
}

// ── Pagination
$per_page    = 10;
$page        = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

$count_stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM tblExpense e
     JOIN tblCategory c ON e.category_id = c.category_id
     $where"
);
$count_stmt->execute($params);
$total_rows  = (int)$count_stmt->fetchColumn();
$total_pages = (int)ceil($total_rows / $per_page);
$total_pages = max(1, $total_pages);

// ── Main query
$stmt = $pdo->prepare(
    "SELECT e.expense_id, e.amount, e.expense_date, e.description,
            c.category_name
     FROM tblExpense e
     JOIN tblCategory c ON e.category_id = c.category_id
     $where
     ORDER BY e.expense_date DESC, e.expense_id DESC
     LIMIT $per_page OFFSET $offset"
);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

// ── Categories for filter dropdown
$cat_stmt = $pdo->prepare('SELECT category_id, category_name FROM tblCategory WHERE is_active = 1 AND user_id = ? ORDER BY category_name');
$cat_stmt->execute([$uid]);
$categories = $cat_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>My Expenses</h1>
    <a href="/smartspend/expenses/add.php" class="btn-primary" id="btn-add-expense">+ Add Expense</a>
</div>

<!-- Filter & Search Bar -->
<form method="GET" action="" class="filter-bar" id="filter-form">
    <div class="form-group">
        <label for="search">Find Expense</label>
        <input type="text" id="search" name="search" placeholder="Search category..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="filter-category">Category</label>
        <select id="filter-category" name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"
                    <?= ($filter_category === (int)$cat['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8') ?>
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
    <?php if ($search !== '' || $filter_category !== null || $filter_from !== '' || $filter_to !== ''): ?>
    <div class="form-group" style="flex:0;">
        <label>&nbsp;</label>
        <a href="/smartspend/expenses/index.php" class="btn-secondary">Clear</a>
    </div>
    <?php endif; ?>
</form>

<!-- Expense Table -->
<?php if (empty($expenses)): ?>
    <p class="text-muted">No expenses found. <a href="/smartspend/expenses/add.php">Add your first one.</a></p>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['expense_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>£<?= number_format((float)$row['amount'], 2) ?></td>
                <td>
                    <div class="actions-cell">
                        <a href="/smartspend/expenses/edit.php?id=<?= $row['expense_id'] ?>"
                           class="btn-warning btn-sm"
                           id="btn-edit-<?= $row['expense_id'] ?>">Edit</a>

                        <form method="POST"
                              action="/smartspend/expenses/delete.php"
                              class="form-delete"
                              id="form-delete-<?= $row['expense_id'] ?>">
                            <input type="hidden" name="expense_id" value="<?= $row['expense_id'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&category_id=<?= $filter_category ?? '' ?>&date_from=<?= urlencode($filter_from) ?>&date_to=<?= urlencode($filter_to) ?>">← Previous</a>
    <?php else: ?>
        <span class="disabled">← Previous</span>
    <?php endif; ?>

    <span class="current"><?= $page ?> / <?= $total_pages ?></span>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&category_id=<?= $filter_category ?? '' ?>&date_from=<?= urlencode($filter_from) ?>&date_to=<?= urlencode($filter_to) ?>">Next →</a>
    <?php else: ?>
        <span class="disabled">Next →</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
