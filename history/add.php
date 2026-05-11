<?php
/**
 * Page:      history/add.php
 * Component: Audit Log — Add Manual Log
 * Developer: Bibek Timsena (Audit & History Log)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = trim($_POST['note']);
    $uid = (int)$_SESSION['user_id'];
    
    // Create a manual log entry. Use expense_id 0 as placeholder.
    if ($note !== '') {
        $stmt = $pdo->prepare("INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed) VALUES (?, 0, 'MANUAL', CURDATE(), ?, 1)");
        $stmt->execute([$uid, json_encode(['note' => $note])]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Manual log added.'];
    }
    header('Location: /smartspend/history/index.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Add Log Entry</h1>
    <a href="/smartspend/history/index.php" class="btn-secondary">← Back</a>
</div>

<div class="form-card">
    <form method="POST" action="">
        <div class="form-group">
            <label for="note">Manual Note</label>
            <input type="text" id="note" name="note" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Note</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
