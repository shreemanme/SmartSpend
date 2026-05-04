<?php
/**
 * Page:      account/password.php
 * Component: Account Management — Change Password
 * Developer: Shreeman Bhandari
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/account/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid              = (int)$_SESSION['user_id'];
$current_password = $_POST['current_password']   ?? '';
$new_password     = $_POST['new_password']        ?? '';
$confirm          = $_POST['confirm_new_password'] ?? '';

// Fetch current hash
$stmt = $pdo->prepare('SELECT password_hash FROM tblUser WHERE user_id = ?');
$stmt->execute([$uid]);
$row = $stmt->fetch();

if (!password_verify($current_password, $row['password_hash'])) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Current password is incorrect.'];
    header('Location: /smartspend/account/index.php');
    exit;
}

if ($new_password !== $confirm) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'New passwords do not match.'];
    header('Location: /smartspend/account/index.php');
    exit;
}

if (strlen($new_password) < 8) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'New password must be at least 8 characters.'];
    header('Location: /smartspend/account/index.php');
    exit;
}

$new_hash = password_hash($new_password, PASSWORD_BCRYPT);
$pdo->prepare('UPDATE tblUser SET password_hash = ? WHERE user_id = ?')
    ->execute([$new_hash, $uid]);

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password changed successfully.'];
header('Location: /smartspend/account/index.php');
exit;
