<?php
/**
 * Page:      reports/index.php
 * Component: Reports — Generate & View
 * Developer: Shreeman Bhandari
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// If a report_id is passed, load and display it
$report_id      = isset($_GET['report_id']) ? (int)$_GET['report_id'] : null;
$report         = null;
$report_rows    = [];
$report_total   = 0;
$cat_breakdown  = [];

if ($report_id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM tblReport WHERE report_id = ? AND user_id = ?');
    $stmt->execute([$report_id, $uid]);
    $report = $stmt->fetch();

    if ($report) {
        // Expense rows
        $stmt = $pdo->prepare(
            'SELECT e.expense_id, e.amount, e.expense_date, e.description, c.category_name
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             WHERE e.user_id = ? AND e.is_deleted = 0
               AND e.expense_date BETWEEN ? AND ?
             ORDER BY e.expense_date ASC'
        );
        $stmt->execute([$uid, $report['date_from'], $report['date_to']]);
        $report_rows  = $stmt->fetchAll();
        $report_total = array_sum(array_column($report_rows, 'amount'));

        // Category breakdown
        $stmt = $pdo->prepare(
            'SELECT c.category_name, SUM(e.amount) AS subtotal
             FROM tblExpense e
             JOIN tblCategory c ON e.category_id = c.category_id
             WHERE e.user_id = ? AND e.is_deleted = 0
               AND e.expense_date BETWEEN ? AND ?
             GROUP BY e.category_id
             ORDER BY subtotal DESC'
        );
        $stmt->execute([$uid, $report['date_from'], $report['date_to']]);
        $cat_breakdown = $stmt->fetchAll();
    }
}

// Previous reports
$prev_stmt = $pdo->prepare('SELECT * FROM tblReport WHERE user_id = ? ORDER BY generated_date DESC');
$prev_stmt->execute([$uid]);
$prev_reports = $prev_stmt->fetchAll();

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
                <th>View</th>
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
                    <a href="?report_id=<?= $pr['report_id'] ?>"
                       class="btn-secondary btn-sm"
                       id="btn-view-report-<?= $pr['report_id'] ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
