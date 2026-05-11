<?php
/**
 * Page:      expenses/edit.php
 * Component: Expense Entry Management — Edit Expense
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? $_POST['expense_id'] ?? 0);

if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid expense.'];
    header('Location: /smartspend/expenses/index.php');
    exit;
}

// Load existing expense — ownership check
$stmt = $pdo->prepare('SELECT * FROM tblExpense WHERE expense_id = ? AND user_id = ? AND is_deleted = 0');
$stmt->execute([$id, $uid]);
$existing = $stmt->fetch();

if (!$existing) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Expense not found.'];
    header('Location: /smartspend/expenses/index.php');
    exit;
}

// Load active categories
$cat_stmt = $pdo->prepare('SELECT category_id, category_name FROM tblCategory WHERE is_active = 1 AND user_id = ? ORDER BY category_name');
$cat_stmt->execute([$uid]);
$categories = $cat_stmt->fetchAll();

$field_errors = [];
$amount       = $existing['amount'];
$category_id  = $existing['category_id'];
$expense_date = $existing['expense_date'];
$description  = $existing['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount       = trim($_POST['amount']       ?? '');
    $category_id  = trim($_POST['category_id']  ?? '');
    $expense_date = trim($_POST['expense_date']  ?? '');
    $description  = trim($_POST['description']   ?? '');

    if ($amount === '' || (float)$amount <= 0) {
        $field_errors['amount'] = 'Please enter a valid amount greater than 0.';
    }
    if ($category_id === '') {
        $field_errors['category_id'] = 'Please select a category.';
    }
    if ($expense_date === '') {
        $field_errors['expense_date'] = 'Please enter a date.';
    } elseif ($expense_date > date('Y-m-d')) {
        $field_errors['expense_date'] = 'Expense date cannot be in the future.';
    }

    if (empty($field_errors)) {
        $old = json_encode($existing);

        $update = $pdo->prepare(
            'UPDATE tblExpense
             SET amount = ?, category_id = ?, expense_date = ?, description = ?
             WHERE expense_id = ? AND user_id = ?'
        );
        $update->execute([(float)$amount, (int)$category_id, $expense_date, $description, $id, $uid]);

        // Audit log — UPDATE
        $log = $pdo->prepare(
            'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
             VALUES (?, ?, \'UPDATE\', CURDATE(), ?, 0)'
        );
        $log->execute([$uid, $id, $old]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Expense updated.'];
        header('Location: /smartspend/expenses/index.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Expense</h1>
</div>

<div class="form-card">
    <form method="POST" action="" novalidate>
        <input type="hidden" name="expense_id" value="<?= $id ?>">

        <div class="form-group">
            <label for="amount">Amount (£)</label>
            <input type="number" id="amount" name="amount"
                   value="<?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') ?>"
                   step="0.01" min="0.01" required>
            <?php if (isset($field_errors['amount'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['amount'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">— Select category —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"
                        <?= ((string)$category_id === (string)$cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($field_errors['category_id'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['category_id'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="expense_date">Date</label>
            <input type="date" id="expense_date" name="expense_date"
                   class="expense-date-input"
                   value="<?= htmlspecialchars($expense_date, ENT_QUOTES, 'UTF-8') ?>"
                   max="<?= date('Y-m-d') ?>" required>
            <?php if (isset($field_errors['expense_date'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['expense_date'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Description <span class="text-muted">(optional)</span></label>
            <input type="text" id="description" name="description"
                   value="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="255">
        </div>

        <div class="form-actions">
            <button type="submit" id="btn-update-expense" class="btn-primary">Save Changes</button>
            <a href="/smartspend/expenses/index.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
