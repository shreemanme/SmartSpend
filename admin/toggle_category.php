<?php
/**
 * Page:      admin/toggle_category.php
 * Component: Admin Panel — Toggle Category Active State
 * Developer: Ratnesh Kumar Yadav (Category Management)
 */

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /smartspend/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/admin/categories.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = (int)($_POST['category_id'] ?? 0);
if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid category.'];
    header('Location: /smartspend/admin/categories.php');
    exit;
}

$stmt = $pdo->prepare('SELECT is_active FROM tblCategory WHERE category_id = ?');
$stmt->execute([$id]);
$cat = $stmt->fetch();

if (!$cat) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Category not found.'];
    header('Location: /smartspend/admin/categories.php');
    exit;
}

// Guard: cannot deactivate the only active category
if ((int)$cat['is_active'] === 1) {
    $active_count = (int)$pdo->query('SELECT COUNT(*) FROM tblCategory WHERE is_active = 1')->fetchColumn();
    if ($active_count <= 1) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Cannot deactivate the only active category.'];
        header('Location: /smartspend/admin/categories.php');
        exit;
    }
}

$pdo->prepare('UPDATE tblCategory SET is_active = 1 - is_active WHERE category_id = ?')->execute([$id]);

$new_state = (int)$cat['is_active'] === 1 ? 'deactivated' : 'activated';
$_SESSION['flash'] = ['type' => 'success', 'msg' => "Category $new_state."];
header('Location: /smartspend/admin/categories.php');
exit;
