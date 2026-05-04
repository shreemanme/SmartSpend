<?php
/**
 * Page:      account/close.php
 * Component: Account Management — Close Account
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

$uid = (int)$_SESSION['user_id'];
$pdo->prepare('UPDATE tblUser SET is_active = 0 WHERE user_id = ?')->execute([$uid]);

session_unset();
session_destroy();

header('Location: /smartspend/auth/login.php');
exit;
