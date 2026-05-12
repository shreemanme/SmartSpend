<?php
/**
 * Page:      reports/index.php
 * Component: Reports — Generate & View
 * Developer: Suraj Rai (Reporting & Analytics)
 *
 * OOP REWRITE:
 * The procedural report-loading and report-listing logic has been
 * converted into two classes: ReportLoader and ReportFilter.
 * The HTML template is unchanged.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// ════════════════════════════════════════════════════════════════════
//  CLASS: ReportLoader
//
//  What it is:
//    The blueprint for an object that loads a single saved report
//    by its ID, then fetches its expense rows and category breakdown.
//
//  How to use it:
//    $loader = new ReportLoader($uid, $reportId, $pdo);  // create object
//    $report = $loader->getReport();                       // get meta row
//    $rows   = $loader->getRows();                         // get expenses
// ════════════════════════════════════════════════════════════════════
class ReportLoader
{
    // ── Properties ──────────────────────────────────────────────────

    private int   $userId;
    private int   $reportId;
    private \PDO  $pdo;
    private ?array $report;  // The report meta row, or null if not found

    // ── Constructor ──────────────────────────────────────────────────
    // Loads the report meta row immediately.

    public function __construct(int $userId, int $reportId, \PDO $pdo)
    {
        $this->userId   = $userId;
        $this->reportId = $reportId;
        $this->pdo      = $pdo;

        $stmt = $pdo->prepare('SELECT * FROM tblReport WHERE report_id = ? AND user_id = ?');
        $stmt->execute([$reportId, $userId]);
        $row = $stmt->fetch();
        $this->report = $row ?: null;
    }

    // ── Public method: getReport() ───────────────────────────────────
    // Returns the report meta row (report_name, date_from, date_to, etc.)
    // or null if the report was not found.
    // Called as: $loader->getReport()

    public function getReport(): ?array
    {
        return $this->report;
    }

    // ── Public method: getRows() ─────────────────────────────────────
    // Returns the expense rows that fall within the report's date range.
    // Returns an empty array if the report meta row is null.
    // Called as: $loader->getRows()

    public function getRows(): array
    {
        if (!$this->report) {
            return [];
        }
        $stmt = $this->pdo->prepare(
            'SELECT e.expense_id, e.amount, e.expense_date, e.description, c.category_name
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             WHERE e.user_id = ? AND e.is_deleted = 0
               AND e.expense_date BETWEEN ? AND ?
             ORDER BY e.expense_date ASC'
        );
        $stmt->execute([$this->userId, $this->report['date_from'], $this->report['date_to']]);
        return $stmt->fetchAll();
    }

    // ── Public method: getCategoryBreakdown() ────────────────────────
    // Returns the per-category subtotals for the report's date range.
    // Called as: $loader->getCategoryBreakdown()

    public function getCategoryBreakdown(): array
    {
        if (!$this->report) {
            return [];
        }
        $stmt = $this->pdo->prepare(
            'SELECT c.category_name, SUM(e.amount) AS subtotal
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             WHERE e.user_id = ? AND e.is_deleted = 0
               AND e.expense_date BETWEEN ? AND ?
             GROUP BY e.category_id
             ORDER BY subtotal DESC'
        );
        $stmt->execute([$this->userId, $this->report['date_from'], $this->report['date_to']]);
        return $stmt->fetchAll();
    }
}

// ════════════════════════════════════════════════════════════════════
//  CLASS: ReportFilter
//
//  What it is:
//    The blueprint for an object that reads the search/date filters
//    from the URL and fetches the matching saved reports.
//
//  How to use it:
//    $filter = new ReportFilter($uid, $_GET);      // create the object
//    $reports = $filter->getReports($pdo);          // fetch matching rows
//    $search  = $filter->getSearch();               // read a filter value
// ════════════════════════════════════════════════════════════════════
class ReportFilter
{
    // ── Properties ──────────────────────────────────────────────────

    private int    $userId;
    private string $search;
    private string $filterFrom;
    private string $filterTo;

    // ── Constructor ──────────────────────────────────────────────────

    public function __construct(int $userId, array $get = [])
    {
        $this->userId     = $userId;
        $this->search     = trim($get['search']      ?? '');
        $this->filterFrom = trim($get['filter_from'] ?? '');
        $this->filterTo   = trim($get['filter_to']   ?? '');
    }

    // ── Public method: getReports() ──────────────────────────────────
    // Returns saved reports matching the current filters.
    // Called as: $filter->getReports($pdo)

    public function getReports(\PDO $pdo): array
    {
        $where  = ['user_id = ?'];
        $params = [$this->userId];

        if ($this->search !== '') {
            $where[]  = 'report_name LIKE ?';
            $params[] = "%{$this->search}%";
        }
        if ($this->filterFrom !== '') {
            $where[]  = 'generated_date >= ?';
            $params[] = $this->filterFrom;
        }
        if ($this->filterTo !== '') {
            $where[]  = 'generated_date <= ?';
            $params[] = $this->filterTo;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        $stmt = $pdo->prepare("SELECT * FROM tblReport $whereClause ORDER BY generated_date DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Getter methods ───────────────────────────────────────────────

    public function getSearch(): string     { return $this->search; }
    public function getFilterFrom(): string { return $this->filterFrom; }
    public function getFilterTo(): string   { return $this->filterTo; }

    public function hasActiveFilter(): bool
    {
        return $this->search !== '' || $this->filterFrom !== '' || $this->filterTo !== '';
    }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECTS & CALLING METHODS
// ════════════════════════════════════════════════════════════════════

// If a report_id is in the URL, load that specific report
$report_id = isset($_GET['report_id']) ? (int)$_GET['report_id'] : null;

$report        = null;
$report_rows   = [];
$report_total  = 0;
$cat_breakdown = [];

if ($report_id !== null) {
    $loader        = new ReportLoader($uid, $report_id, $pdo);
    $report        = $loader->getReport();
    $report_rows   = $loader->getRows();
    $report_total  = array_sum(array_column($report_rows, 'amount'));
    $cat_breakdown = $loader->getCategoryBreakdown();
}

// List / filter the saved reports
$reportFilter = new ReportFilter($uid, $_GET);
$prev_reports = $reportFilter->getReports($pdo);

// Unpack values for the HTML template (same variable names as before)
$search      = $reportFilter->getSearch();
$filter_from = $reportFilter->getFilterFrom();
$filter_to   = $reportFilter->getFilterTo();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Reports</h1>
</div>

<!-- Report Generation Form -->
<div class="form-card">
    <h2>Generate New Report</h2>
    <form method="POST" action="/smartspend/reports/generate.php" novalidate>
        <div class="form-group">
            <label for="report_name">Report name</label>
            <input type="text" id="report_name" name="report_name"
                   placeholder="e.g. April 2025 Expenses" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" required>
            </div>
            <div class="form-group">
                <label for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" id="btn-generate-report" class="btn-primary">Generate Report</button>
        </div>
    </form>
</div>

<?php if ($report): ?>
<!-- Report Results -->
<h2 class="section-title">
    <?= htmlspecialchars($report['report_name'], ENT_QUOTES, 'UTF-8') ?>
    <span class="text-muted" style="font-size:14px; font-weight:400;">
        — <?= htmlspecialchars($report['date_from'], ENT_QUOTES, 'UTF-8') ?>
        to <?= htmlspecialchars($report['date_to'], ENT_QUOTES, 'UTF-8') ?>
    </span>
</h2>

<?php if (empty($report_rows)): ?>
    <p class="text-muted">No expenses found for this date range.</p>
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
            <?php foreach ($report_rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['expense_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>£<?= number_format((float)$row['amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="grand-total-row">
                <td colspan="3"><strong>Grand Total</strong></td>
                <td><strong>£<?= number_format($report_total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Category Breakdown -->
<h3 class="section-title" style="margin-top:24px;">Category Breakdown</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr><th>Category</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cat_breakdown as $cb): ?>
            <tr>
                <td><?= htmlspecialchars($cb['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>£<?= number_format((float)$cb['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mb-16">
    <a href="/smartspend/reports/export.php?id=<?= $report_id ?>"
       class="btn-secondary"
       id="btn-export-csv-<?= $report_id ?>">⬇ Export CSV</a>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Previous Reports -->
<h2 class="section-title">Previous Reports</h2>

<!-- Filter & Search Bar for Reports -->
<form method="GET" action="" class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end;">
    <div class="form-group" style="flex: 1;">
        <label for="search">Find Report (Name)</label>
        <input type="text" id="search" name="search" placeholder="Search name..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="filter_from">Filter From (Generated)</label>
        <input type="date" id="filter_from" name="filter_from" value="<?= htmlspecialchars($filter_from, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="filter_to">Filter To (Generated)</label>
        <input type="date" id="filter_to" name="filter_to" value="<?= htmlspecialchars($filter_to, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group" style="flex: 0;">
        <button type="submit" class="btn-primary">Apply</button>
    </div>
    <?php if ($reportFilter->hasActiveFilter()): ?>
    <div class="form-group" style="flex: 0;">
        <a href="/smartspend/reports/index.php" class="btn-secondary" style="display:inline-block; padding:8px 12px; text-decoration:none;">Clear</a>
    </div>
    <?php endif; ?>
</form>

<?php if (empty($prev_reports)): ?>
    <p class="text-muted">No reports generated yet.</p>
<?php else: ?>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Report Name</th>
                <th>From</th>
                <th>To</th>
                <th>Generated</th>
                <th>Exported</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prev_reports as $pr): ?>
            <tr>
                <td><?= htmlspecialchars($pr['report_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($pr['date_from'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($pr['date_to'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($pr['generated_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ((int)$pr['is_exported'] === 1): ?>
                        <span class="badge badge-success">Exported</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Not exported</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions-cell">
                        <a href="?report_id=<?= $pr['report_id'] ?>" class="btn-secondary btn-sm">View</a>
                        <a href="/smartspend/reports/edit.php?id=<?= $pr['report_id'] ?>" class="btn-warning btn-sm">Edit</a>
                        <form method="POST" action="/smartspend/reports/delete.php" style="display:inline;">
                            <input type="hidden" name="report_id" value="<?= $pr['report_id'] ?>">
                            <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Delete this report?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
