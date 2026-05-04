<?php
/**
 * Page:      reports/export.php
 * Component: Reports — CSV Export
 * Developer: Shreeman Bhandari
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid       = (int)$_SESSION['user_id'];
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($report_id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid report.'];
    header('Location: /smartspend/reports/index.php');
    exit;
}

// Verify ownership
$stmt = $pdo->prepare('SELECT * FROM tblReport WHERE report_id = ? AND user_id = ?');
$stmt->execute([$report_id, $uid]);
$report = $stmt->fetch();

if (!$report) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Report not found.'];
    header('Location: /smartspend/reports/index.php');
    exit;
}

// Re-run the expense query
$stmt = $pdo->prepare(
    'SELECT e.expense_date, c.category_name, e.description, e.amount
     FROM tblExpense e
     JOIN tblCategory c ON e.category_id = c.category_id
     WHERE e.user_id = ? AND e.is_deleted = 0
       AND e.expense_date BETWEEN ? AND ?
     ORDER BY e.expense_date ASC'
);
$stmt->execute([$uid, $report['date_from'], $report['date_to']]);
$rows = $stmt->fetchAll();

// Mark as exported
$pdo->prepare('UPDATE tblReport SET is_exported = 1 WHERE report_id = ?')->execute([$report_id]);

// Stream CSV
$filename = 'smartspend_report_' . $report_id . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$out = fopen('php://output', 'w');
// BOM for Excel UTF-8 compatibility
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Date', 'Category', 'Description', 'Amount']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['expense_date'],
        $row['category_name'],
        $row['description'] ?? '',
        number_format((float)$row['amount'], 2),
    ]);
}

// Grand total row
$total = array_sum(array_column($rows, 'amount'));
fputcsv($out, ['', '', 'TOTAL', number_format($total, 2)]);

fclose($out);
exit;
