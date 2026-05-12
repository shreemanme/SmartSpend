<?php
// categories/add.php — Handles add-category form via the CategoryAddForm class.

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// Validates the submitted name (including duplicate check) and inserts the row.
class CategoryAddForm
{

    private int    $userId;
    private \PDO   $pdo;
    private array  $fieldErrors;
    private string $categoryName;
    private string $description;


    public function __construct(int $userId, \PDO $pdo)
    {
        $this->userId       = $userId;
        $this->pdo          = $pdo;
        $this->fieldErrors  = [];
        $this->categoryName = '';
        $this->description  = '';
    }

    // Ensures the name is not empty and not already used by this user.
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

    // Inserts the new category row into the database.
    private function save(): void
    {
        $this->pdo->prepare(
            'INSERT INTO tblCategory (user_id, category_name, description, created_date, is_active)
             VALUES (?, ?, ?, CURDATE(), 1)'
        )->execute([$this->userId, $this->categoryName, $this->description]);
    }

    // Reads POST data, validates, saves if valid, and redirects on success.

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

    // Getters — allow the HTML template to read private properties.
    public function getFieldErrors(): array  { return $this->fieldErrors; }
    public function getCategoryName(): string { return $this->categoryName; }
    public function getDescription(): string  { return $this->description; }
}


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
