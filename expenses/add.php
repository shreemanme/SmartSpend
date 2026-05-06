<?php
/**
 * Page:      expenses/add.php
 * Component: Expense Entry Management — Add Expense
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid          = (int)$_SESSION['user_id'];
$field_errors = [];
$amount       = '';
$category_id  = '';
$expense_date = '';
$description  = '';

// Load active categories
$cat_stmt = $pdo->prepare('SELECT category_id, category_name FROM tblCategory WHERE is_active = 1 ORDER BY category_name');
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount       = trim($_POST['amount']       ?? '');
    $category_id  = trim($_POST['category_id']  ?? '');
    $expense_date = trim($_POST['expense_date']  ?? '');
    $description  = trim($_POST['description']   ?? '');

    // Validate amount
    if ($amount === '' || (float)$amount <= 0) {
        $field_errors['amount'] = 'Please enter a valid amount greater than 0.';
    }

    // Validate category
    if ($category_id === '') {
        $field_errors['category_id'] = 'Please select a category.';
    }

    // Validate date
    if ($expense_date === '') {
        $field_errors['expense_date'] = 'Please enter a date.';
    } elseif ($expense_date > date('Y-m-d')) {
        $field_errors['expense_date'] = 'Expense date cannot be in the future.';
    }

    if (empty($field_errors)) {
        // Insert expense
        $stmt = $pdo->prepare(
            'INSERT INTO tblExpense (user_id, category_id, amount, expense_date, description, is_deleted)
             VALUES (?, ?, ?, ?, ?, 0)'
        );
        $stmt->execute([$uid, (int)$category_id, (float)$amount, $expense_date, $description]);
        $expense_id = (int)$pdo->lastInsertId();

        // Audit log — CREATE
        $log_stmt = $pdo->prepare(
            'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
             VALUES (?, ?, \'CREATE\', CURDATE(), NULL, 0)'
        );
        $log_stmt->execute([$uid, $expense_id]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Expense added successfully.'];
        header('Location: /smartspend/expenses/index.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Add Expense</h1>
</div>

<div class="form-card">
    <form method="POST" action="" novalidate>

        <div class="form-group">
            <label for="amount">Amount (£)</label>
            <input type="number" id="amount" name="amount"
                   value="<?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') ?>"
                   step="0.01" min="0.01" placeholder="0.00" required>
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
                   maxlength="255" placeholder="e.g. Lunch at Costa">
        </div>

        <div class="form-actions">
            <button type="submit" id="btn-add-expense-submit" class="btn-primary">Add Expense</button>
            <a href="/smartspend/expenses/index.php" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
