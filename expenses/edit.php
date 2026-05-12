<?php
/**
 * Page:      expenses/edit.php
 * Component: Expense Entry Management — Edit Expense
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 *
 * OOP REWRITE:
 * The procedural edit-expense logic has been converted into the
 * ExpenseEditForm class below. The HTML form template is unchanged.
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

// ════════════════════════════════════════════════════════════════════
//  CLASS: ExpenseEditForm
//
//  What it is:
//    The blueprint for an object that loads an existing expense,
//    populates the edit form, validates submitted changes, and
//    saves them with an audit log entry.
//
//  How to use it:
//    $form = new ExpenseEditForm($uid, $id, $pdo);  // create object
//    $form->handlePost($_POST);                       // process form
//    $amount = $form->getAmount();                    // read a value
// ════════════════════════════════════════════════════════════════════
class ExpenseEditForm
{
    // ── Properties ──────────────────────────────────────────────────

    private int    $userId;
    private int    $expenseId;
    private \PDO   $pdo;
    private array  $fieldErrors;
    private string $amount;
    private string $categoryId;
    private string $expenseDate;
    private string $description;
    private array  $categories;
    private array  $existing;    // The original row from the database

    // ── Constructor ──────────────────────────────────────────────────
    // Loads the existing expense and the category dropdown list.
    // Redirects immediately if the expense does not belong to this user.

    public function __construct(int $userId, int $expenseId, \PDO $pdo)
    {
        $this->userId     = $userId;
        $this->expenseId  = $expenseId;
        $this->pdo        = $pdo;
        $this->fieldErrors = [];

        // Load the expense — ownership check prevents editing others' data
        $stmt = $pdo->prepare(
            'SELECT * FROM tblExpense
             WHERE expense_id = ? AND user_id = ? AND is_deleted = 0'
        );
        $stmt->execute([$expenseId, $userId]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Expense not found.'];
            header('Location: /smartspend/expenses/index.php');
            exit;
        }

        $this->existing     = $existing;
        $this->amount       = $existing['amount'];
        $this->categoryId   = $existing['category_id'];
        $this->expenseDate  = $existing['expense_date'];
        $this->description  = $existing['description'] ?? '';

        // Load categories for the dropdown
        $cat = $pdo->prepare(
            'SELECT category_id, category_name
             FROM tblCategory
             WHERE is_active = 1 AND user_id = ?
             ORDER BY category_name'
        );
        $cat->execute([$userId]);
        $this->categories = $cat->fetchAll();
    }

    // ── Private method: validate() ───────────────────────────────────
    // Checks the submitted values and populates $this->fieldErrors.

    private function validate(): void
    {
        if ($this->amount === '' || (float)$this->amount <= 0) {
            $this->fieldErrors['amount'] = 'Please enter a valid amount greater than 0.';
        }
        if ($this->categoryId === '') {
            $this->fieldErrors['category_id'] = 'Please select a category.';
        }
        if ($this->expenseDate === '') {
            $this->fieldErrors['expense_date'] = 'Please enter a date.';
        } elseif ($this->expenseDate > date('Y-m-d')) {
            $this->fieldErrors['expense_date'] = 'Expense date cannot be in the future.';
        }
    }

    // ── Private method: update() ─────────────────────────────────────
    // Saves the validated changes to the database and writes an audit log.

    private function update(): void
    {
        $old = json_encode($this->existing);

        $this->pdo->prepare(
            'UPDATE tblExpense
             SET amount = ?, category_id = ?, expense_date = ?, description = ?
             WHERE expense_id = ? AND user_id = ?'
        )->execute([
            (float)$this->amount,
            (int)$this->categoryId,
            $this->expenseDate,
            $this->description,
            $this->expenseId,
            $this->userId,
        ]);

        // Audit log — UPDATE
        $this->pdo->prepare(
            'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
             VALUES (?, ?, \'UPDATE\', CURDATE(), ?, 0)'
        )->execute([$this->userId, $this->expenseId, $old]);
    }

    // ── Public method: handlePost() ──────────────────────────────────
    // Reads submitted data, validates it, and updates the record if valid.
    // Called as: $form->handlePost($_POST)

    public function handlePost(array $post): void
    {
        $this->amount      = trim($post['amount']       ?? '');
        $this->categoryId  = trim($post['category_id']  ?? '');
        $this->expenseDate = trim($post['expense_date']  ?? '');
        $this->description = trim($post['description']   ?? '');

        $this->validate();

        if (empty($this->fieldErrors)) {
            $this->update();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Expense updated.'];
            header('Location: /smartspend/expenses/index.php');
            exit;
        }
    }

    // ── Getter methods ───────────────────────────────────────────────

    public function getCategories(): array  { return $this->categories; }
    public function getFieldErrors(): array { return $this->fieldErrors; }
    public function getAmount(): string     { return (string)$this->amount; }
    public function getCategoryId(): string { return (string)$this->categoryId; }
    public function getExpenseDate(): string{ return $this->expenseDate; }
    public function getDescription(): string{ return $this->description; }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
// ════════════════════════════════════════════════════════════════════

$form = new ExpenseEditForm($uid, $id, $pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->handlePost($_POST);
}

// Unpack values for the HTML template (same variable names as before)
$categories   = $form->getCategories();
$field_errors = $form->getFieldErrors();
$amount       = $form->getAmount();
$category_id  = $form->getCategoryId();
$expense_date = $form->getExpenseDate();
$description  = $form->getDescription();

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
