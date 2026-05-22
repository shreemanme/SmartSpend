<?php
// expenses/delete.php — Soft-deletes an expense via the ExpenseDeleter class.

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

// Checks ownership, sets is_deleted = 1, and writes an audit log entry.
class ExpenseDeleter
{
    private int  $userId;
    private int  $expenseId;
    private \PDO $pdo;


    public function __construct(int $userId, int $expenseId, \PDO $pdo)
    {
        $this->userId    = $userId;
        $this->expenseId = $expenseId;
        $this->pdo       = $pdo;
    }

    // Returns the expense row (with category_name) or null if not found / not owned by this user.

    private function findExpense(): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, c.category_name
             FROM tblExpense e
             LEFT JOIN tblCategory c ON c.category_id = e.category_id
             WHERE e.expense_id = ? AND e.user_id = ? AND e.is_deleted = 0'
        );
        $stmt->execute([$this->expenseId, $this->userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Marks is_deleted = 1; does not physically remove the row.

    private function softDelete(): void
    {
        $this->pdo->prepare(
            'UPDATE tblExpense SET is_deleted = 1
             WHERE expense_id = ? AND user_id = ?'
        )->execute([$this->expenseId, $this->userId]);
    }

    // Inserts a DELETE entry into tblAuditLog with the old row data.

    private function writeAuditLog(string $oldJson): void
    {
        $this->pdo->prepare(
            'INSERT INTO tblAuditLog (user_id, expense_id, action_type, action_date, old_value, is_reviewed)
             VALUES (?, ?, \'DELETE\', CURDATE(), ?, 0)'
        )->execute([$this->userId, $this->expenseId, $oldJson]);
    }

    // Verifies ownership, soft-deletes, logs, then redirects.

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

$deleter = new ExpenseDeleter($uid, $id, $pdo);
$deleter->run();

