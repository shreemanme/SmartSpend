<?php
/**
 * Page:      history/edit.php
 * Component: Audit Log — Edit (Acknowledge)
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
    
    // Toggle reviewed status as the "Edit" action
    $stmt = $pdo->prepare("UPDATE tblAuditLog SET is_reviewed = 1 WHERE log_id = ? AND user_id = ?");
    if ($stmt->execute([$log_id, $uid])) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Log marked as reviewed.'];
    }
}
header('Location: /smartspend/history/index.php');
exit;
