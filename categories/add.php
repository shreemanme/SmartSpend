<?php
/**
 * Page:      admin/add_category.php
 * Component: Admin Panel — Add Category
 * Developer: Ratnesh Kumar Yadav (Category Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$field_errors   = [];
$category_name  = '';
$description    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $description   = trim($_POST['description']   ?? '');

    if (empty($category_name)) {
        $field_errors['category_name'] = 'Category name is required.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tblCategory WHERE category_name = ? AND user_id = ?');
        $stmt->execute([$category_name, $_SESSION['user_id']]);
        if ((int)$stmt->fetchColumn() > 0) {
            $field_errors['category_name'] = 'A category with this name already exists.';
        }
    }

    if (empty($field_errors)) {
        $pdo->prepare(
            'INSERT INTO tblCategory (user_id, category_name, description, created_date, is_active)
             VALUES (?, ?, ?, CURDATE(), 1)'
        )->execute([$_SESSION['user_id'], $category_name, $description]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Category added successfully.'];
        header('Location: /smartspend/categories/index.php');
        exit;
    }
}

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
