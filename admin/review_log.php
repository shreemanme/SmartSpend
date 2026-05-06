<?php
/**
 * Page:      admin/review_log.php
 * Component: Admin Panel — Mark Audit Entry Reviewed
 * Developer: Bibek Timsena
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/admin/audit.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$log_id = (int)($_POST['log_id'] ?? 0);
if ($log_id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid log entry.'];
    header('Location: /smartspend/admin/audit.php');
    exit;
}

// is_reviewed can only be set to 1 — never back to 0
$pdo->prepare('UPDATE tblAuditLog SET is_reviewed = 1 WHERE log_id = ?')->execute([$log_id]);

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Log entry marked as reviewed.'];
header('Location: /smartspend/admin/audit.php');
exit;
