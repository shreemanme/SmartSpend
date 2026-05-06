<?php
/**
 * Page:      account/index.php
 * Component: Account Management — View & Edit
 * Developer: Nandan Kumar Yadav
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid  = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT full_name, email, created_date, role FROM tblUser WHERE user_id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: /smartspend/auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>My Account</h1>
</div>

<!-- User details -->
<div class="detail-card">
    <div class="detail-row">
        <span class="detail-label">Full name</span>
        <span class="detail-value"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Email</span>
        <span class="detail-value"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Member since</span>
        <span class="detail-value"><?= htmlspecialchars($user['created_date'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Role</span>
        <span class="detail-value">
            <span class="badge <?= $user['role'] === 'admin' ? 'badge-warning' : 'badge-success' ?>">
                <?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </span>
    </div>
</div>

<!-- Update details -->
<div class="form-card">
    <h2>Update Details</h2>
    <form method="POST" action="/smartspend/account/update.php" novalidate>
        <div class="form-group">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-actions">
            <button type="submit" id="btn-update-details" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<!-- Change password -->
<div class="form-card">
    <h2>Change Password</h2>
    <form method="POST" action="/smartspend/account/password.php" novalidate>
        <div class="form-group">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password"
                   autocomplete="current-password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password"
                   placeholder="Minimum 8 characters"
                   autocomplete="new-password" required>
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirm new password</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password"
                   autocomplete="new-password" required>
        </div>
        <div class="form-actions">
            <button type="submit" id="btn-change-password" class="btn-primary">Update Password</button>
        </div>
    </form>
</div>

<!-- Close Account -->
<div class="danger-zone">
    <h3>Close Account</h3>
    <p>Closing your account will deactivate it immediately. Your data will be preserved but you will no longer be able to log in.</p>
    <form method="POST" action="/smartspend/account/close.php" id="form-close-account">
        <button type="submit" id="btn-close-account" class="btn-danger">Close My Account</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
