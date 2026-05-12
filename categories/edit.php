<?php
// categories/edit.php — Handles edit-category form via the CategoryEditForm class.

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];
$id  = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['category_id'] ?? 0);

if ($id === 0) {
    header('Location: /smartspend/categories/index.php');
    exit;
}

// Loads the existing category, validates changes, and saves to the database.
class CategoryEditForm
{

    private int    $userId;
    private int    $categoryId;
    private \PDO   $pdo;
    private array  $fieldErrors;
    private string $categoryName;
    private string $description;

    // Fetches the category from DB; redirects if not found or not owned by this user.

    public function __construct(int $userId, int $categoryId, \PDO $pdo)
    {
        $this->userId     = $userId;
        $this->categoryId = $categoryId;
        $this->pdo        = $pdo;
        $this->fieldErrors = [];

        $stmt = $pdo->prepare(
            'SELECT * FROM tblCategory WHERE category_id = ? AND user_id = ?'
        );
        $stmt->execute([$categoryId, $userId]);
        $cat = $stmt->fetch();

        if (!$cat) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Category not found or access denied.'];
            header('Location: /smartspend/categories/index.php');
            exit;
        }

        $this->categoryName = $cat['category_name'];
        $this->description  = $cat['description'] ?? '';
    }

    // Ensures the name is not empty and not used by another category.

    private function validate(): void
    {
        if (empty($this->categoryName)) {
            $this->fieldErrors['category_name'] = 'Category name is required.';
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tblCategory
             WHERE category_name = ? AND user_id = ? AND category_id != ?'
        );
        $stmt->execute([$this->categoryName, $this->userId, $this->categoryId]);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->fieldErrors['category_name'] = 'Another category already has this name.';
        }
    }

    // Persists the updated name and description to the database.

    private function update(): void
    {
        $this->pdo->prepare(
            'UPDATE tblCategory
             SET category_name = ?, description = ?
             WHERE category_id = ? AND user_id = ?'
        )->execute([$this->categoryName, $this->description, $this->categoryId, $this->userId]);
    }

    // Reads POST data, validates, and updates the record if valid.

    public function handlePost(array $post): void
    {
        $this->categoryName = trim($post['category_name'] ?? '');
        $this->description  = trim($post['description']   ?? '');

        $this->validate();

        if (empty($this->fieldErrors)) {
            $this->update();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Category updated.'];
            header('Location: /smartspend/categories/index.php');
            exit;
        }
    }

    // Getters — allow the HTML template to read private properties.
    public function getFieldErrors(): array  { return $this->fieldErrors; }
    public function getCategoryName(): string { return $this->categoryName; }
    public function getDescription(): string  { return $this->description; }
}


$form = new CategoryEditForm($uid, $id, $pdo);

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
    <h1>Edit Category</h1>
    <a href="/smartspend/categories/index.php" class="btn-secondary">← Back</a>
</div>

<div class="form-card">
    <form method="POST" action="" novalidate>
        <input type="hidden" name="category_id" value="<?= $id ?>">

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
            <button type="submit" id="btn-save-category" class="btn-primary">Save Changes</button>
            <a href="/smartspend/categories/index.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
