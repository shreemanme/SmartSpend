<?php
// expenses/index.php — Lists and filters expenses via the ExpenseFilter class.

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// Reads filter inputs, builds paginated SQL queries, and returns expense rows.
class ExpenseFilter
{

    private int    $userId;      // The logged-in user's ID
    private string $search;      // Search term typed in the text box
    private ?int   $categoryId;  // Selected category (null = no filter)
    private string $dateFrom;    // "From" date filter
    private string $dateTo;      // "To" date filter
    private int    $perPage;     // How many rows to show per page
    private int    $currentPage; // Which page number we are on
    private string $searchError = '';

    // Reads and sanitises $_GET filter values; initialises all properties.

    public function __construct(int $userId, array $get = [])
    {
        $this->userId      = $userId;
        $this->search      = trim($get['search']    ?? '');
        $this->categoryId  = isset($get['category_id']) && $get['category_id'] !== ''
                             ? (int)$get['category_id'] : null;
        $this->dateFrom    = trim($get['date_from'] ?? '');
        $this->dateTo      = trim($get['date_to']   ?? '');
        $this->perPage     = 10;
        $this->currentPage = max(1, (int)($get['page'] ?? 1));
    }

    // Validates the search term; sets $searchError and returns false if invalid.
    private function validate(): bool
    {
        if ($this->search === '') return true;

        if (is_numeric($this->search)) {
            $this->searchError = 'Numbers are not valid search terms for expenses. Please enter a category name.';
            return false;
        }

        return true;
    }

    // Builds the dynamic WHERE clause and bound parameters for shared use.

    private function buildWhere(): array
    {
        $where  = 'WHERE e.user_id = ? AND e.is_deleted = 0';
        $params = [$this->userId];

        if ($this->categoryId !== null) {
            $where   .= ' AND e.category_id = ?';
            $params[] = $this->categoryId;
        }
        if ($this->dateFrom !== '') {
            $where   .= ' AND e.expense_date >= ?';
            $params[] = $this->dateFrom;
        }
        if ($this->dateTo !== '') {
            $where   .= ' AND e.expense_date <= ?';
            $params[] = $this->dateTo;
        }
        if ($this->search !== '') {
            $where   .= ' AND c.category_name LIKE ?';
            $params[] = "%{$this->search}%";
        }

        // Returns both the WHERE string and the parameter array together
        return [$where, $params];
    }

    // Returns the total number of matching rows (used for pagination).

    public function countExpenses(\PDO $pdo): int
    {
        if (!$this->validate()) return 0;

        [$where, $params] = $this->buildWhere();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             $where"
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Returns the expense rows for the current page.

    public function getExpenses(\PDO $pdo): array
    {
        if (!$this->validate()) return [];

        [$where, $params] = $this->buildWhere();

        $offset = ($this->currentPage - 1) * $this->perPage;

        $stmt = $pdo->prepare(
            "SELECT e.expense_id, e.amount, e.expense_date, e.description,
                    c.category_name
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             $where
             ORDER BY e.expense_date DESC, e.expense_id DESC
             LIMIT {$this->perPage} OFFSET $offset"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Returns all active categories for the filter dropdown.

    public function getCategories(\PDO $pdo): array
    {
        $stmt = $pdo->prepare(
            'SELECT category_id, category_name
             FROM tblCategory
             WHERE is_active = 1 AND user_id = ?
             ORDER BY category_name'
        );
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    // Getters — allow the HTML template to read private filter values.

    public function getSearch(): string     { return $this->search; }
    public function getCategoryId(): ?int   { return $this->categoryId; }
    public function getDateFrom(): string   { return $this->dateFrom; }
    public function getDateTo(): string     { return $this->dateTo; }
    public function getPerPage(): int       { return $this->perPage; }
    public function getCurrentPage(): int   { return $this->currentPage; }
    public function getSearchError(): string { return $this->searchError; }

    // Returns true if any filter is active (used to show/hide Clear button)
    public function hasActiveFilter(): bool
    {
        return $this->search !== ''
            || $this->categoryId !== null
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }
}


$filter      = new ExpenseFilter($uid, $_GET);

$total_rows  = $filter->countExpenses($pdo);
$total_pages = max(1, (int)ceil($total_rows / $filter->getPerPage()));
$expenses    = $filter->getExpenses($pdo);
$categories  = $filter->getCategories($pdo);

// Unpack values for the HTML template (same variable names as before)
$search          = $filter->getSearch();
$filter_category = $filter->getCategoryId();
$filter_from     = $filter->getDateFrom();
$filter_to       = $filter->getDateTo();
$page            = $filter->getCurrentPage();
$search_error    = $filter->getSearchError();

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
        <input type="text" id="search" name="search"
               placeholder="Search category..."
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               class="<?= $search_error ? 'input-error' : '' ?>">
        <?php if ($search_error): ?>
            <span class="form-error"><?= $search_error ?></span>
        <?php endif; ?>
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
    <?php if ($filter->hasActiveFilter()): ?>
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
