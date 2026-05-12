<?php
/**
 * Page:      dashboard/index.php
 * Component: User Dashboard
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 *
 * OOP REWRITE:
 * The procedural stats queries have been converted into the
 * DashboardStats class below. The HTML template is unchanged.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// ════════════════════════════════════════════════════════════════════
//  CLASS: DashboardStats
//
//  What it is:
//    The blueprint for an object that runs all four dashboard queries:
//    total spent this month, expense count, top category, and the
//    five most recent expenses.
//
//  How to use it:
//    $stats = new DashboardStats($uid, $pdo);     // create the object
//    $total = $stats->getTotalThisMonth();         // call a method
//    $recent = $stats->getRecentExpenses();        // call another method
// ════════════════════════════════════════════════════════════════════
class DashboardStats
{
    // ── Properties ──────────────────────────────────────────────────

    private int  $userId;
    private \PDO $pdo;

    // ── Constructor ──────────────────────────────────────────────────
    // Stores the user ID and database connection for use in the methods.

    public function __construct(int $userId, \PDO $pdo)
    {
        $this->userId = $userId;
        $this->pdo    = $pdo;
    }

    // ── Public method: getTotalThisMonth() ───────────────────────────
    // Returns the total amount spent in the current calendar month.
    // Called as: $stats->getTotalThisMonth()

    public function getTotalThisMonth(): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM tblExpense
             WHERE user_id = ? AND is_deleted = 0
               AND MONTH(expense_date) = MONTH(CURDATE())
               AND YEAR(expense_date)  = YEAR(CURDATE())'
        );
        $stmt->execute([$this->userId]);
        return (float)$stmt->fetchColumn();
    }

    // ── Public method: getCountThisMonth() ───────────────────────────
    // Returns the number of expenses recorded in the current month.
    // Called as: $stats->getCountThisMonth()

    public function getCountThisMonth(): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tblExpense
             WHERE user_id = ? AND is_deleted = 0
               AND MONTH(expense_date) = MONTH(CURDATE())
               AND YEAR(expense_date)  = YEAR(CURDATE())'
        );
        $stmt->execute([$this->userId]);
        return (int)$stmt->fetchColumn();
    }

    // ── Public method: getTopCategory() ─────────────────────────────
    // Returns the category with the highest total spend this month,
    // or null if there are no expenses yet.
    // Called as: $stats->getTopCategory()

    public function getTopCategory(): ?array
    {
        $stmt = $this->pdo->prepare(
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
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Public method: getRecentExpenses() ───────────────────────────
    // Returns the 5 most recent expenses for the dashboard table.
    // Called as: $stats->getRecentExpenses()

    public function getRecentExpenses(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.expense_id, e.amount, e.expense_date, e.description,
                    c.category_name
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             WHERE e.user_id = ? AND e.is_deleted = 0
             ORDER BY e.expense_date DESC, e.expense_id DESC
             LIMIT 5'
        );
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
//
//  new DashboardStats($uid, $pdo)
//    → Creates one object from the DashboardStats blueprint.
//
//  $stats->getTotalThisMonth()
//    → Calls that method on the $stats object using the -> arrow.
// ════════════════════════════════════════════════════════════════════

$stats = new DashboardStats($uid, $pdo);

$total_month = $stats->getTotalThisMonth();
$count_month = $stats->getCountThisMonth();
$top_cat     = $stats->getTopCategory();
$recent      = $stats->getRecentExpenses();

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
