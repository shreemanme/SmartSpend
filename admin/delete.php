<?php
/**
 * Page:      admin/delete.php
 * Component: Admin Dashboard — Delete User
 * Purpose:   Post-only action to delete a user and cascade delete their data.
 */

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $logged_in_admin = (int)$_SESSION['user_id'];

    if ($user_id === 0) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Invalid user ID.'
        ];
        header('Location: /smartspend/admin/index.php');
        exit;
    }

    // Safety: prevent admin from deleting themselves
    if ($user_id === $logged_in_admin) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Safety block: You cannot delete your own administrator account.'
        ];
        header('Location: /smartspend/admin/index.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Delete audit logs related to user or user's expenses
        $stmt = $pdo->prepare('DELETE FROM tblAuditLog WHERE user_id = ? OR expense_id IN (SELECT expense_id FROM tblExpense WHERE user_id = ?)');
        $stmt->execute([$user_id, $user_id]);

        // 2. Delete reports
        $stmt = $pdo->prepare('DELETE FROM tblReport WHERE user_id = ?');
        $stmt->execute([$user_id]);

        // 3. Delete expenses
        $stmt = $pdo->prepare('DELETE FROM tblExpense WHERE user_id = ?');
        $stmt->execute([$user_id]);

        // 4. Delete user account
        $stmt = $pdo->prepare('DELETE FROM tblUser WHERE user_id = ?');
        $stmt->execute([$user_id]);

        $pdo->commit();

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'User and all associated data have been permanently deleted.'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'An error occurred while deleting the user: ' . $e->getMessage()
        ];
    }
}

header('Location: /smartspend/admin/index.php');
exit;
