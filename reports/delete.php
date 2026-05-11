<?php
/**
 * Page:      reports/delete.php
 * Component: Reports — Delete
 * Developer: Suraj Rai (Reporting & Analytics)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = (int)$_POST['report_id'];
    $uid = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM tblReport WHERE report_id = ? AND user_id = ?");
    if ($stmt->execute([$report_id, $uid])) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Report deleted successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Failed to delete report.'];
    }
}

header('Location: /smartspend/reports/index.php');
exit;
