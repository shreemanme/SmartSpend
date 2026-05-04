<?php
/**
 * Page:      admin/add_user.php
 * Component: Admin Panel — Add User
 * Developer: Shreeman Bhandari
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$field_errors = [];
$full_name    = '';
$email        = '';
$role         = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $role      = in_array($_POST['role'] ?? '', ['user', 'admin']) ? $_POST['role'] : 'user';

    if (empty($full_name)) {
        $field_errors['full_name'] = 'Full name is required.';
    }
    if (empty($email)) {
        $field_errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tblUser WHERE email = ?');
        $stmt->execute([$email]);
        if ((int)$stmt->fetchColumn() > 0) {
            $field_errors['email'] = 'Email already in use.';
        }
    }
    if (empty($password)) {
        $field_errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $field_errors['password'] = 'Password must be at least 8 characters.';
    }

    if (empty($field_errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare(
            'INSERT INTO tblUser (full_name, email, password_hash, created_date, is_active, role)
             VALUES (?, ?, ?, CURDATE(), 1, ?)'
        )->execute([$full_name, $email, $hash, $role]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'User created successfully.'];
        header('Location: /smartspend/admin/users.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Add User</h1>
    <a href="/smartspend/admin/users.php" class="btn-secondary">← Back</a>
</div>

<div class="form-card">
    <form method="POST" action="" novalidate>
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
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Minimum 8 characters" required>
            <?php if (isset($field_errors['password'])): ?>
                <span class="form-error"><?= htmlspecialchars($field_errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user"  <?= $role === 'user'  ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" id="btn-create-user" class="btn-primary">Create User</button>
            <a href="/smartspend/admin/users.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
