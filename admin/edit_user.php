<?php
/**
 * Page:      admin/edit_user.php
 * Component: Admin Panel — Edit User
 * Developer: Nandan Kumar Yadav
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['edit_user_id'] ?? 0);
if ($id === 0) {
    header('Location: /smartspend/admin/users.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM tblUser WHERE user_id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'User not found.'];
    header('Location: /smartspend/admin/users.php');
    exit;
}

$field_errors = [];
$full_name    = $user['full_name'];
$email        = $user['email'];
$role         = $user['role'];
$is_active    = (int)$user['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $role      = in_array($_POST['role'] ?? '', ['user', 'admin']) ? $_POST['role'] : 'user';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($full_name)) {
        $field_errors['full_name'] = 'Full name is required.';
    }
    if (empty($email)) {
        $field_errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tblUser WHERE email = ? AND user_id != ?');
        $stmt->execute([$email, $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $field_errors['email'] = 'Email already in use by another account.';
        }
    }

    if (empty($field_errors)) {
        $pdo->prepare(
            'UPDATE tblUser SET full_name = ?, email = ?, role = ?, is_active = ? WHERE user_id = ?'
        )->execute([$full_name, $email, $role, $is_active, $id]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'User updated.'];
        header('Location: /smartspend/admin/users.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Edit User</h1>
    <a href="/smartspend/admin/users.php" class="btn-secondary">← Back</a>
</div>

<div class="form-card">
    <form method="POST" action="" novalidate>
        <input type="hidden" name="edit_user_id" value="<?= $id ?>">

        <div class="form-group">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($field_errors['full_name'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($field_errors['email'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user"  <?= $role === 'user'  ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= $is_active ? 'checked' : '' ?>>
                Account is active
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" id="btn-save-user" class="btn-primary">Save Changes</button>
            <a href="/smartspend/admin/users.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
