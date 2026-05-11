<?php
/**
 * Page:      history/delete.php
 * Component: Audit Log — Delete
 * Developer: Bibek Timsena (Audit & History Log)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $log_id = (int)$_POST['log_id'];
    $uid = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM tblAuditLog WHERE log_id = ? AND user_id = ?");
    if ($stmt->execute([$log_id, $uid])) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Log entry deleted.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed to delete log.'];
    }
}
header('Location: /smartspend/history/index.php');
exit;
