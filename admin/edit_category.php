<?php
/**
 * Page:      admin/edit_category.php
 * Component: Admin Panel — Edit Category
 * Developer: Shreeman Bhandari
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['category_id'] ?? 0);
if ($id === 0) {
    header('Location: /smartspend/admin/categories.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM tblCategory WHERE category_id = ?');
$stmt->execute([$id]);
$cat = $stmt->fetch();

if (!$cat) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Category not found.'];
    header('Location: /smartspend/admin/categories.php');
    exit;
}

$field_errors  = [];
$category_name = $cat['category_name'];
$description   = $cat['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $description   = trim($_POST['description']   ?? '');

    if (empty($category_name)) {
        $field_errors['category_name'] = 'Category name is required.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tblCategory WHERE category_name = ? AND category_id != ?');
        $stmt->execute([$category_name, $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $field_errors['category_name'] = 'Another category already has this name.';
        }
    }

    if (empty($field_errors)) {
        $pdo->prepare(
            'UPDATE tblCategory SET category_name = ?, description = ? WHERE category_id = ?'
        )->execute([$category_name, $description, $id]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Category updated.'];
        header('Location: /smartspend/admin/categories.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Category</h1>
    <a href="/smartspend/admin/categories.php" class="btn-secondary">← Back</a>
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
            <a href="/smartspend/admin/categories.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
