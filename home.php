<?php
/**
 * Page:      home.php
 * Component: Public Homepage
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();

// Redirect logged-in users straight to their dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /smartspend/dashboard/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="SmartSpend is a personal expense tracker that helps you log, categorise, and understand your spending in minutes. Free to use, no card required.">
    <title>SmartSpend: Personal Expense Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/smartspend/assets/css/style.css">
    <link rel="stylesheet" href="/smartspend/assets/css/home.css">
</head>

<body class="home-page">

    <!-- Public Navigation -->
    <nav class="home-nav" role="navigation" aria-label="Main navigation">
        <div class="home-nav-inner">
            <a href="/smartspend/home.php" class="home-nav-brand" aria-label="SmartSpend home">
                <img src="/smartspend/assets/img/SmartSpend.svg" alt="SmartSpend logo" width="36" height="36">
                <span>SmartSpend</span>
            </a>
            <div class="home-nav-actions">
                <a href="/smartspend/auth/login.php" class="home-nav-login">Log in</a>
                <a href="/smartspend/auth/register.php" class="home-nav-cta" id="nav-get-started">Get started free</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" aria-labelledby="hero-heading">
        <div class="hero-inner">
            <div class="hero-badge">
                <span class="hero-badge-dot" aria-hidden="true"></span>
                Simple &middot; Secure &middot; Free
            </div>
            <h1 id="hero-heading" class="hero-heading">
                Take control of<br>
                <span class="hero-heading-accent">your spending</span>
            </h1>
            <p class="hero-subheading">
                SmartSpend helps you log every expense, understand where your money goes, and make smarter financial decisions. One clean, premium dashboard.
            </p>
            <div class="hero-actions">
                <a href="/smartspend/auth/register.php" class="btn-hero-primary" id="hero-cta-register">
                    Create free account
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="/smartspend/auth/login.php" class="btn-hero-secondary" id="hero-cta-login">
                    Log in to your account
                </a>
            </div>
            <p class="hero-note">No credit card required &middot; Takes 30 seconds to set up</p>
        </div>

        <!-- Floating dashboard preview card -->
        <div class="hero-preview" aria-hidden="true">
            <div class="preview-card">
                <div class="preview-header">
                    <div class="preview-dot preview-dot-red"></div>
                    <div class="preview-dot preview-dot-yellow"></div>
                    <div class="preview-dot preview-dot-green"></div>
                    <span class="preview-title">Dashboard Overview</span>
                </div>
                <div class="preview-stats">
                    <div class="preview-stat">
                        <span class="preview-stat-value">£842.50</span>
                        <span class="preview-stat-label">Spent this month</span>
                    </div>
                    <div class="preview-stat">
                        <span class="preview-stat-value preview-stat-green">£157.50</span>
                        <span class="preview-stat-label">Under budget</span>
                    </div>
                    <div class="preview-stat">
                        <span class="preview-stat-value">14</span>
                        <span class="preview-stat-label">Transactions</span>
                    </div>
                </div>
                <div class="preview-chart" aria-label="Spending breakdown chart">
                    <div class="preview-bar-row">
                        <span class="preview-bar-label">Food</span>
                        <div class="preview-bar-track">
                            <div class="preview-bar preview-bar-fill" style="width: 72%"></div>
                        </div>
                        <span class="preview-bar-val">£302</span>
                    </div>
                    <div class="preview-bar-row">
                        <span class="preview-bar-label">Transport</span>
                        <div class="preview-bar-track">
                            <div class="preview-bar preview-bar-fill preview-bar-alt" style="width: 42%"></div>
                        </div>
                        <span class="preview-bar-val">£178</span>
                    </div>
                    <div class="preview-bar-row">
                        <span class="preview-bar-label">Utilities</span>
                        <div class="preview-bar-track">
                            <div class="preview-bar preview-bar-fill preview-bar-muted" style="width: 28%"></div>
                        </div>
                        <span class="preview-bar-val">£119</span>
                    </div>
                    <div class="preview-bar-row">
                        <span class="preview-bar-label">Entertainment</span>
                        <div class="preview-bar-track">
                            <div class="preview-bar preview-bar-fill preview-bar-warn" style="width: 18%"></div>
                        </div>
                        <span class="preview-bar-val">£75</span>
                    </div>
                </div>
                <div class="preview-recent">
                    <p class="preview-recent-title">Recent expenses</p>
                    <div class="preview-expense-row">
                        <span class="preview-expense-icon">🛒</span>
                        <span class="preview-expense-name">Weekly groceries</span>
                        <span class="preview-expense-amount">£68.40</span>
                    </div>
                    <div class="preview-expense-row">
                        <span class="preview-expense-icon">🚌</span>
                        <span class="preview-expense-name">Monthly bus pass</span>
                        <span class="preview-expense-amount">£52.00</span>
                    </div>
                    <div class="preview-expense-row">
                        <span class="preview-expense-icon">☕</span>
                        <span class="preview-expense-name">Coffee &amp; lunch</span>
                        <span class="preview-expense-amount">£14.80</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Strip -->
    <section class="stats-strip" aria-label="Platform highlights">
        <div class="stats-strip-inner">
            <div class="strip-stat">
                <strong>100% Free</strong>
                <span>No subscriptions, no ads</span>
            </div>
            <div class="strip-divider" aria-hidden="true"></div>
            <div class="strip-stat">
                <strong>Custom Categories</strong>
                <span>Tailored budget types</span>
            </div>
            <div class="strip-divider" aria-hidden="true"></div>
            <div class="strip-stat">
                <strong>CSV Reports</strong>
                <span>One-click downloads</span>
            </div>
            <div class="strip-divider" aria-hidden="true"></div>
            <div class="strip-stat">
                <strong>GDPR Ready</strong>
                <span>Full personal data control</span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features" aria-labelledby="features-heading">
        <div class="features-inner">
            <div class="section-label">Features Overview</div>
            <h2 id="features-heading" class="section-heading">
                Everything you need to track spending
            </h2>
            <p class="section-subheading">
                Simple, intuitive tools to track expenditures, analyze category habits, and maintain clear records with minimal effort.
            </p>

            <div class="features-grid">

                <div class="feature-card" id="feature-expenses">
                    <div class="feature-icon feature-icon-green" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <h3>Expense Tracking</h3>
                    <p>Log transactions with descriptions, amounts, categories, and dates. Includes validation safety and paginated historical lists.</p>
                </div>

                <div class="feature-card" id="feature-dashboard">
                    <div class="feature-icon feature-icon-blue" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                    </div>
                    <h3>Interactive Dashboard</h3>
                    <p>Review real-time financial stats including total monthly expenditures, transaction count summaries, and top-spending category trends.</p>
                </div>

                <div class="feature-card" id="feature-reports">
                    <div class="feature-icon feature-icon-purple" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14,2 14,8 20,8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10,9 9,9 8,9" />
                        </svg>
                    </div>
                    <h3>CSV Export &amp; Reporting</h3>
                    <p>Generate detailed reports matching specific dates. Download a clean, formatted CSV document compatible with all spreadsheet software.</p>
                </div>

                <div class="feature-card" id="feature-history">
                    <div class="feature-icon feature-icon-orange" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <h3>Personal History Audit</h3>
                    <p>View a transparent history log tracking all changes. Entries preserve detailed before/after snapshots of records for clear self-accounting.</p>
                </div>

                <div class="feature-card" id="feature-categories">
                    <div class="feature-icon feature-icon-teal" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                            <line x1="7" y1="7" x2="7.01" y2="7" />
                        </svg>
                    </div>
                    <h3>Flexible Categories</h3>
                    <p>Customize spending categories to fit your lifestyle. Form validation prevents duplicate category titles and keeps records structured.</p>
                </div>

                <div class="feature-card" id="feature-security">
                    <div class="feature-icon feature-icon-red" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>
                    <h3>Secure &amp; GDPR Compliant</h3>
                    <p>Hashed passwords using BCRYPT algorithms and PDO queries protect data. Download profile information in JSON or erase account files instantly.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works" aria-labelledby="how-heading">
        <div class="how-inner">
            <div class="section-label section-label-white">How it works</div>
            <h2 id="how-heading" class="section-heading section-heading-white">
                Take control in three steps
            </h2>
            <div class="steps">
                <div class="step" id="step-1">
                    <div class="step-number" aria-hidden="true">1</div>
                    <div class="step-content">
                        <h3>Create your profile</h3>
                        <p>Sign up securely with your email and password in seconds. No setup required.</p>
                    </div>
                </div>
                <div class="step-connector" aria-hidden="true"></div>
                <div class="step" id="step-2">
                    <div class="step-number" aria-hidden="true">2</div>
                    <div class="step-content">
                        <h3>Log your transactions</h3>
                        <p>Add expenditures with custom categories, dates, and amounts straight from your dashboard.</p>
                    </div>
                </div>
                <div class="step-connector" aria-hidden="true"></div>
                <div class="step" id="step-3">
                    <div class="step-number" aria-hidden="true">3</div>
                    <div class="step-content">
                        <h3>Analyze and export</h3>
                        <p>Watch interactive statistics update instantly. Filter transactions and download CSV reports in one click.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="cta-section" aria-labelledby="cta-heading">
        <div class="cta-inner">
            <div class="cta-icon" aria-hidden="true">
                <img src="/smartspend/assets/img/SmartSpend.svg" alt="" width="64" height="64">
            </div>
            <h2 id="cta-heading" class="cta-heading">Ready to start tracking?</h2>
            <p class="cta-subheading">
                Join SmartSpend for free today. Take control of your financial future.
            </p>
            <a href="/smartspend/auth/register.php" class="btn-cta" id="footer-cta-register">
                Get started (it's free)
            </a>
            <p class="cta-login-link">
                Already have an account?
                <a href="/smartspend/auth/login.php" id="footer-cta-login">Log in here</a>
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="home-footer" role="contentinfo">
        <div class="home-footer-inner">
            <a href="/smartspend/home.php" class="home-footer-brand" aria-label="SmartSpend home">
                <img src="/smartspend/assets/img/SmartSpend.svg" alt="SmartSpend logo" width="24" height="24">
                <span>SmartSpend</span>
            </a>
            <p class="home-footer-copy">
                &copy; <?= date('Y') ?> SmartSpend. Personal finance management system.
            </p>
            <nav class="home-footer-links" aria-label="Footer navigation">
                <a href="/smartspend/auth/login.php">Login</a>
                <a href="/smartspend/auth/register.php">Register</a>
            </nav>
        </div>
    </footer>

    <script src="/smartspend/assets/js/main.js"></script>
    <script>
        // Animate feature cards and steps as they scroll into view
        const observeEls = document.querySelectorAll('.feature-card, .step, .strip-stat, .preview-card');
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            observeEls.forEach(el => io.observe(el));
        } else {
            observeEls.forEach(el => el.classList.add('is-visible'));
        }
    </script>

</body>
</html>
