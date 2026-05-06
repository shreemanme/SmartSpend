<?php
/**
 * Page:      admin/users.php
 * Component: Admin Panel — User Management
 * Developer: Nandan Kumar Yadav (User & Account Management)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query('SELECT * FROM tblUser ORDER BY created_date DESC');
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>User Management</h1>
    <a href="/smartspend/admin/add_user.php" class="btn-primary" id="btn-add-user">+ Add User</a>
</div>

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
