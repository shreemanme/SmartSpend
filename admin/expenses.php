<?php
// admin/expenses.php — Anonymized platform expense stats via AdminExpenseStats.

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Reads category/date filters and returns anonymized aggregate expense stats.
class AdminExpenseStats
{
    private ?int   $filterCategory;
    private string $filterFrom;
    private string $filterTo;

    public function __construct(array $get = [])
    {
        $this->filterCategory = isset($get['category_id']) && $get['category_id'] !== ''
            ? (int)$get['category_id'] : null;
        $this->filterFrom = trim($get['date_from'] ?? '');
        $this->filterTo   = trim($get['date_to']   ?? '');
    }

    // Returns anonymized, aggregated expense rows grouped by date and category.
    public function getStats(\PDO $pdo): array
    {
        $where  = 'WHERE e.is_deleted = 0';
        $params = [];

        if ($this->filterCategory !== null) {
            $where   .= ' AND e.category_id = ?';
            $params[] = $this->filterCategory;
        }
        if ($this->filterFrom !== '') {
            $where   .= ' AND e.expense_date >= ?';
            $params[] = $this->filterFrom;
        }
        if ($this->filterTo !== '') {
            $where   .= ' AND e.expense_date <= ?';
            $params[] = $this->filterTo;
        }

        $stmt = $pdo->prepare(
            "SELECT e.expense_date, c.category_name,
                    COUNT(e.expense_id) AS total_transactions,
                    SUM(e.amount) AS total_amount
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             $where
             GROUP BY e.expense_date, c.category_name
             ORDER BY e.expense_date DESC, c.category_name ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Returns all categories for the filter dropdown.
    public function getCategories(\PDO $pdo): array
    {
        return $pdo->query('SELECT category_id, category_name FROM tblCategory ORDER BY category_name')->fetchAll();
    }

    public function getFilterCategory(): ?int   { return $this->filterCategory; }
    public function getFilterFrom(): string     { return $this->filterFrom; }
    public function getFilterTo(): string       { return $this->filterTo; }
}

$stats           = new AdminExpenseStats($_GET);
$expenses        = $stats->getStats($pdo);
$categories      = $stats->getCategories($pdo);
$filter_category = $stats->getFilterCategory();
$filter_from     = $stats->getFilterFrom();
$filter_to       = $stats->getFilterTo();

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

