<?php
/**
 * Page:      includes/header.php
 * Component: Shared Header — Nav Bar, Flash Messages
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
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
    <meta name="theme-color" content="#00A844">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' — SmartSpend' : 'SmartSpend' ?></title>
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
                    <li><a href="/smartspend/dashboard/index.php" <?= nav_active('dashboard') ?>>Dashboard</a></li>
                    <li><a href="/smartspend/expenses/index.php" <?= nav_active('expenses') ?>>Expenses</a></li>
                    <li><a href="/smartspend/reports/index.php" <?= nav_active('reports') ?>>Reports</a></li>
                    <li><a href="/smartspend/categories/index.php" <?= nav_active('categories') ?>>Categories</a></li>
                    <li><a href="/smartspend/history/index.php" <?= nav_active('history') ?>>History</a></li>
                    <li><a href="/smartspend/account/index.php" <?= nav_active('account') ?>>Account</a></li>
                <?php else: ?>
                    <li><a href="/smartspend/auth/login.php" <?= nav_active('login') ?>>Login</a></li>
                    <li><a href="/smartspend/auth/register.php" <?= nav_active('register') ?>>Register</a></li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="navbar-right">
                    <span class="navbar-greeting">Hi,
                        <strong><?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0], ENT_QUOTES, 'UTF-8') ?></strong></span>

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