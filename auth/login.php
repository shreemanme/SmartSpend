<?php
/**
 * Page:      auth/login.php
 * Component: Authentication — Login
 * Developer: Nandan Kumar Yadav (User & Account Management)
 */

session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /smartspend/dashboard/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email']      ?? '');
    $password   = trim($_POST['password']   ?? '');
    $login_role = trim($_POST['login_role'] ?? 'user');

    if (empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM tblUser WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors[] = 'Invalid email or password.';
        } elseif ((int)$user['is_active'] === 0) {
            $errors[] = 'This account has been deactivated.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } elseif ($login_role === 'admin' && $user['role'] !== 'admin') {
            $errors[] = 'Invalid email or password.';
        } else {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            
            $first_name = explode(' ', trim($user['full_name']))[0];
            if (isset($_SESSION['just_registered'])) {
                $_SESSION['flash'] = ['type' => 'success', 'msg' => "Welcome, {$first_name}!"];
                unset($_SESSION['just_registered']);
            } else {
                $_SESSION['flash'] = ['type' => 'success', 'msg' => "Welcome back, {$first_name}!"];
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /smartspend/admin/index.php');
            } else {
                header('Location: /smartspend/dashboard/index.php');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Log in to SmartSpend — your personal expense tracker.">
    <title>Login — SmartSpend</title>
    <link rel="stylesheet" href="/smartspend/assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/smartspend/assets/img/SmartSpend.svg" alt="SmartSpend logo">
            <span>SmartSpend</span>
        </div>

        <h2 id="form-title">Login to SmartSpend</h2>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
                $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
                $msg  = htmlspecialchars($flash['msg'],  ENT_QUOTES, 'UTF-8');
            ?>
            <div class="flash flash-<?= $type ?>" role="alert"><?= $msg ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <div class="flash flash-error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <input type="hidden" name="login_role" id="login-role" value="user">
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
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password">
            </div>
            <div class="form-actions">
                <button type="submit" id="btn-login" class="btn-primary" style="width:100%;">Log in</button>
            </div>
        </form>

        <button type="button" id="btn-toggle-admin" class="btn-secondary" style="width:100%; margin-top: 12px; margin-bottom: 8px;">Login as Admin</button>

        <p class="auth-link">
            Don't have an account? <a href="/smartspend/auth/register.php">Register</a>
        </p>
    </div>
</div>

<script src="/smartspend/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnToggleAdmin = document.getElementById('btn-toggle-admin');
    const loginRole = document.getElementById('login-role');
    const formTitle = document.getElementById('form-title');
    const registerLink = document.querySelector('.auth-link');
    
    if (btnToggleAdmin) {
        btnToggleAdmin.addEventListener('click', function() {
            if (loginRole.value === 'user') {
                loginRole.value = 'admin';
                formTitle.textContent = 'Admin Login';
                btnToggleAdmin.textContent = 'Login as Standard User';
                if (registerLink) registerLink.style.display = 'none';
            } else {
                loginRole.value = 'user';
                formTitle.textContent = 'Login to SmartSpend';
                btnToggleAdmin.textContent = 'Login as Admin';
                if (registerLink) registerLink.style.display = 'block';
            }
        });
    }
});
</script>
</body>
</html>
