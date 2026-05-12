<?php
/**
 * Page:      expenses/index.php
 * Component: Expense Entry Management — List View
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 *
 * OOP REWRITE:
 * The procedural filter/query logic has been converted into the
 * ExpenseFilter class below. The HTML template is unchanged.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// ════════════════════════════════════════════════════════════════════
//  CLASS: ExpenseFilter
//
//  What it is:
//    A class is a blueprint. ExpenseFilter is the blueprint for an
//    object that reads filter inputs, builds SQL queries, and returns
//    expense data. Instead of loose variables scattered at the top of
//    the file, everything related to filtering is grouped here.
//
//  How to use it:
//    $filter = new ExpenseFilter($uid, $_GET);   // create the object
//    $rows   = $filter->getExpenses($pdo);        // call a method
// ════════════════════════════════════════════════════════════════════
class ExpenseFilter
{
    // ── Properties ──────────────────────────────────────────────────
    // A property is a variable that belongs to the class.
    // "private" means only code inside this class can read or change it.

    private int    $userId;      // The logged-in user's ID
    private string $search;      // Search term typed in the text box
    private ?int   $categoryId;  // Selected category (null = no filter)
    private string $dateFrom;    // "From" date filter
    private string $dateTo;      // "To" date filter
    private int    $perPage;     // How many rows to show per page
    private int    $currentPage; // Which page number we are on

    // ── Constructor ──────────────────────────────────────────────────
    // __construct() is a special method that runs automatically the
    // moment you write: new ExpenseFilter(...)
    // It reads and cleans the raw $_GET data so no other method has to.

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

    // ── Private helper method ────────────────────────────────────────
    // buildWhere() builds the dynamic WHERE clause and the list of
    // bound parameters. It is "private" because only getExpenses()
    // and countExpenses() need to call it — outside code never does.

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

    // ── Public method: countExpenses() ───────────────────────────────
    // Asks the database "how many rows match my filters?"
    // Returns a plain integer — used to calculate total pages.
    // Called as: $filter->countExpenses($pdo)

    public function countExpenses(\PDO $pdo): int
    {
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

    // ── Public method: getExpenses() ─────────────────────────────────
    // Fetches the expense rows for the current page.
    // Returns an array of rows — each row is one expense.
    // Called as: $filter->getExpenses($pdo)

    public function getExpenses(\PDO $pdo): array
    {
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

    // ── Public method: getCategories() ───────────────────────────────
    // Fetches all active categories for this user.
    // Returns an array used to populate the filter dropdown.
    // Called as: $filter->getCategories($pdo)

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

    // ── Getter methods ───────────────────────────────────────────────
    // Because properties are private, the HTML template cannot read
    // $filter->search directly. These getter methods act as a safe
    // read-only window into the object's data.

    public function getSearch(): string     { return $this->search; }
    public function getCategoryId(): ?int   { return $this->categoryId; }
    public function getDateFrom(): string   { return $this->dateFrom; }
    public function getDateTo(): string     { return $this->dateTo; }
    public function getPerPage(): int       { return $this->perPage; }
    public function getCurrentPage(): int   { return $this->currentPage; }

    // Returns true if any filter is active (used to show/hide Clear button)
    public function hasActiveFilter(): bool
    {
        return $this->search !== ''
            || $this->categoryId !== null
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
//
//  new ExpenseFilter($uid, $_GET)
//    → Creates one object from the ExpenseFilter blueprint.
//    → Runs __construct() automatically with the logged-in user's ID
//      and the raw URL parameters ($_GET).
//
//  $filter->countExpenses($pdo)
//    → Calls the countExpenses method on the $filter object.
//    → The arrow (->) is how you access a method or property on an object.
// ════════════════════════════════════════════════════════════════════

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
