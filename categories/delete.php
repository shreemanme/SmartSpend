<?php
/**
 * Page:      admin/toggle_category.php
 * Component: Admin Panel — Toggle Category Active State
 * Developer: Ratnesh Kumar Yadav (Category Management)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/categories/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = (int)($_POST['category_id'] ?? 0);
if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid category.'];
    header('Location: /smartspend/categories/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT is_active FROM tblCategory WHERE category_id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$cat = $stmt->fetch();

if (!$cat) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Category not found.'];
    header('Location: /smartspend/categories/index.php');
    exit;
}

// Guard: cannot deactivate the only active category
if ((int)$cat['is_active'] === 1) {
    $stmt_count = $pdo->prepare('SELECT COUNT(*) FROM tblCategory WHERE is_active = 1 AND user_id = ?');
    $stmt_count->execute([$_SESSION['user_id']]);
    $active_count = (int)$stmt_count->fetchColumn();
    if ($active_count <= 1) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Cannot deactivate the only active category.'];
        header('Location: /smartspend/categories/index.php');
        exit;
    }
}

$pdo->prepare('UPDATE tblCategory SET is_active = 1 - is_active WHERE category_id = ? AND user_id = ?')->execute([$id, $_SESSION['user_id']]);

$new_state = (int)$cat['is_active'] === 1 ? 'deactivated' : 'activated';
$_SESSION['flash'] = ['type' => 'success', 'msg' => "Category $new_state."];
header('Location: /smartspend/categories/index.php');
exit;
