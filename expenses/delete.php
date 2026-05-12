<?php
/**
 * Page:      expenses/delete.php
 * Component: Expense Entry Management — Soft Delete
 * Developer: Shreeman Bhandari (Scrum Master & Expense Management)
 *
 * OOP REWRITE:
 * The procedural soft-delete logic has been converted into the
 * ExpenseDeleter class below.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smartspend/expenses/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$uid = (int)$_SESSION['user_id'];
$id  = (int)($_POST['expense_id'] ?? 0);

if ($id === 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid expense.'];
    header('Location: /smartspend/expenses/index.php');
    exit;
}

// ════════════════════════════════════════════════════════════════════
//  CLASS: ExpenseDeleter
//
//  What it is:
//    The blueprint for an object that safely soft-deletes an expense.
//    It first checks ownership, then marks is_deleted = 1, then
//    writes an audit log entry.
//
//  How to use it:
//    $deleter = new ExpenseDeleter($uid, $id, $pdo);  // create object
//    $deleter->run();                                   // execute delete
// ════════════════════════════════════════════════════════════════════
class ExpenseDeleter
{
    // ── Properties ──────────────────────────────────────────────────

    private int  $userId;     // Logged-in user's ID
    private int  $expenseId;  // ID of the expense to delete
    private \PDO $pdo;        // Database connection

    // ── Constructor ──────────────────────────────────────────────────
    // Stores the IDs and database connection for use in run().

    public function __construct(int $userId, int $expenseId, \PDO $pdo)
    {
        $this->userId    = $userId;
        $this->expenseId = $expenseId;
        $this->pdo       = $pdo;
    }

    // ── Private method: findExpense() ────────────────────────────────
    // Looks up the expense and returns it, or null if not found.
    // "private" because it's an internal helper — run() calls it.

    private function findExpense(): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM tblExpense
             WHERE expense_id = ? AND user_id = ? AND is_deleted = 0'
        );
        $stmt->execute([$this->expenseId, $this->userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Private method: softDelete() ─────────────────────────────────
    // Sets is_deleted = 1 (does NOT physically remove the row).

    private function softDelete(): void
    {
        $this->pdo->prepare(
            'UPDATE tblExpense SET is_deleted = 1
             WHERE expense_id = ? AND user_id = ?'
        )->execute([$this->expenseId, $this->userId]);
    }

    // ── Private method: writeAuditLog() ─────────────────────────────
    // Records the DELETE action and the old data in the audit log.

    private function writeAuditLog(string $oldJson): void
    {
        $this->pdo->prepare(
            'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
             VALUES (?, ?, \'DELETE\', CURDATE(), ?, 0)'
        )->execute([$this->userId, $this->expenseId, $oldJson]);
    }

    // ── Public method: run() ─────────────────────────────────────────
    // The single public entry point: checks ownership, deletes, logs.
    // Always redirects at the end.
    // Called as: $deleter->run()

    public function run(): void
    {
        $row = $this->findExpense();

        if (!$row) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Expense not found.'];
            header('Location: /smartspend/expenses/index.php');
            exit;
        }

        $this->softDelete();
        $this->writeAuditLog(json_encode($row));

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Expense deleted.'];
        header('Location: /smartspend/expenses/index.php');
        exit;
    }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
//
//  new ExpenseDeleter($uid, $id, $pdo)
//    → Creates the object from the ExpenseDeleter blueprint.
//
//  $deleter->run()
//    → Calls the run method on the object using the -> arrow.
// ════════════════════════════════════════════════════════════════════

$deleter = new ExpenseDeleter($uid, $id, $pdo);
$deleter->run();

