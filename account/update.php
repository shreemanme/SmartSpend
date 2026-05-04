<?php
/**
 * Page:      account/update.php
 * Component: Account Management — Update Details
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

$uid       = (int)$_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email']     ?? '');

if (empty($full_name)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Full name cannot be empty.'];
    header('Location: /smartspend/account/index.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Please enter a valid email address.'];
    header('Location: /smartspend/account/index.php');
    exit;
}

// Check email uniqueness (exclude self)
$stmt = $pdo->prepare('SELECT COUNT(*) FROM tblUser WHERE email = ? AND user_id != ?');
$stmt->execute([$email, $uid]);
if ((int)$stmt->fetchColumn() > 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'That email address is already in use.'];
    header('Location: /smartspend/account/index.php');
    exit;
}

$pdo->prepare('UPDATE tblUser SET full_name = ?, email = ? WHERE user_id = ?')
    ->execute([$full_name, $email, $uid]);

$_SESSION['full_name'] = $full_name;

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Account details updated.'];
header('Location: /smartspend/account/index.php');
exit;
