<?php
/**
 * Page:      categories/add.php
 * Component: Category Management — Add Category
 * Developer: Ratnesh Kumar Yadav (Category Management)
 *
 * OOP REWRITE:
 * The procedural add-category logic has been converted into the
 * CategoryAddForm class below. The HTML form template is unchanged.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// ════════════════════════════════════════════════════════════════════
//  CLASS: CategoryAddForm
//
//  What it is:
//    The blueprint for an object that validates and saves a new
//    category for the logged-in user.
//
//  How to use it:
//    $form = new CategoryAddForm($uid, $pdo);  // create the object
//    $form->handlePost($_POST);                 // process the form
//    $errors = $form->getFieldErrors();         // read errors
// ════════════════════════════════════════════════════════════════════
class CategoryAddForm
{
    // ── Properties ──────────────────────────────────────────────────

    private int    $userId;
    private \PDO   $pdo;
    private array  $fieldErrors;
    private string $categoryName;
    private string $description;

    // ── Constructor ──────────────────────────────────────────────────

    public function __construct(int $userId, \PDO $pdo)
    {
        $this->userId       = $userId;
        $this->pdo          = $pdo;
        $this->fieldErrors  = [];
        $this->categoryName = '';
        $this->description  = '';
    }

    // ── Private method: validate() ───────────────────────────────────
    // Checks the submitted name and verifies it is not a duplicate.

    private function validate(): void
    {
        if (empty($this->categoryName)) {
            $this->fieldErrors['category_name'] = 'Category name is required.';
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tblCategory WHERE category_name = ? AND user_id = ?'
        );
        $stmt->execute([$this->categoryName, $this->userId]);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->fieldErrors['category_name'] = 'A category with this name already exists.';
        }
    }

    // ── Private method: save() ───────────────────────────────────────
    // Inserts the new category row.

    private function save(): void
    {
        $this->pdo->prepare(
            'INSERT INTO tblCategory (user_id, category_name, description, created_date, is_active)
             VALUES (?, ?, ?, CURDATE(), 1)'
        )->execute([$this->userId, $this->categoryName, $this->description]);
    }

    // ── Public method: handlePost() ──────────────────────────────────
    // Reads form data, validates, saves if valid, and redirects.
    // Called as: $form->handlePost($_POST)

    public function handlePost(array $post): void
    {
        $this->categoryName = trim($post['category_name'] ?? '');
        $this->description  = trim($post['description']   ?? '');

        $this->validate();

        if (empty($this->fieldErrors)) {
            $this->save();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Category added successfully.'];
            header('Location: /smartspend/categories/index.php');
            exit;
        }
    }

    // ── Getter methods ───────────────────────────────────────────────

    public function getFieldErrors(): array  { return $this->fieldErrors; }
    public function getCategoryName(): string { return $this->categoryName; }
    public function getDescription(): string  { return $this->description; }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
// ════════════════════════════════════════════════════════════════════

$form = new CategoryAddForm($uid, $pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->handlePost($_POST);
}

// Unpack values for the HTML template (same variable names as before)
$field_errors  = $form->getFieldErrors();
$category_name = $form->getCategoryName();
$description   = $form->getDescription();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Add Category</h1>
    <a href="/smartspend/categories/index.php" class="btn-secondary">← Back to Categories</a>
</div>

<div class="form-card">
    <form method="POST" action="" novalidate>
        <div class="form-group">
            <label for="category_name">Category name</label>
            <input type="text" id="category_name" name="category_name"
                   value="<?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($field_errors['category_name'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="description">Description <span class="text-muted">(optional)</span></label>
            <input type="text" id="description" name="description"
                   value="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="200">
        </div>
        <div class="form-actions">
            <button type="submit" id="btn-create-category" class="btn-primary">Create Category</button>
            <a href="/smartspend/categories/index.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
