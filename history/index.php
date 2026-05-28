<?php
// history/index.php — Lists and filters audit log entries via the AuditFilter class.

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$uid = (int)$_SESSION['user_id'];

// Reads search/action filters from the URL and returns matching audit log rows.
class AuditFilter
{

    private int    $userId;
    private string $search;           // category name to search for
    private string $filterAction;     // 'CREATE', 'UPDATE', 'DELETE', 'MANUAL', or ''
    private string $searchError = '';
    private array  $matchedCategoryIds   = [];   // resolved category IDs from DB
    private array  $matchedCategoryNames = [];   // resolved category names from DB

    // Reads and stores the search and action filter values from $_GET.
    public function __construct(int $userId, array $get = [])
    {
        $this->userId       = $userId;
        $this->search       = trim($get['search'] ?? '');
        $this->filterAction = trim($get['action'] ?? '');
    }

    // Validates the search term against the user's real categories; sets $searchError on failure.
    private function validate(\PDO $pdo): bool
    {
        if ($this->search === '') return true;

        if (strlen($this->search) < 2) {
            $this->searchError = 'Category name must be at least 2 characters.';
            return false;
        }

        // Look up matching categories for this user only.
        $stmt = $pdo->prepare(
            'SELECT category_id, category_name FROM tblCategory WHERE user_id = ? AND category_name LIKE ?'
        );
        $stmt->execute([$this->userId, '%' . $this->search . '%']);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            $this->searchError = 'No category found matching &ldquo;' . htmlspecialchars($this->search, ENT_QUOTES, 'UTF-8') . '&rdquo;. Please enter a valid category name.';
            return false;
        }

        foreach ($rows as $row) {
            $this->matchedCategoryIds[]   = (int)$row['category_id'];
            $this->matchedCategoryNames[] = $row['category_name'];
        }

        return true;
    }

    // Builds the WHERE clause and returns matching audit log rows; returns [] if input is invalid.
    public function getLogs(\PDO $pdo): array
    {
        if (!$this->validate($pdo)) return [];

        $where  = ['user_id = ?'];
        $params = [$this->userId];

        // Search by category: match category_id or category_name stored in the JSON columns.
        if (!empty($this->matchedCategoryIds)) {
            $orParts = [];
            foreach ($this->matchedCategoryIds as $cid) {
                // Match stored category_id (json_encode outputs no space after colon)
                $orParts[] = 'old_value LIKE ?';
                $orParts[] = 'new_value LIKE ?';
                $params[]  = '%"category_id":' . $cid . '%';
                $params[]  = '%"category_id":' . $cid . '%';
            }
            foreach ($this->matchedCategoryNames as $cname) {
                // Match category_name stored directly in the JSON (e.g. category log entries)
                $orParts[] = 'old_value LIKE ?';
                $orParts[] = 'new_value LIKE ?';
                $params[]  = '%"category_name":"%' . $cname . '%';
                $params[]  = '%"category_name":"%' . $cname . '%';
            }
            $where[] = '(' . implode(' OR ', $orParts) . ')';
        }

        // Dropdown filter restricts to a specific action type.
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

    // Getters — allow the template to read private filter values.
    public function getSearch(): string       { return $this->search; }
    public function getFilterAction(): string { return $this->filterAction; }
    public function getSearchError(): string  { return $this->searchError; }

    // Returns true if any filter is active — used to show the Clear button.
    public function hasActiveFilter(): bool
    {
        return $this->search !== '' || $this->filterAction !== '';
    }
}


$filter        = new AuditFilter($uid, $_GET);
$logs          = $filter->getLogs($pdo);
$search        = $filter->getSearch();
$filter_action = $filter->getFilterAction();
$search_error  = $filter->getSearchError();

// Build a category_id → name map so we can resolve IDs stored in JSON.
$catStmt = $pdo->prepare('SELECT category_id, category_name FROM tblCategory WHERE user_id = ?');
$catStmt->execute([$uid]);
$categoryMap = [];
foreach ($catStmt->fetchAll() as $c) {
    $categoryMap[(int)$c['category_id']] = $c['category_name'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>My History Log</h1>
</div>

<!-- Search and Filter Bar -->
<form method="GET" action="" class="filter-bar" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-start;">
    <div class="form-group" style="flex: 1;">
        <label for="search">Search by Category</label>
        <input type="text" id="search" name="search"
               placeholder="e.g. Food, Transport, Shopping..."
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               class="<?= $search_error ? 'input-error' : '' ?>">
        <?php if ($search_error): ?>
            <span class="form-error"><?= $search_error ?></span>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="action">Filter by Action</label>
        <select id="action" name="action">
            <option value="">All Actions</option>
            <option value="CREATE" <?= $filter_action === 'CREATE' ? 'selected' : '' ?>>Created</option>
            <option value="UPDATE" <?= $filter_action === 'UPDATE' ? 'selected' : '' ?>>Updated</option>
            <option value="DELETE" <?= $filter_action === 'DELETE' ? 'selected' : '' ?>>Deleted</option>
            <option value="MANUAL" <?= $filter_action === 'MANUAL' ? 'selected' : '' ?>>Note Added</option>
        </select>
    </div>
    <div class="form-group" style="flex: 0;">
        <label>&nbsp;</label>
        <button type="submit" class="btn-primary">Apply</button>
    </div>
    <?php if ($filter->hasActiveFilter()): ?>
    <div class="form-group" style="flex: 0;">
        <label>&nbsp;</label>
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
            <?php if (empty($logs)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px 20px; color: var(--text-muted, #888);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:block; margin: 0 auto 12px; opacity: 0.4;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <?php if ($filter->hasActiveFilter()): ?>
                        <strong>No results found.</strong><br>
                        <span style="font-size: 0.875rem;">Try a different keyword or action filter, or <a href="/smartspend/history/index.php">clear all filters</a>.</span>
                    <?php else: ?>
                        <strong>No history entries yet.</strong><br>
                        <span style="font-size: 0.875rem;">Your activity log will appear here once you start adding or editing expenses.</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['action_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php
                        $pastTense = [
                            'CREATE' => '<span class="badge badge-success">Created</span>',
                            'UPDATE' => '<span class="badge badge-warning">Updated</span>',
                            'DELETE' => '<span class="badge badge-danger">Deleted</span>',
                            'MANUAL' => '<span class="badge badge-secondary">Note Added</span>',
                        ];
                        $actionType = $log['action_type'] ?? 'MANUAL';
                        echo $pastTense[$actionType] ?? '<span class="badge badge-secondary">' . htmlspecialchars($actionType, ENT_QUOTES, 'UTF-8') . '</span>';
                    ?>
                </td>
                <td>
                    <?php
                        $oldVal     = $log['old_value'] ?? '';
                        $newVal     = $log['new_value']  ?? '';
                        $oldData    = json_decode($oldVal, true);
                        $newData    = json_decode($newVal, true);
                        $actionType = $log['action_type'] ?? '';

                        // Resolve category name from the map using either stored name or ID.
                        $resolveCat = function(array $data) use ($categoryMap): string {
                            if (!empty($data['category_name'])) {
                                return htmlspecialchars($data['category_name'], ENT_QUOTES, 'UTF-8');
                            }
                            $cid = (int)($data['category_id'] ?? 0);
                            return $cid && isset($categoryMap[$cid])
                                ? htmlspecialchars($categoryMap[$cid], ENT_QUOTES, 'UTF-8')
                                : '—';
                        };

                        if ($actionType === 'UPDATE' && is_array($oldData) && is_array($newData)) {
                            // Inline before → after: amount, category, description
                            $oldAmt  = is_numeric($oldData['amount'] ?? null) ? '£' . number_format((float)$oldData['amount'], 2) : '—';
                            $newAmt  = is_numeric($newData['amount'] ?? null) ? '£' . number_format((float)$newData['amount'], 2) : '—';
                            $cat     = $resolveCat($newData);
                            $desc    = htmlspecialchars($newData['description'] ?? $oldData['description'] ?? '', ENT_QUOTES, 'UTF-8');
                            echo "<span class=\"audit-before\">$oldAmt</span>"
                               . " <span class=\"audit-arrow\">&#8594;</span> "
                               . "<span class=\"audit-after\">$newAmt</span>";
                            if ($cat !== '—') echo "<br><small>Category: <strong>$cat</strong></small>";
                            if ($desc !== '')  echo "<br><small>$desc</small>";

                        } elseif (is_array($oldData) && isset($oldData['amount'])) {
                            // CREATE / DELETE rows with expense data
                            $amt  = '£' . number_format((float)$oldData['amount'], 2);
                            $date = htmlspecialchars($oldData['expense_date'] ?? '', ENT_QUOTES, 'UTF-8');
                            $cat  = $resolveCat($oldData);
                            $desc = htmlspecialchars($oldData['description'] ?? '', ENT_QUOTES, 'UTF-8');
                            echo "<strong>$amt</strong>" . ($date ? " on $date" : '');
                            if ($cat !== '—') echo "<br><small>Category: <strong>$cat</strong></small>";
                            if ($desc !== '')  echo "<br><small>$desc</small>";

                        } elseif (is_array($oldData) && isset($oldData['category_name'])) {
                            // Category action
                            echo 'Category: <strong>' . htmlspecialchars($oldData['category_name'], ENT_QUOTES, 'UTF-8') . '</strong>';

                        } elseif ($actionType === 'MANUAL') {
                            // Standalone manual note
                            $noteText = is_array($oldData) && isset($oldData['note'])
                                ? htmlspecialchars($oldData['note'], ENT_QUOTES, 'UTF-8')
                                : htmlspecialchars($oldVal === '' ? '—' : $oldVal, ENT_QUOTES, 'UTF-8');
                            echo '<span class="audit-manual-note">&#9998;&nbsp;<em>' . $noteText . '</em></span>';

                        } else {
                            // Fallback for unknown formats
                            echo htmlspecialchars($oldVal === '' ? '—' : $oldVal, ENT_QUOTES, 'UTF-8');
                        }

                        // Show any note attached to this entry (stored in new_value under 'note' key).
                        // Applies to CREATE / DELETE / UPDATE / category rows — not MANUAL (they are notes).
                        if ($actionType !== 'MANUAL') {
                            $attachedNote = is_array($newData) && isset($newData['note'])
                                ? htmlspecialchars($newData['note'], ENT_QUOTES, 'UTF-8')
                                : '';
                            if ($attachedNote !== '') {
                                echo '<br><span class="audit-manual-note">&#9998;&nbsp;<em>' . $attachedNote . '</em></span>';
                            }
                        }
                    ?>
                </td>
                <td>
                    <?= (int)$log['is_reviewed'] === 1 ? '<span class="badge badge-success">Reviewed</span>' : '<span class="badge badge-warning">Pending</span>' ?>
                </td>
                <td>
                    <div class="actions-cell">
                        <?php
                            $rowAction  = $log['action_type'] ?? '';
                            $rowNewData = json_decode($log['new_value'] ?? '', true);
                            $hasNote    = is_array($rowNewData) && isset($rowNewData['note']);
                            $showNote   = ($rowAction !== 'MANUAL');
                            $showAck    = ((int)$log['is_reviewed'] === 0);
                        ?>

                        <?php if ($showNote): ?>
                        <a href="/smartspend/history/add.php?log_id=<?= $log['log_id'] ?>"
                           class="btn-secondary btn-sm"
                           title="<?= $hasNote ? 'Edit note for this entry' : 'Add a note to this entry' ?>">
                            &#9998; <?= $hasNote ? 'Edit Note' : 'Note' ?>
                        </a>
                        <?php endif; ?>

                        <?php if ($showNote && $showAck): ?>
                        <span class="action-divider">|</span>
                        <?php endif; ?>

                        <?php if ($showAck): ?>
                        <form method="POST" action="/smartspend/history/edit.php" style="display:inline;">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-warning btn-sm">Acknowledge</button>
                        </form>
                        <?php endif; ?>

                        <span class="action-divider">|</span>

                        <form method="POST" action="/smartspend/history/delete.php" style="display:inline;">
                            <input type="hidden" name="log_id" value="<?= $log['log_id'] ?>">
                            <button type="submit" class="btn-danger btn-sm" onclick="return confirm('Delete this log entry?')">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
