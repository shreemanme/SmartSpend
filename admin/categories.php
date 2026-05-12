<?php
// admin/categories.php — Lists all categories via the AdminCategoryList class.

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Fetches all categories for the admin category management table.
class AdminCategoryList
{
    // Returns all categories ordered by name.
    public function getCategories(\PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM tblCategory ORDER BY category_name')->fetchAll();
    }
}

$list       = new AdminCategoryList();
$categories = $list->getCategories($pdo);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Category Management</h1>
    <a href="/smartspend/admin/add_category.php" class="btn-primary" id="btn-add-category">+ Add Category</a>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= $cat['category_id'] ?></td>
                <td><?= htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($cat['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ((int)$cat['is_active'] === 1): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactive</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($cat['created_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <div class="actions-cell">
                        <a href="/smartspend/admin/edit_category.php?id=<?= $cat['category_id'] ?>"
                           class="btn-warning btn-sm"
                           id="btn-edit-cat-<?= $cat['category_id'] ?>">Edit</a>

                        <form method="POST" action="/smartspend/admin/toggle_category.php"
                              id="form-toggle-cat-<?= $cat['category_id'] ?>">
                            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                            <button type="submit" class="btn-sm <?= (int)$cat['is_active'] === 1 ? 'btn-secondary' : 'btn-primary' ?>">
                                <?= (int)$cat['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
