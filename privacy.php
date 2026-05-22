<?php
/**
 * Page:      privacy.php
 * Component: Privacy Policy
 */

$pageTitle = 'Privacy Policy';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="page-header">
        <h1>Privacy Policy</h1>
    </div>

    <div class="form-card" style="max-width: 100%;">
        <p class="text-muted" style="margin-bottom: 24px;">Last updated: <?php echo date('F j, Y'); ?></p>

    <section>
        <h2 style="font-size: 18px; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">1. Data Collection</h2>
        <p style="margin-bottom: 20px;">We collect your name, email address, and the financial expenses you choose to log in the application. This data is essential to provide you with the core functionality of SmartSpend.</p>

        <h2 style="font-size: 18px; margin-top: 32px; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">2. Data Usage</h2>
        <p style="margin-bottom: 20px;">Your data is used strictly to provide the expense tracking and reporting features of the application. We do not sell your personal data to third parties. Administrators have access only to anonymized, aggregated platform statistics and do not view your individual expenses.</p>

        <h2 style="font-size: 18px; margin-top: 32px; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">3. Data Security</h2>
        <p style="margin-bottom: 20px;">Your password is securely hashed before storage. We employ standard technical measures to safeguard your personal data.</p>

        <h2 style="font-size: 18px; margin-top: 32px; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">4. Your Rights (GDPR)</h2>
        <p style="margin-bottom: 20px;">As a user, you have the right to access, rectify, or erase your personal data. You can download all your data in JSON format or permanently delete your account entirely from the Account Settings page.</p>

        <h2 style="font-size: 18px; margin-top: 32px; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">5. Cookies</h2>
        <p style="margin-bottom: 20px;">SmartSpend only uses strictly necessary session cookies to maintain your logged-in state. We do not use tracking or advertising cookies.</p>
    </section>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
