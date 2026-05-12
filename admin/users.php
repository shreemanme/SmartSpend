<?php
// admin/users.php — Lists and filters users via the AdminUserFilter class.

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Reads name/role/status filters and fetches matching user rows.
class AdminUserFilter
{
    private string $search;
    private string $filterRole;
    private string $filterStatus;

    public function __construct(array $get = [])
    {
        $this->search       = trim($get['search'] ?? '');
        $this->filterRole   = trim($get['role']   ?? '');
        $this->filterStatus = trim($get['status'] ?? '');
    }

    // Returns users matching the current search, role, and status filters.
    public function getUsers(\PDO $pdo): array
    {
        $where  = [];
        $params = [];

        if ($this->search !== '') {
            $where[]  = '(full_name LIKE ? OR email LIKE ?)';
            $params[] = "%{$this->search}%";
            $params[] = "%{$this->search}%";
        }
        if ($this->filterRole !== '') {
            $where[]  = 'role = ?';
            $params[] = $this->filterRole;
        }
        if ($this->filterStatus !== '') {
            $where[]  = 'is_active = ?';
            $params[] = $this->filterStatus === 'active' ? 1 : 0;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $pdo->prepare("SELECT * FROM tblUser $whereClause ORDER BY created_date DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSearch(): string       { return $this->search; }
    public function getFilterRole(): string   { return $this->filterRole; }
    public function getFilterStatus(): string { return $this->filterStatus; }

    public function hasActiveFilter(): bool
    {
        return $this->search !== '' || $this->filterRole !== '' || $this->filterStatus !== '';
    }
}

$filter        = new AdminUserFilter($_GET);
$users         = $filter->getUsers($pdo);
$search        = $filter->getSearch();
$filter_role   = $filter->getFilterRole();
$filter_status = $filter->getFilterStatus();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>User Management</h1>
    <a href="/smartspend/admin/add_user.php" class="btn-primary" id="btn-add-user">+ Add User</a>
</div>

<!-- Search and Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end;">
    <div class="form-group" style="flex: 1;">
        <label for="search">Find User (Name/Email)</label>
        <input type="text" id="search" name="search" placeholder="Search..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="role">Filter by Role</label>
        <select id="role" name="role">
            <option value="">All Roles</option>
            <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="user" <?= $filter_role === 'user' ? 'selected' : '' ?>>User</option>
        </select>
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
        <a href="/smartspend/admin/users.php" class="btn-secondary" style="display:inline-block; padding:8px 12px; text-decoration:none;">Clear</a>
    </div>
    <?php endif; ?>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['user_id'] ?></td>
                <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['email'],     ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="badge <?= $u['role'] === 'admin' ? 'badge-warning' : 'badge-success' ?>">
                        <?= htmlspecialchars(ucfirst($u['role']), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td>
                    <?php if ((int)$u['is_active'] === 1): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactive</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['created_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <div class="actions-cell">
                        <a href="/smartspend/admin/edit_user.php?id=<?= $u['user_id'] ?>"
                           class="btn-warning btn-sm"
                           id="btn-edit-user-<?= $u['user_id'] ?>">Edit</a>

                        <?php if ((int)$u['is_active'] === 1): ?>
                        <form method="POST" action="/smartspend/admin/deactivate_user.php"
                              id="form-deactivate-<?= $u['user_id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                            <button type="submit" class="btn-danger btn-sm"
                                onclick="return confirm('Deactivate this user?')">Deactivate</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
