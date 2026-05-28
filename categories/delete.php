<?php
// categories/delete.php — Developer: Ratnesh Kumar Yadav (Category Management)

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

$uid    = (int)$_SESSION['user_id'];
$id     = (int)($_POST['category_id'] ?? 0);
$action = $_POST['action'] ?? 'toggle'; // 'toggle' | 'delete'

if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid category.'];
    header('Location: /smartspend/categories/index.php');
    exit;
}

// Flips a category between active/inactive; prevents deactivating the last one.
class CategoryToggle
{

    private int  $userId;
    private int  $categoryId;
    private \PDO $pdo;

    public function __construct(int $userId, int $categoryId, \PDO $pdo)
    {
        $this->userId     = $userId;
        $this->categoryId = $categoryId;
        $this->pdo        = $pdo;
    }

    // Fetches the category row; returns null if not found.
    private function findCategory(): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT is_active FROM tblCategory
             WHERE category_id = ? AND user_id = ?'
        );
        $stmt->execute([$this->categoryId, $this->userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Returns the count of the user's currently active categories.
    private function countActive(): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tblCategory WHERE is_active = 1 AND user_id = ?'
        );
        $stmt->execute([$this->userId]);
        return (int)$stmt->fetchColumn();
    }

    // Flips is_active between 1 and 0 for this category.
    private function toggle(): void
    {
        $this->pdo->prepare(
            'UPDATE tblCategory SET is_active = 1 - is_active
             WHERE category_id = ? AND user_id = ?'
        )->execute([$this->categoryId, $this->userId]);
    }

    // Checks ownership, guards against deactivating the last category, then toggles.
    public function run(): void
    {
        $cat = $this->findCategory();

        if (!$cat) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Category not found.'];
            header('Location: /smartspend/categories/index.php');
            exit;
        }

        // Guard: cannot deactivate the only active category
        if ((int)$cat['is_active'] === 1 && $this->countActive() <= 1) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Cannot deactivate the only active category.'];
            header('Location: /smartspend/categories/index.php');
            exit;
        }

        $this->toggle();

        $newState = (int)$cat['is_active'] === 1 ? 'deactivated' : 'activated';
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Category {$newState}."];
        header('Location: /smartspend/categories/index.php');
        exit;
    }
}

// Permanently removes a category; guards against deleting categories with linked expenses.
class CategoryDelete
{

    private int  $userId;
    private int  $categoryId;
    private \PDO $pdo;

    public function __construct(int $userId, int $categoryId, \PDO $pdo)
    {
        $this->userId     = $userId;
        $this->categoryId = $categoryId;
        $this->pdo        = $pdo;
    }

    // Fetches the category row; returns null if not found or not owned by this user.
    private function findCategory(): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT category_id, category_name FROM tblCategory
             WHERE category_id = ? AND user_id = ?'
        );
        $stmt->execute([$this->categoryId, $this->userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Returns the number of expenses linked to this category.
    private function countLinkedExpenses(): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tblExpense WHERE category_id = ? AND user_id = ?'
        );
        $stmt->execute([$this->categoryId, $this->userId]);
        return (int)$stmt->fetchColumn();
    }

    // Verifies ownership, checks for linked expenses, then hard-deletes the row.
    public function run(): void
    {
        $cat = $this->findCategory();

        if (!$cat) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Category not found.'];
            header('Location: /smartspend/categories/index.php');
            exit;
        }

        if ($this->countLinkedExpenses() > 0) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => "Cannot delete \"{$cat['category_name']}\" — it has linked expenses. Remove or reassign those expenses first."
            ];
            header('Location: /smartspend/categories/index.php');
            exit;
        }

        $this->pdo->prepare(
            'DELETE FROM tblCategory WHERE category_id = ? AND user_id = ?'
        )->execute([$this->categoryId, $this->userId]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => "Category \"{$cat['category_name']}\" deleted successfully."
        ];
        header('Location: /smartspend/categories/index.php');
        exit;
    }
}

// Route to the appropriate class based on the submitted action.
if ($action === 'delete') {
    $handler = new CategoryDelete($uid, $id, $pdo);
} else {
    $handler = new CategoryToggle($uid, $id, $pdo);
}

$handler->run();
