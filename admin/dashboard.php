<?php
/**
 * Page:      admin/dashboard.php
 * Component: Admin Panel — Dashboard
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$stat_users      = (int)$pdo->query('SELECT COUNT(*) FROM tblUser')->fetchColumn();
$stat_expenses   = (int)$pdo->query('SELECT COUNT(*) FROM tblExpense WHERE is_deleted = 0')->fetchColumn();
$stat_categories = (int)$pdo->query('SELECT COUNT(*) FROM tblCategory WHERE is_active = 1')->fetchColumn();
$stat_audit      = (int)$pdo->query('SELECT COUNT(*) FROM tblAuditLog')->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
</div>

<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-number"><?= $stat_users ?></div>
        <div class="stat-label">Total registered users</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stat_expenses ?></div>
        <div class="stat-label">Active expenses</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stat_categories ?></div>
        <div class="stat-label">Active categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stat_audit ?></div>
        <div class="stat-label">Audit log entries</div>
    </div>
</div>

<div class="admin-nav-grid">
    <a href="/smartspend/admin/users.php"      class="admin-nav-card" id="admin-nav-users">
        <span class="admin-nav-icon">👤</span>Users
    </a>
    <a href="/smartspend/admin/categories.php" class="admin-nav-card" id="admin-nav-categories">
        <span class="admin-nav-icon">🏷️</span>Categories
    </a>
    <a href="/smartspend/admin/expenses.php"   class="admin-nav-card" id="admin-nav-expenses">
        <span class="admin-nav-icon">💳</span>Expenses
    </a>
    <a href="/smartspend/admin/audit.php"      class="admin-nav-card" id="admin-nav-audit">
        <span class="admin-nav-icon">📋</span>Audit Log
    </a>
    <a href="/smartspend/admin/reports.php"    class="admin-nav-card" id="admin-nav-reports">
        <span class="admin-nav-icon">📊</span>Reports
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
