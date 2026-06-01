<?php
/**
 * Page:      admin/index.php
 * Component: Admin Dashboard — User Directory
 * Purpose:   Full CRUD listing, searching, sorting, filtering, and pagination of users.
 */

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Read filter parameters
$search = trim($_GET['search'] ?? '');
$role = trim($_GET['role'] ?? '');
$status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
$sort_by = trim($_GET['sort_by'] ?? 'date');
$sort_order = trim($_GET['sort_order'] ?? 'desc');

// Build query conditions
$where = ['1=1'];
$params = [];

if ($search !== '') {
    $where[] = '(full_name LIKE ? OR email LIKE ? OR user_id = ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = (int)$search;
}

if ($role !== '') {
    $where[] = 'role = ?';
    $params[] = $role;
}

if ($status !== '') {
    $where[] = 'is_active = ?';
    $params[] = (int)$status;
}

$where_sql = implode(' AND ', $where);

// Count total matching records for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM tblUser WHERE $where_sql");
$count_stmt->execute($params);
$total_records = (int)$count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);
if ($total_pages < 1) $total_pages = 1;

if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Sorting column mapping
$sort_column = 'created_date';
if ($sort_by === 'name') {
    $sort_column = 'full_name';
}

$order_direction = strtolower($sort_order) === 'asc' ? 'ASC' : 'DESC';

// Fetch users
$query_sql = "SELECT user_id, full_name, email, role, is_active, created_date 
              FROM tblUser 
              WHERE $where_sql 
              ORDER BY $sort_column $order_direction 
              LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query_sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Build query string for pagination links
$query_params = $_GET;
unset($query_params['page']);
$base_query_string = http_build_query($query_params);
if ($base_query_string !== '') {
    $base_query_string .= '&';
}

$pageTitle = 'User Management';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.actions-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
}
.action-btn-link {
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.2s, opacity 0.2s;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
}
.action-btn-link:hover {
    opacity: 0.75;
    text-decoration: underline;
}
.text-view {
    color: #40c3f9; /* Theme sky blue */
}
.text-edit {
    color: #ffb838; /* Theme amber */
}
.text-delete {
    color: #ff5154; /* Theme danger red */
}
.action-divider {
    width: 1px;
    height: 12px;
    background-color: var(--border);
    display: inline-block;
}
</style>

<div class="page-header">
    <h1>User Management</h1>
    <a href="/smartspend/admin/create.php" class="btn-primary">+ Add New User</a>
</div>

<!-- Search, Filter & Sort Form -->
<form method="GET" action="" class="filter-bar" novalidate>
    <div class="form-group">
        <label for="search">Search</label>
        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Name, email, or ID">
    </div>
    
    <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role">
            <option value="">All Roles</option>
            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>Standard User</option>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administrator</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">All Statuses</option>
            <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="sort_by">Sort By</label>
        <select id="sort_by" name="sort_by">
            <option value="date" <?= $sort_by === 'date' ? 'selected' : '' ?>>Registration Date</option>
            <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Full Name</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="sort_order">Order</label>
        <select id="sort_order" name="sort_order">
            <option value="desc" <?= $sort_order === 'desc' ? 'selected' : '' ?>>Descending</option>
            <option value="asc" <?= $sort_order === 'asc' ? 'selected' : '' ?>>Ascending</option>
        </select>
    </div>
    
    <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn-primary">Filter</button>
        <a href="/smartspend/admin/index.php" class="btn-secondary" style="display:inline-block;">Reset</a>
    </div>
</form>

<!-- Users Table -->
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Role</th>
                <th>Account Status</th>
                <th>Registration Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px 0;">No users found matching your search criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td style="font-weight: 700; color: var(--text-dark);"><?= htmlspecialchars($u['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'badge-warning' : 'badge-success' ?>">
                                <?= htmlspecialchars(ucfirst($u['role']), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= (int)$u['is_active'] === 1 ? 'badge-success' : 'badge-inactive' ?>">
                                <?= (int)$u['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $date = date_create($u['created_date']);
                            echo $date ? date_format($date, 'M d, Y') : htmlspecialchars($u['created_date'], ENT_QUOTES, 'UTF-8');
                            ?>
                        </td>
                        <td class="actions-cell">
                            <a href="/smartspend/admin/view.php?id=<?= $u['user_id'] ?>" class="action-btn-link text-view">View</a>
                            <span class="action-divider"></span>
                            <a href="/smartspend/admin/edit.php?id=<?= $u['user_id'] ?>" class="action-btn-link text-edit">Edit</a>
                            <?php if ($u['user_id'] !== (int)$_SESSION['user_id']): ?>
                                <span class="action-divider"></span>
                                <form method="POST" action="/smartspend/admin/delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this user and all their expenses? This cannot be undone.')">
                                    <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                    <button type="submit" class="action-btn-link text-delete">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Grid -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= $base_query_string ?>page=1">&laquo; First</a>
            <a href="?<?= $base_query_string ?>page=<?= $page - 1 ?>">&lsaquo; Prev</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i === $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?<?= $base_query_string ?>page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?<?= $base_query_string ?>page=<?= $page + 1 ?>">Next &rsaquo;</a>
            <a href="?<?= $base_query_string ?>page=<?= $total_pages ?>">Last &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
