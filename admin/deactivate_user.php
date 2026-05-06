<?php
/**
 * Page:      admin/deactivate_user.php
 * Component: Admin Panel — Deactivate User
 * Developer: Nandan Kumar Yadav (User & Account Management)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/admin/users.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)($_POST['user_id'] ?? 0);
if ($uid === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid user.'];
    header('Location: /smartspend/admin/users.php');
    exit;
}

// Always deactivate — never hard delete
$pdo->prepare('UPDATE tblUser SET is_active = 0 WHERE user_id = ?')->execute([$uid]);

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'User deactivated.'];
header('Location: /smartspend/admin/users.php');
exit;
