<?php
/**
 * Page:      expenses/delete.php
 * Component: Expense Entry Management — Soft Delete
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

// Reject GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/expenses/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int) $_SESSION['user_id'];
$id = (int) ($_POST['expense_id'] ?? 0);

if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid expense.'];
    header('Location: /smartspend/expenses/index.php');
    exit;
}

// Ownership check
$stmt = $pdo->prepare('SELECT * FROM tblExpense WHERE expense_id = ? AND user_id = ? AND is_deleted = 0');
$stmt->execute([$id, $uid]);
$row = $stmt->fetch();

if (!$row) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Expense not found.'];
    header('Location: /smartspend/expenses/index.php');
    exit;
}

$old = json_encode($row);

// Soft delete
$pdo->prepare('UPDATE tblExpense SET is_deleted = 1 WHERE expense_id = ? AND user_id = ?')
    ->execute([$id, $uid]);

// Audit log — DELETE
$pdo->prepare(
    'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
     VALUES (?, ?, \'DELETE\', CURDATE(), ?, 0)'
)->execute([$uid, $id, $old]);

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Expense deleted.'];
header('Location: /smartspend/expenses/index.php');
exit;
