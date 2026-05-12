<?php
/**
 * Page:      expenses/add.php
 * Component: Expense Entry Management — Add Expense
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 *
 * OOP REWRITE:
 * The procedural add-expense logic has been converted into the
 * ExpenseForm class below. The HTML form template is unchanged.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// ════════════════════════════════════════════════════════════════════
//  CLASS: ExpenseForm
//
//  What it is:
//    A class is a blueprint. ExpenseForm is the blueprint for an
//    object that handles creating a new expense: loading categories,
//    reading form input, validating it, and saving it to the database.
//
//  How to use it:
//    $form = new ExpenseForm($uid, $pdo);       // create the object
//    $form->handlePost($_POST);                  // process the form
//    $categories = $form->getCategories();       // get dropdown data
// ════════════════════════════════════════════════════════════════════
class ExpenseForm
{
    // ── Properties ──────────────────────────────────────────────────
    // These variables belong to the object and hold its data.

    private int    $userId;       // The logged-in user's ID
    private \PDO   $pdo;          // The database connection passed in
    private array  $fieldErrors;  // Validation error messages, keyed by field name
    private string $amount;       // Form value: expense amount
    private string $categoryId;   // Form value: selected category
    private string $expenseDate;  // Form value: expense date
    private string $description;  // Form value: optional description
    private array  $categories;   // List of categories for the dropdown

    // ── Constructor ──────────────────────────────────────────────────
    // Runs automatically when you write: new ExpenseForm(...)
    // Loads categories from the database immediately on creation.

    public function __construct(int $userId, \PDO $pdo)
    {
        $this->userId      = $userId;
        $this->pdo         = $pdo;
        $this->fieldErrors = [];
        $this->amount      = '';
        $this->categoryId  = '';
        $this->expenseDate = '';
        $this->description = '';

        // Load categories straight away so the dropdown is always ready
        $stmt = $pdo->prepare(
            'SELECT category_id, category_name
             FROM tblCategory
             WHERE is_active = 1 AND user_id = ?
             ORDER BY category_name'
        );
        $stmt->execute([$userId]);
        $this->categories = $stmt->fetchAll();
    }

    // ── Private method: validate() ───────────────────────────────────
    // Checks the submitted form values and fills $this->fieldErrors.
    // Private: only handlePost() calls it — nothing outside does.

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

    // ── Private method: save() ───────────────────────────────────────
    // Inserts the validated expense into the database and writes an
    // audit log entry. Private: only handlePost() calls it.

    private function save(): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tblExpense (user_id, category_id, amount, expense_date, description, is_deleted)
             VALUES (?, ?, ?, ?, ?, 0)'
        );
        $stmt->execute([
            $this->userId,
            (int)$this->categoryId,
            (float)$this->amount,
            $this->expenseDate,
            $this->description,
        ]);

        $expenseId = (int)$this->pdo->lastInsertId();

        // Audit log — CREATE
        $this->pdo->prepare(
            'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
             VALUES (?, ?, \'CREATE\', CURDATE(), NULL, 0)'
        )->execute([$this->userId, $expenseId]);
    }

    // ── Public method: handlePost() ──────────────────────────────────
    // The main entry point when the form is submitted (POST request).
    // Reads the submitted data, validates it, and saves it if valid.
    // Redirects on success; returns normally so the form can redisplay.
    // Called as: $form->handlePost($_POST)

    public function handlePost(array $post): void
    {
        $this->amount      = trim($post['amount']       ?? '');
        $this->categoryId  = trim($post['category_id']  ?? '');
        $this->expenseDate = trim($post['expense_date']  ?? '');
        $this->description = trim($post['description']   ?? '');

        $this->validate();

        if (empty($this->fieldErrors)) {
            $this->save();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Expense added successfully.'];
            header('Location: /smartspend/expenses/index.php');
            exit;
        }
    }

    // ── Getter methods ───────────────────────────────────────────────
    // Because properties are private, the HTML template uses these
    // methods to safely read the values.

    public function getCategories(): array  { return $this->categories; }
    public function getFieldErrors(): array { return $this->fieldErrors; }
    public function getAmount(): string     { return $this->amount; }
    public function getCategoryId(): string { return $this->categoryId; }
    public function getExpenseDate(): string{ return $this->expenseDate; }
    public function getDescription(): string{ return $this->description; }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
//
//  new ExpenseForm($uid, $pdo)
//    → Creates one object using the ExpenseForm blueprint.
//    → Runs __construct() automatically, which loads categories.
//
//  $form->handlePost($_POST)
//    → Calls the handlePost method on the $form object.
//    → The arrow (->) is how you call a method on an object.
// ════════════════════════════════════════════════════════════════════

$form = new ExpenseForm($uid, $pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->handlePost($_POST);
}

// Unpack values for the HTML template (same variable names as before)
$categories  = $form->getCategories();
$field_errors = $form->getFieldErrors();
$amount      = $form->getAmount();
$category_id = $form->getCategoryId();
$expense_date = $form->getExpenseDate();
$description = $form->getDescription();

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
