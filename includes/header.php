<?php
/**
 * Page:      includes/header.php
 * Component: Shared Header — Nav Bar, Flash Messages
 * Developer: Shreeman Bhandari
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine active page for nav link highlighting
$current = $_SERVER['PHP_SELF'] ?? '';
function nav_active(string $path): string
{
    global $current;
    return str_contains($current, $path) ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="SmartSpend — Personal expense tracker. Log, manage and analyse your spending with ease.">
    <title>SmartSpend</title>
    <link rel="stylesheet" href="/smartspend/assets/css/style.css">
</head>

<body>

    <!-- ── Navigation Bar ─────────────────────────────────────────────────── -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="navbar-inner">

            <a href="/smartspend/" class="navbar-brand">
                <img src="/smartspend/assets/img/SmartSpend.svg" alt="SmartSpend logo">
                <span>SmartSpend</span>
            </a>

            <button id="nav-hamburger" class="nav-hamburger" aria-label="Toggle navigation" aria-expanded="false"
                aria-controls="nav-links">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="navbar-links" id="nav-links" role="list">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <li><a href="/smartspend/admin/dashboard.php" <?= nav_active('admin/dashboard') ?>>Admin Dashboard</a></li>
                        <li><a href="/smartspend/admin/users.php" <?= nav_active('admin/users') ?>>Users</a></li>
                        <li><a href="/smartspend/admin/categories.php" <?= nav_active('admin/categories') ?>>Categories</a></li>
                        <li><a href="/smartspend/admin/expenses.php" <?= nav_active('admin/expenses') ?>>Expenses</a></li>
                        <li><a href="/smartspend/admin/audit.php" <?= nav_active('admin/audit') ?>>Audit Log</a></li>
                    <?php else: ?>
                        <li><a href="/smartspend/dashboard/index.php" <?= nav_active('dashboard') ?>>Dashboard</a></li>
                        <li><a href="/smartspend/expenses/index.php" <?= nav_active('expenses') ?>>Expenses</a></li>
                        <li><a href="/smartspend/reports/index.php" <?= nav_active('reports') ?>>Reports</a></li>
                        <li><a href="/smartspend/account/index.php" <?= nav_active('account') ?>>Account</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="/smartspend/auth/login.php" <?= nav_active('login') ?>>Login</a></li>
                    <li><a href="/smartspend/auth/register.php" <?= nav_active('register') ?>>Register</a></li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="navbar-right">
                    <span class="navbar-greeting">Hi,
                        <strong><?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0], ENT_QUOTES, 'UTF-8') ?></strong></span>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="/smartspend/admin/dashboard.php" class="btn-admin-pill">Admin Panel</a>
                    <?php endif; ?>
                    <span class="nav-divider"></span>
                    <a href="/smartspend/auth/logout.php" class="nav-logout">Logout</a>
                </div>
            <?php endif; ?>

        </div>
    </nav>

    <!-- ── Flash Messages ─────────────────────────────────────────────────── -->
    <?php if (isset($_SESSION['flash'])): ?>
        <?php
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
        $msg = htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8');
        ?>
        <div class="flash-wrapper">
            <div class="flash flash-<?= $type ?>" role="alert"><?= $msg ?></div>
        </div>
    <?php endif; ?>

    <main class="container">