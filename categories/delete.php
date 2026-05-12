<?php
/**
 * Page:      categories/delete.php
 * Component: Category Management — Toggle Active State
 * Developer: Ratnesh Kumar Yadav (Category Management)
 *
 * OOP REWRITE:
 * The procedural toggle logic has been converted into the
 * CategoryToggle class below.
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

$uid = (int)$_SESSION['user_id'];
$id  = (int)($_POST['category_id'] ?? 0);

if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid category.'];
    header('Location: /smartspend/categories/index.php');
    exit;
}

// ════════════════════════════════════════════════════════════════════
//  CLASS: CategoryToggle
//
//  What it is:
//    The blueprint for an object that flips a category between
//    active and inactive. It includes a safety guard that prevents
//    deactivating the last remaining active category.
//
//  How to use it:
//    $toggle = new CategoryToggle($uid, $id, $pdo);  // create object
//    $toggle->run();                                   // execute toggle
// ════════════════════════════════════════════════════════════════════
class CategoryToggle
{
    // ── Properties ──────────────────────────────────────────────────

    private int  $userId;
    private int  $categoryId;
    private \PDO $pdo;

    // ── Constructor ──────────────────────────────────────────────────

    public function __construct(int $userId, int $categoryId, \PDO $pdo)
    {
        $this->userId     = $userId;
        $this->categoryId = $categoryId;
        $this->pdo        = $pdo;
    }

    // ── Private method: findCategory() ───────────────────────────────
    // Looks up the category and returns it, or null if not found.

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

    // ── Private method: countActive() ────────────────────────────────
    // Returns how many active categories the user currently has.
    // Used to guard against deactivating the only active category.

    private function countActive(): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tblCategory WHERE is_active = 1 AND user_id = ?'
        );
        $stmt->execute([$this->userId]);
        return (int)$stmt->fetchColumn();
    }

    // ── Private method: toggle() ─────────────────────────────────────
    // Flips is_active from 1 to 0 or 0 to 1.

    private function toggle(): void
    {
        $this->pdo->prepare(
            'UPDATE tblCategory SET is_active = 1 - is_active
             WHERE category_id = ? AND user_id = ?'
        )->execute([$this->categoryId, $this->userId]);
    }

    // ── Public method: run() ─────────────────────────────────────────
    // The single public entry point. Checks ownership, runs the guard,
    // then toggles the state. Always redirects at the end.
    // Called as: $toggle->run()

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

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
// ════════════════════════════════════════════════════════════════════

$toggle = new CategoryToggle($uid, $id, $pdo);
$toggle->run();

