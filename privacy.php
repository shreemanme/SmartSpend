<?php
/**
 * Page:      privacy.php
 * Component: Privacy Policy
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Privacy Policy for SmartSpend.">
    <title>Privacy Policy — SmartSpend</title>
    <link rel="stylesheet" href="/smartspend/assets/css/style.css">
</head>
<body>

<header role="banner">
    <div class="header-container">
        <div class="logo">
            <a href="/smartspend/index.php">
                <img src="/smartspend/assets/img/SmartSpend.svg" alt="SmartSpend logo">
                <span>SmartSpend</span>
            </a>
        </div>
        <nav role="navigation" aria-label="Main navigation">
            <ul class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/smartspend/dashboard/index.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="/smartspend/auth/login.php">Log in</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="container" role="main" style="max-width:800px; padding: 40px 20px;">
    <h1>Privacy Policy</h1>
    <p>Last updated: <?php echo date('F j, Y'); ?></p>

    <section style="margin-top: 30px;">
        <h2>1. Data Collection</h2>
        <p>We collect your name, email address, and the financial expenses you choose to log in the application. This data is essential to provide you with the core functionality of SmartSpend.</p>

        <h2 style="margin-top: 20px;">2. Data Usage</h2>
        <p>Your data is used strictly to provide the expense tracking and reporting features of the application. We do not sell your personal data to third parties. Administrators have access only to anonymized, aggregated platform statistics and do not view your individual expenses.</p>

        <h2 style="margin-top: 20px;">3. Data Security</h2>
        <p>Your password is securely hashed before storage. We employ standard technical measures to safeguard your personal data.</p>

        <h2 style="margin-top: 20px;">4. Your Rights (GDPR)</h2>
        <p>As a user, you have the right to access, rectify, or erase your personal data. You can download all your data in JSON format or permanently delete your account entirely from the Account Settings page.</p>

        <h2 style="margin-top: 20px;">5. Cookies</h2>
        <p>SmartSpend only uses strictly necessary session cookies to maintain your logged-in state. We do not use tracking or advertising cookies.</p>
    </section>
</main>

<footer role="contentinfo">
    SmartSpend &copy; <?= date('Y') ?> | CTEC2713 Agile Development Team Project | <a href="/smartspend/privacy.php" style="color:inherit; text-decoration:underline;">Privacy Policy</a>
</footer>

</body>
</html>
