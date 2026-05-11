<?php
/**
 * Page:      account/delete.php
 * Component: Account Management — Permanent Deletion
 * Developer: Nandan Kumar Yadav (User & Account Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Delete audit logs for the user's expenses, and actions by the user
        $stmt = $pdo->prepare('DELETE FROM tblAuditLog WHERE user_id = ? OR expense_id IN (SELECT expense_id FROM tblExpense WHERE user_id = ?)');
        $stmt->execute([$uid, $uid]);

        // Delete reports
        $stmt = $pdo->prepare('DELETE FROM tblReport WHERE user_id = ?');
        $stmt->execute([$uid]);

        // Delete expenses
        $stmt = $pdo->prepare('DELETE FROM tblExpense WHERE user_id = ?');
        $stmt->execute([$uid]);

        // Delete user account
        $stmt = $pdo->prepare('DELETE FROM tblUser WHERE user_id = ?');
        $stmt->execute([$uid]);

        $pdo->commit();

        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'Your account and all associated data have been permanently deleted.'
        ];
        header('Location: /smartspend/index.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'An error occurred while deleting your account. Please try again.'
        ];
        header('Location: /smartspend/account/index.php');
        exit;
    }
} else {
    header('Location: /smartspend/account/index.php');
    exit;
}
