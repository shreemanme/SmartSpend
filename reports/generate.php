<?php
/**
 * Page:      reports/generate.php
 * Component: Reports — Generate Handler
 * Developer: Suraj Rai (Reporting & Analytics)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/reports/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid         = (int)$_SESSION['user_id'];
$report_name = trim($_POST['report_name'] ?? '');
$date_from   = trim($_POST['date_from']   ?? '');
$date_to     = trim($_POST['date_to']     ?? '');

// Validate
if (empty($report_name)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Report name is required.'];
    header('Location: /smartspend/reports/index.php');
    exit;
}
if (empty($date_from) || empty($date_to)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Both From and To dates are required.'];
    header('Location: /smartspend/reports/index.php');
    exit;
}
if ($date_to < $date_from) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => '"To" date must be on or after the "From" date.'];
    header('Location: /smartspend/reports/index.php');
    exit;
}

// Insert report record
$stmt = $pdo->prepare(
    'INSERT INTO tblReport (user_id, report_name, date_from, date_to, generated_date, is_exported)
     VALUES (?, ?, ?, ?, CURDATE(), 0)'
);
$stmt->execute([$uid, $report_name, $date_from, $date_to]);
$report_id = (int)$pdo->lastInsertId();

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Report generated successfully.'];
header('Location: /smartspend/reports/index.php?report_id=' . $report_id);
exit;
