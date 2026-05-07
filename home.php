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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        <div class="hero-bg-shapes" aria-hidden="true">
            <div class="hero-shape hero-shape-1"></div>
            <div class="hero-shape hero-shape-2"></div>
            <div class="hero-shape hero-shape-3"></div>
        </div>
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
                SmartSpend helps you log every expense, understand where your money goes,
                and make smarter financial decisions. One clean dashboard.
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
                    <span class="preview-title">Dashboard, May 2026</span>
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
                <strong>100%</strong>
                <span>Free to use</span>
            </div>
            <div class="strip-divider" aria-hidden="true"></div>
            <div class="strip-stat">
                <strong>5 categories</strong>
                <span>Built-in expense types</span>
            </div>
            <div class="strip-divider" aria-hidden="true"></div>
            <div class="strip-stat">
                <strong>CSV export</strong>
                <span>Download your reports</span>
            </div>
            <div class="strip-divider" aria-hidden="true"></div>
            <div class="strip-stat">
                <strong>bcrypt</strong>
                <span>Password security</span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features" aria-labelledby="features-heading">
        <div class="features-inner">
            <div class="section-label">What you get</div>
            <h2 id="features-heading" class="section-heading">
                Everything you need to track spending
            </h2>
            <p class="section-subheading">
                Built without bloat. SmartSpend gives you exactly the tools you need,
                nothing more, nothing less.
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
                    <p>Log expenses with a title, amount, category, and date. Edit or remove them any time. Soft-delete means nothing is ever lost.</p>
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
                    <h3>Live Dashboard</h3>
                    <p>See your month-to-date total, highest spending category, and recent transactions the moment you log in. No setup required.</p>
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
                    <h3>Reports &amp; CSV Export</h3>
                    <p>Filter expenses by date range, see category totals, and export the whole lot to a CSV file for use in any spreadsheet app.</p>
                </div>

                <div class="feature-card" id="feature-account">
                    <div class="feature-icon feature-icon-orange" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <h3>Secure Accounts</h3>
                    <p>Register in seconds. Passwords are hashed with bcrypt and all database queries use PDO prepared statements (security by default).</p>
                </div>

                <div class="feature-card" id="feature-categories">
                    <div class="feature-icon feature-icon-teal" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                            <line x1="7" y1="7" x2="7.01" y2="7" />
                        </svg>
                    </div>
                    <h3>Smart Categories</h3>
                    <p>Organise spending into clear categories (Food, Transport, Utilities, Entertainment, and more) so trends become obvious at a glance.</p>
                </div>

                <div class="feature-card" id="feature-admin">
                    <div class="feature-icon feature-icon-red" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                        </svg>
                    </div>
                    <h3>Admin Panel</h3>
                    <p>Admins can manage users, oversee all categories, and review a full audit log of every create, update, and delete action in the system.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works" aria-labelledby="how-heading">
        <div class="how-inner">
            <div class="section-label section-label-white">How it works</div>
            <h2 id="how-heading" class="section-heading section-heading-white">
                Up and running in three steps
            </h2>
            <div class="steps">
                <div class="step" id="step-1">
                    <div class="step-number" aria-hidden="true">1</div>
                    <div class="step-content">
                        <h3>Create your account</h3>
                        <p>Register with your name, email, and a password. You're in straight away.</p>
                    </div>
                </div>
                <div class="step-connector" aria-hidden="true"></div>
                <div class="step" id="step-2">
                    <div class="step-number" aria-hidden="true">2</div>
                    <div class="step-content">
                        <h3>Log your expenses</h3>
                        <p>Add expenses with a description, amount, category, and date. Takes five seconds per entry, less time than the receipt is in your pocket.</p>
                    </div>
                </div>
                <div class="step-connector" aria-hidden="true"></div>
                <div class="step" id="step-3">
                    <div class="step-number" aria-hidden="true">3</div>
                    <div class="step-content">
                        <h3>Understand your spending</h3>
                        <p>Your dashboard updates instantly. Filter by date, download reports, and see exactly where every pound is going.</p>
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
                Join SmartSpend for free today. No subscriptions, no ads, no nonsense.
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
                &copy; <?= date('Y') ?> SmartSpend. CTEC2713 Agile Development Team Project.
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
