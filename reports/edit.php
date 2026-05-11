<?php
/**
 * Page:      reports/edit.php
 * Component: Reports — Edit
 * Developer: Suraj Rai (Reporting & Analytics)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = (int)$_POST['report_id'];
    $report_name = trim($_POST['report_name']);

    if ($report_name !== '') {
        $stmt = $pdo->prepare("UPDATE tblReport SET report_name = ? WHERE report_id = ? AND user_id = ?");
        $stmt->execute([$report_name, $report_id, $uid]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Report updated successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Report name cannot be empty.'];
    }
    header('Location: /smartspend/reports/index.php');
    exit;
}

$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM tblReport WHERE report_id = ? AND user_id = ?");
$stmt->execute([$report_id, $uid]);
$report = $stmt->fetch();

if (!$report) {
    header('Location: /smartspend/reports/index.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Report</h1>
    <a href="/smartspend/reports/index.php" class="btn-secondary">← Back</a>
</div>

<div class="form-card">
    <form method="POST" action="">
        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
        <div class="form-group">
            <label for="report_name">Report Name</label>
            <input type="text" id="report_name" name="report_name" value="<?= htmlspecialchars($report['report_name'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Update Report</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
