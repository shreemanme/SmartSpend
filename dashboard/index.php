<?php
/**
 * Page:      dashboard/index.php
 * Component: User Dashboard
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// Stat 1 — Total spent this month
$stmt = $pdo->prepare(
    'SELECT COALESCE(SUM(amount), 0) FROM tblExpense
     WHERE user_id = ? AND is_deleted = 0
       AND MONTH(expense_date) = MONTH(CURDATE())
       AND YEAR(expense_date)  = YEAR(CURDATE())'
);
$stmt->execute([$uid]);
$total_month = (float)$stmt->fetchColumn();

// Stat 2 — Number of expenses this month
$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM tblExpense
     WHERE user_id = ? AND is_deleted = 0
       AND MONTH(expense_date) = MONTH(CURDATE())
       AND YEAR(expense_date)  = YEAR(CURDATE())'
);
$stmt->execute([$uid]);
$count_month = (int)$stmt->fetchColumn();

// Stat 3 — Top category this month
$stmt = $pdo->prepare(
    'SELECT c.category_name, SUM(e.amount) AS total
     FROM tblExpense e
     JOIN tblCategory c ON e.category_id = c.category_id
     WHERE e.user_id = ? AND e.is_deleted = 0
       AND MONTH(e.expense_date) = MONTH(CURDATE())
       AND YEAR(e.expense_date)  = YEAR(CURDATE())
     GROUP BY e.category_id
     ORDER BY total DESC
     LIMIT 1'
);
$stmt->execute([$uid]);
$top_cat = $stmt->fetch();

// Last 5 expenses
$stmt = $pdo->prepare(
    'SELECT e.expense_id, e.amount, e.expense_date, e.description,
            c.category_name
     FROM tblExpense e
     JOIN tblCategory c ON e.category_id = c.category_id
     WHERE e.user_id = ? AND e.is_deleted = 0
     ORDER BY e.expense_date DESC, e.expense_id DESC
     LIMIT 5'
);
$stmt->execute([$uid]);
$recent = $stmt->fetchAll();

$first_name = htmlspecialchars(explode(' ', $_SESSION['full_name'])[0], ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Overview</h1>
    <a href="/smartspend/expenses/add.php" class="btn-primary" id="btn-add-expense-dash">+ Add Expense</a>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">£<?= number_format($total_month, 2) ?></div>
        <div class="stat-label">Total spent this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $count_month ?></div>
        <div class="stat-label">Expenses this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $top_cat ? htmlspecialchars($top_cat['category_name'], ENT_QUOTES, 'UTF-8') : '—' ?></div>
        <div class="stat-label">Top category this month</div>
    </div>
</div>

<!-- Recent Expenses -->
<h2 class="section-title">Recent Expenses</h2>

<?php if (empty($recent)): ?>
    <p class="text-muted">No expenses recorded yet. <a href="/smartspend/expenses/add.php">Add your first one.</a></p>
<?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['expense_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>£<?= number_format((float)$row['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-8">
        <a href="/smartspend/expenses/index.php" class="btn-secondary">View all expenses</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
