<?php
/**
 * Page:      admin/view.php
 * Component: Admin Dashboard — View User Details
 * Purpose:   Detailed profile view for an individual user.
 */

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM tblUser WHERE user_id = ?');
$stmt->execute([$user_id]);
$u = $stmt->fetch();

if (!$u) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'User not found.'
    ];
    header('Location: /smartspend/admin/index.php');
    exit;
}

$pageTitle = 'User Details';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>User Details (ID: <?= htmlspecialchars($u['user_id'], ENT_QUOTES, 'UTF-8') ?>)</h1>
    <div>
        <a href="/smartspend/admin/edit.php?id=<?= $u['user_id'] ?>" class="btn-primary">Edit User</a>
        <a href="/smartspend/admin/index.php" class="btn-secondary">Back to List</a>
    </div>
</div>

<div class="detail-card">
    <div class="detail-row">
        <span class="detail-label">User ID</span>
        <span class="detail-value"><?= htmlspecialchars($u['user_id'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Full Name</span>
        <span class="detail-value"><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Email Address</span>
        <span class="detail-value"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Role</span>
        <span class="detail-value">
            <span class="badge <?= $u['role'] === 'admin' ? 'badge-warning' : 'badge-success' ?>">
                <?= htmlspecialchars(ucfirst($u['role']), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Account Status</span>
        <span class="detail-value">
            <span class="badge <?= (int)$u['is_active'] === 1 ? 'badge-success' : 'badge-inactive' ?>">
                <?= (int)$u['is_active'] === 1 ? 'Active' : 'Inactive' ?>
            </span>
        </span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Registration Date</span>
        <span class="detail-value">
            <?php 
            $date = date_create($u['created_date']);
            echo $date ? date_format($date, 'F d, Y') : htmlspecialchars($u['created_date'], ENT_QUOTES, 'UTF-8');
            ?>
        </span>
    </div>

</div>

<?php if ($u['user_id'] !== (int)$_SESSION['user_id']): ?>
    <div class="danger-zone" style="margin-top: 32px;">
        <h3>Delete User Account</h3>
        <p>Permanently delete this user and all associated financial records (expenses, categories, reports). This action cannot be undone.</p>
        <form method="POST" action="/smartspend/admin/delete.php" onsubmit="return confirm('Are you sure you want to permanently delete this user and all their expenses? This cannot be undone.')">
            <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
            <button type="submit" class="btn-danger">Delete User Account</button>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
