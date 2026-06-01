<?php
/**
 * Page:      admin/create.php
 * Component: Admin Dashboard — Create User
 * Purpose:   Form and handler for creating a new user.
 */

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];
$field_errors = [];

$full_name = '';
$email = '';
$role = 'user';
$is_active = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? 'user');
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    // Validate full name
    if (empty($full_name)) {
        $field_errors['full_name'] = 'Full name is required.';
    }

    // Validate email
    if (empty($email)) {
        $field_errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    } else {
        // Check uniqueness
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tblUser WHERE email = ?');
        $stmt->execute([$email]);
        if ((int)$stmt->fetchColumn() > 0) {
            $field_errors['email'] = 'An account with this email already exists.';
        }
    }

    // Validate password
    if (empty($password)) {
        $field_errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $field_errors['password'] = 'Password must be at least 8 characters.';
    }

    // Validate role
    if (!in_array($role, ['user', 'admin'])) {
        $field_errors['role'] = 'Invalid role selected.';
    }

    // Validate status
    if (!in_array($is_active, [0, 1])) {
        $field_errors['is_active'] = 'Invalid status selected.';
    }

    if (empty($field_errors)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO tblUser (full_name, email, password_hash, created_date, is_active, role)
                 VALUES (?, ?, ?, CURDATE(), ?, ?)'
            );
            $stmt->execute([$full_name, $email, $hash, $is_active, $role]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'User created successfully.'
            ];
            header('Location: /smartspend/admin/index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'An error occurred while creating the user: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Add New User';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Add New User</h1>
    <a href="/smartspend/admin/index.php" class="btn-secondary">Back to List</a>
</div>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error" role="alert"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. John Doe" required>
                <?php if (isset($field_errors['full_name'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. john@example.com" required>
                <?php if (isset($field_errors['email'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
                <?php if (isset($field_errors['password'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>Standard User</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
                <?php if (isset($field_errors['role'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['role'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="is_active">Account Status</label>
                <select id="is_active" name="is_active" required>
                    <option value="1" <?= (int)$is_active === 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (int)$is_active === 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if (isset($field_errors['is_active'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['is_active'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Create User</button>
            <a href="/smartspend/admin/index.php" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
