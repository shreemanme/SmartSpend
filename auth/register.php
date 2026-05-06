<?php
/**
 * Page:      auth/register.php
 * Component: Authentication — Registration
 * Developer: Nandan Kumar Yadav
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /smartspend/dashboard/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$errors           = [];
$field_errors     = [];
$full_name        = '';
$email            = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim($_POST['email']            ?? '');
    $password         = $_POST['password']              ?? '';
    $confirm_password = $_POST['confirm_password']      ?? '';

    // Validate full_name
    if (empty($full_name)) {
        $field_errors['full_name'] = 'Full name is required.';
    }

    // Validate email
    if (empty($email)) {
        $field_errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = 'Please enter a valid email address.';
    } else {
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

    // Validate confirm password
    if (empty($confirm_password)) {
        $field_errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $field_errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($field_errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            'INSERT INTO tblUser (full_name, email, password_hash, created_date, is_active, role)
             VALUES (?, ?, ?, CURDATE(), 1, \'user\')'
        );
        $stmt->execute([$full_name, $email, $hash]);

        // Remember this so the login page can show a proper welcome instead of "welcome back"
        $_SESSION['just_registered'] = true;

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'Account created successfully! You can now log in with your new credentials.',
        ];
        header('Location: /smartspend/auth/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create a free SmartSpend account to start tracking your expenses.">
    <title>Register — SmartSpend</title>
    <link rel="stylesheet" href="/smartspend/assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/smartspend/assets/img/SmartSpend.svg" alt="SmartSpend logo">
            <span>SmartSpend</span>
        </div>

        <h2>Create your account</h2>

        <form method="POST" action="" novalidate>
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="John Smith"
                    required
                    autocomplete="name">
                <?php if (isset($field_errors['full_name'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="you@example.com"
                    required
                    autocomplete="email">
                <?php if (isset($field_errors['email'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 8 characters"
                    required
                    autocomplete="new-password">
                <?php if (isset($field_errors['password'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Repeat your password"
                    required
                    autocomplete="new-password">
                <?php if (isset($field_errors['confirm_password'])): ?>
                    <span class="form-error"><?= htmlspecialchars($field_errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" id="btn-register" class="btn-primary" style="width:100%;">Create account</button>
            </div>
        </form>

        <p class="auth-link">
            Already have an account? <a href="/smartspend/auth/login.php">Log in</a>
        </p>
    </div>
</div>

<script src="/smartspend/assets/js/main.js"></script>
</body>
</html>
