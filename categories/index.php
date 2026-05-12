<?php
// categories/index.php — Lists and filters categories via the CategoryFilter class.

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];

// Reads search/status filters from the URL and returns matching category rows.
class CategoryFilter
{

    private int    $userId;
    private string $search;
    private string $status;   // 'active', 'inactive', or ''

    // Reads and sanitises filter values from the URL.

    public function __construct(int $userId, array $get = [])
    {
        $this->userId = $userId;
        $this->search = trim($get['search'] ?? '');
        $this->status = trim($get['status'] ?? '');
    }

    // Builds the WHERE clause from filters and returns matching category rows.

    public function getCategories(\PDO $pdo): array
    {
        $where  = ['user_id = ?'];
        $params = [$this->userId];

        if ($this->search !== '') {
            $where[]  = 'category_name LIKE ? OR description LIKE ?';
            $params[] = "%{$this->search}%";
            $params[] = "%{$this->search}%";
        }
        if ($this->status !== '') {
            $where[]  = 'is_active = ?';
            $params[] = $this->status === 'active' ? 1 : 0;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        $stmt = $pdo->prepare("SELECT * FROM tblCategory $whereClause ORDER BY category_name");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Getters — allow the template to read private filter values.
    public function getSearch(): string { return $this->search; }
    public function getStatus(): string { return $this->status; }

    // Returns true if any filter is set — used to show the Clear button
    public function hasActiveFilter(): bool
    {
        return $this->search !== '' || $this->status !== '';
    }
}


$filter     = new CategoryFilter($uid, $_GET);
$categories = $filter->getCategories($pdo);

// Unpack values for the HTML template (same variable names as before)
$search        = $filter->getSearch();
$filter_status = $filter->getStatus();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Category Management</h1>
    <a href="/smartspend/categories/add.php" class="btn-primary" id="btn-add-category">+ Add Category</a>
</div>

<!-- Search and Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end;">
    <div class="form-group" style="flex: 1;">
        <label for="search">Find Category</label>
        <input type="text" id="search" name="search" placeholder="Search name/desc..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="status">Filter by Status</label>
        <select id="status" name="status">
            <option value="">All Statuses</option>
            <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <div class="form-group" style="flex: 0;">
        <button type="submit" class="btn-primary">Apply</button>
    </div>
    <?php if ($filter->hasActiveFilter()): ?>
    <div class="form-group" style="flex: 0;">
        <a href="/smartspend/categories/index.php" class="btn-secondary" style="display:inline-block; padding:8px 12px; text-decoration:none;">Clear</a>
    </div>
    <?php endif; ?>
</form>

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
                        <a href="/smartspend/categories/edit.php?id=<?= $cat['category_id'] ?>"
                           class="btn-warning btn-sm"
                           id="btn-edit-cat-<?= $cat['category_id'] ?>">Edit</a>

                        <form method="POST" action="/smartspend/categories/delete.php"
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
