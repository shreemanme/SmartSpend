<?php
/**
 * Page:      history/index.php
 * Component: Audit Log — User View
 * Developer: Bibek Timsena (Audit & History Log)
 *
 * OOP REWRITE:
 * The procedural filter/query logic has been converted into the
 * AuditFilter class below. The HTML template is unchanged.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$uid = (int)$_SESSION['user_id'];

// ════════════════════════════════════════════════════════════════════
//  CLASS: AuditFilter
//
//  What it is:
//    The blueprint for an object that reads search/action filters
//    from the URL and fetches the matching audit log entries.
//
//  How to use it:
//    $filter = new AuditFilter($uid, $_GET);   // create the object
//    $logs = $filter->getLogs($pdo);            // fetch log rows
//    $search = $filter->getSearch();            // read a filter value
// ════════════════════════════════════════════════════════════════════
class AuditFilter
{
    // ── Properties ──────────────────────────────────────────────────

    private int    $userId;
    private string $search;
    private string $filterAction;  // 'CREATE', 'UPDATE', 'DELETE', 'MANUAL', or ''

    // ── Constructor ──────────────────────────────────────────────────
    // Reads and stores the filter values from the URL.

    public function __construct(int $userId, array $get = [])
    {
        $this->userId       = $userId;
        $this->search       = trim($get['search'] ?? '');
        $this->filterAction = trim($get['action'] ?? '');
    }

    // ── Public method: getLogs() ─────────────────────────────────────
    // Builds the WHERE clause and returns the matching audit log rows.
    // Called as: $filter->getLogs($pdo)

    public function getLogs(\PDO $pdo): array
    {
        $where  = ['user_id = ?'];
        $params = [$this->userId];

        if ($this->search !== '') {
            $where[]  = 'action_type LIKE ?';
            $params[] = "%{$this->search}%";
        }
        if ($this->filterAction !== '') {
            $where[]  = 'action_type = ?';
            $params[] = $this->filterAction;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        $stmt = $pdo->prepare(
            "SELECT * FROM tblAuditLog $whereClause ORDER BY action_date DESC, log_id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Getter methods ───────────────────────────────────────────────

    public function getSearch(): string       { return $this->search; }
    public function getFilterAction(): string { return $this->filterAction; }

    // Returns true if any filter is active — used to show the Clear button
    public function hasActiveFilter(): bool
    {
        return $this->search !== '' || $this->filterAction !== '';
    }
}

// ════════════════════════════════════════════════════════════════════
//  CREATING THE OBJECT & CALLING METHODS
// ════════════════════════════════════════════════════════════════════

$filter       = new AuditFilter($uid, $_GET);
$logs         = $filter->getLogs($pdo);
$search       = $filter->getSearch();
$filter_action = $filter->getFilterAction();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>My History Log</h1>
    <a href="/smartspend/history/add.php" class="btn-primary">+ Add Note</a>
</div>

<!-- Search and Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-end;">
    <div class="form-group" style="flex: 1;">
        <label for="search">Find (Action)</label>
        <input type="text" id="search" name="search" placeholder="Search action..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="form-group">
        <label for="action">Filter by Action</label>
        <select id="action" name="action">
            <option value="">All Actions</option>
            <option value="CREATE" <?= $filter_action === 'CREATE' ? 'selected' : '' ?>>Create</option>
            <option value="UPDATE" <?= $filter_action === 'UPDATE' ? 'selected' : '' ?>>Update</option>
            <option value="DELETE" <?= $filter_action === 'DELETE' ? 'selected' : '' ?>>Delete</option>
            <option value="MANUAL" <?= $filter_action === 'MANUAL' ? 'selected' : '' ?>>Manual Note</option>
        </select>
    </div>
    <div class="form-group" style="flex: 0;">
        <button type="submit" class="btn-primary">Apply</button>
    </div>
    <?php if ($filter->hasActiveFilter()): ?>
    <div class="form-group" style="flex: 0;">
        <a href="/smartspend/history/index.php" class="btn-secondary" style="display:inline-block; padding:8px 12px; text-decoration:none;">Clear</a>
    </div>
    <?php endif; ?>
</form>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Action</th>
                <th>Details</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['action_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="badge badge-secondary"><?= htmlspecialchars($log['action_type'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td>
                    <?php
                        $oldVal = $log['old_value'] ?? '';
                        $decoded = json_decode($oldVal, true);
                        if (is_array($decoded)) {
                            // Check if it's an expense
                            if (isset($decoded['amount']) && isset($decoded['expense_date'])) {
                                $amt = number_format((float)$decoded['amount'], 2);
                                $date = htmlspecialchars($decoded['expense_date'], ENT_QUOTES, 'UTF-8');
                                $desc = !empty($decoded['description']) ? htmlspecialchars($decoded['description'], ENT_QUOTES, 'UTF-8') : 'No description';
                                echo "Expense: <strong>$" . $amt . "</strong> on " . $date . " <br><small><em>(" . $desc . ")</em></small>";
                            } 
                            // Check if it's a category
                            elseif (isset($decoded['category_name'])) {
                                echo "Category: <strong>" . htmlspecialchars($decoded['category_name'], ENT_QUOTES, 'UTF-8') . "</strong>";
                            } 
                            // Generic fallback filtering out database IDs
                            else {
                                $parts = [];
                                $hidden_keys = ['expense_id', 'user_id', 'category_id', 'is_deleted', 'is_active', 'created_at', 'updated_at', 'log_id', 'password_hash'];
                                foreach ($decoded as $k => $v) {
                                    if (in_array($k, $hidden_keys)) continue;
                                    
                                    $cleanKey = ucwords(str_replace('_', ' ', $k));
                                    $cleanVal = $v === '' ? '—' : (string)$v;
                                    
                                    if ($k === 'amount') {
                                        $cleanVal = '$' . number_format((float)$v, 2);
                                    }
                                    
                                    $parts[] = '<strong>' . htmlspecialchars($cleanKey, ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($cleanVal, ENT_QUOTES, 'UTF-8');
                                }
                                echo empty($parts) ? '<em>Record updated</em>' : implode('<br>', $parts);
                            }
                        } else {
                            echo htmlspecialchars($oldVal === '' ? '—' : $oldVal, ENT_QUOTES, 'UTF-8');
                        }
                    ?>
                </td>
                <td>
                    <?= (int)$log['is_reviewed'] === 1 ? '<span class="badge badge-success">Reviewed</span>' : '<span class="badge badge-warning">Pending</span>' ?>
                </td>
                <td>
                    <div class="actions-cell">
                        <?php if ((int)$log['is_reviewed'] === 0): ?>
                        <form method="POST" action="/smartspend/history/edit.php" style="display:inline;">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-warning btn-sm">Acknowledge</button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" action="/smartspend/history/delete.php" style="display:inline;">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Delete this log entry?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
