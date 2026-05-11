<?php
/**
 * Page:      account/export_data.php
 * Component: Account Management — Data Export
 * Developer: Nandan Kumar Yadav (User & Account Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare('SELECT full_name, email, created_date, role FROM tblUser WHERE user_id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all expenses
$stmt = $pdo->prepare('SELECT e.expense_id, e.amount, e.expense_date, e.description, c.category_name 
                       FROM tblExpense e 
                       JOIN tblCategory c ON e.category_id = c.category_id 
                       WHERE e.user_id = ?');
$stmt->execute([$uid]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all reports
$stmt = $pdo->prepare('SELECT report_id, report_name, date_from, date_to, generated_date 
                       FROM tblReport WHERE user_id = ?');
$stmt->execute([$uid]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$exportData = [
    'user' => $user,
    'expenses' => $expenses,
    'reports' => $reports,
    'exported_at' => date('Y-m-d H:i:s')
];

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="smartspend_data_export.json"');
echo json_encode($exportData, JSON_PRETTY_PRINT);
exit;
