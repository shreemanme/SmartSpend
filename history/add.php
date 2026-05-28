<?php
/**
 * Page:      history/add.php
 * Component: Audit Log — Attach / Edit a note on a specific log entry
 * Developer: Bibek Timsina (Audit & History Log)
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /smartspend/index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
$uid = (int)$_SESSION['user_id'];

// Resolve log_id from POST (submit) or GET (page load).
$logId = (int)(($_POST['log_id'] ?? $_GET['log_id'] ?? 0));

if ($logId <= 0) {
    header('Location: /smartspend/history/index.php');
    exit;
}

// Fetch the target log entry — must belong to this user and must not be MANUAL.
$stmt = $pdo->prepare('SELECT * FROM tblAuditLog WHERE log_id = ? AND user_id = ?');
$stmt->execute([$logId, $uid]);
$entry = $stmt->fetch();

if (!$entry || $entry['action_type'] === 'MANUAL') {
    header('Location: /smartspend/history/index.php');
    exit;
}

// ── POST: save the note ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = trim($_POST['note'] ?? '');

    if ($note === '') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Note cannot be empty.'];
        header("Location: /smartspend/history/add.php?log_id=$logId");
        exit;
    }

    if (strlen($note) > 300) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Note must be 300 characters or fewer.'];
        header("Location: /smartspend/history/add.php?log_id=$logId");
        exit;
    }

    // Merge the note into the existing new_value JSON (preserving expense fields for UPDATE rows).
    $existing = json_decode($entry['new_value'] ?? '', true);
    if (!is_array($existing)) $existing = [];
    $existing['note'] = $note;

    $upd = $pdo->prepare('UPDATE tblAuditLog SET new_value = ? WHERE log_id = ? AND user_id = ?');
    $upd->execute([json_encode($existing), $logId, $uid]);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Note saved to log entry.'];
    header('Location: /smartspend/history/index.php');
    exit;
}

// ── GET: render the form ─────────────────────────────────────────────────────

// Pull any existing note so the textarea is pre-filled when editing.
$existingNewData = json_decode($entry['new_value'] ?? '', true);
$existingNote    = is_array($existingNewData) && isset($existingNewData['note'])
    ? $existingNewData['note']
    : '';

// Build a human-readable summary of what this entry is about.
$oldData    = json_decode($entry['old_value'] ?? '', true);
$actionType = $entry['action_type'] ?? '';
$actionDate = htmlspecialchars($entry['action_date'], ENT_QUOTES, 'UTF-8');

$entryLabel = match ($actionType) {
    'CREATE' => 'Created expense',
    'UPDATE' => 'Updated expense',
    'DELETE' => 'Deleted expense',
    default  => htmlspecialchars($actionType, ENT_QUOTES, 'UTF-8'),
};
if (is_array($oldData) && isset($oldData['amount'])) {
    $entryLabel .= ' — £' . number_format((float)$oldData['amount'], 2);
}
if (is_array($oldData) && isset($oldData['description']) && $oldData['description'] !== '') {
    $entryLabel .= ', ' . htmlspecialchars($oldData['description'], ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><?= $existingNote !== '' ? 'Edit Note' : 'Add Note' ?></h1>
    <a href="/smartspend/history/index.php" class="btn-secondary">← Back</a>
</div>

<div class="form-card">

    <!-- Context strip: shows which log entry the note will be attached to -->
    <div style="background: var(--surface-alt, #f4f4f8); border-left: 3px solid var(--primary, #6c63ff);
                border-radius: 0 8px 8px 0; padding: 10px 14px; margin-bottom: 20px; font-size: 0.88rem;
                color: var(--text-muted, #666);">
        <strong>Attaching note to:</strong> <?= $entryLabel ?> &nbsp;·&nbsp; <?= $actionDate ?>
    </div>

    <p style="margin-bottom: 16px; color: var(--text-muted, #888); font-size: 0.9rem;">
        Your note will appear in the <strong>Details</strong> column of this log entry,
        below the category and description, marked with a ✎ pencil icon.
    </p>

    <form method="POST" action="">
        <input type="hidden" name="log_id" value="<?= $logId ?>">
        <div class="form-group">
            <label for="note">Note</label>
            <textarea id="note" name="note" rows="4" maxlength="300"
                      placeholder="e.g. Double-checked this expense against the receipt."
                      style="width: 100%; resize: vertical; font-family: inherit; font-size: 0.95rem;
                             padding: 10px 12px; border-radius: 8px;
                             border: 1px solid var(--border, #ccc);
                             background: var(--input-bg, #fff); color: var(--text, #111);"
                      required><?= htmlspecialchars($existingNote, ENT_QUOTES, 'UTF-8') ?></textarea>
            <div style="text-align: right; font-size: 0.78rem; color: var(--text-muted, #888); margin-top: 4px;">
                <span id="note-count"><?= strlen($existingNote) ?></span> / 300 characters
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Note</button>
        </div>
    </form>
</div>

<script>
    const noteArea  = document.getElementById('note');
    const noteCount = document.getElementById('note-count');
    noteArea.addEventListener('input', () => {
        noteCount.textContent = noteArea.value.length;
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
