<?php
/**
 * Document Requests Admin Manager - Barangay Zone 12-A
 */
$active_tab  = 'requests';
$admin_title = 'Document Requests';

require_once 'includes/admin_header.php';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `document_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `reference_no` VARCHAR(20) NOT NULL UNIQUE,
        `document_type` VARCHAR(100) NOT NULL,
        `requester_name` VARCHAR(255) NOT NULL,
        `requester_address` TEXT NOT NULL,
        `requester_contact` VARCHAR(50) DEFAULT NULL,
        `purpose` TEXT NOT NULL,
        `status` ENUM('Pending','Processing','Ready for Pickup','Released','Cancelled') DEFAULT 'Pending',
        `admin_notes` TEXT DEFAULT NULL,
        `date_requested` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error   = '';

$statuses = ['Pending','Processing','Ready for Pickup','Released','Cancelled'];

// DELETE
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM document_requests WHERE id=:id")->execute(['id'=>$id]);
        $message = "Request deleted.";
        $action  = 'list';
    } catch (PDOException $e) { $error = "Delete failed."; $action = 'list'; }
}

// UPDATE STATUS via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update' && $id) {
    $new_status = trim($_POST['status'] ?? '');
    $admin_notes= trim($_POST['admin_notes'] ?? '');
    if (!in_array($new_status, $statuses)) {
        $error = "Invalid status.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE document_requests SET status=:s, admin_notes=:n WHERE id=:id");
            $stmt->execute(['s'=>$new_status,'n'=>$admin_notes?:null,'id'=>$id]);
            $message = "Request status updated to \"$new_status\".";
            $action  = 'list';
        } catch (PDOException $e) { $error = "Update failed: " . $e->getMessage(); }
    }
}

// Fetch single for update view
$req = null;
if ($action === 'update' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM document_requests WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        $req = $stmt->fetch();
        if (!$req) { $error = "Request not found."; $action = 'list'; }
    } catch (PDOException $e) { $error = "Fetch failed."; $action = 'list'; }
}

$status_colors = [
    'Pending'          => ['#fef3c7','#d97706'],
    'Processing'       => ['#dbeafe','#2563eb'],
    'Ready for Pickup' => ['#d1fae5','#059669'],
    'Released'         => ['#f0fdf4','#16a34a'],
    'Cancelled'        => ['#fee2e2','#ef4444'],
];
?>

<?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<?php if ($action === 'update' && $req): ?>
    <!-- UPDATE STATUS VIEW -->
    <div style="margin-bottom:1.5rem;"><a href="manage_requests.php" class="btn-action btn-edit">← Back to List</a></div>
    <?php [$sbg,$sfg] = $status_colors[$req['status']] ?? ['#e2e8f0','#475569']; ?>
    <div class="form-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <h3 class="form-title" style="margin-bottom:0.25rem;">Update Request</h3>
                <div style="font-size:1.1rem;font-weight:800;color:var(--color-primary);letter-spacing:0.08em;"><?php echo htmlspecialchars($req['reference_no']); ?></div>
            </div>
            <span style="background:<?php echo $sbg;?>;color:<?php echo $sfg;?>;padding:0.3rem 1rem;border-radius:999px;font-weight:700;font-size:0.85rem;"><?php echo htmlspecialchars($req['status']); ?></span>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:0.9rem;margin-bottom:1.75rem;">
            <tr><th style="width:30%;padding:0.5rem 0.25rem;color:var(--color-text-muted);font-weight:600;text-align:left;border-bottom:1px solid var(--color-border);">Document</th><td style="padding:0.5rem 0.25rem;border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($req['document_type']); ?></td></tr>
            <tr><th style="width:30%;padding:0.5rem 0.25rem;color:var(--color-text-muted);font-weight:600;text-align:left;border-bottom:1px solid var(--color-border);">Name</th><td style="padding:0.5rem 0.25rem;border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($req['requester_name']); ?></td></tr>
            <tr><th style="padding:0.5rem 0.25rem;color:var(--color-text-muted);font-weight:600;text-align:left;border-bottom:1px solid var(--color-border);">Address</th><td style="padding:0.5rem 0.25rem;border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($req['requester_address']); ?></td></tr>
            <tr><th style="padding:0.5rem 0.25rem;color:var(--color-text-muted);font-weight:600;text-align:left;border-bottom:1px solid var(--color-border);">Contact</th><td style="padding:0.5rem 0.25rem;border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($req['requester_contact'] ?? '—'); ?></td></tr>
            <tr><th style="padding:0.5rem 0.25rem;color:var(--color-text-muted);font-weight:600;text-align:left;border-bottom:1px solid var(--color-border);">Purpose</th><td style="padding:0.5rem 0.25rem;border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($req['purpose']); ?></td></tr>
            <tr><th style="padding:0.5rem 0.25rem;color:var(--color-text-muted);font-weight:600;text-align:left;">Date Filed</th><td style="padding:0.5rem 0.25rem;"><?php echo date('M d, Y g:i A', strtotime($req['date_requested'])); ?></td></tr>
        </table>

        <form method="POST" action="manage_requests.php?action=update&id=<?php echo $req['id']; ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Update Status</label>
                    <select name="status" class="form-control">
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $req['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Admin Notes <span style="font-weight:400;color:var(--color-text-muted)">(visible to requester)</span></label>
                    <textarea name="admin_notes" class="form-control" style="min-height:80px;" placeholder="e.g. Please bring a valid ID upon pickup."><?php echo htmlspecialchars($req['admin_notes'] ?? ''); ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn-action btn-add" style="margin-top:1.25rem;font-size:0.95rem;width:100%;justify-content:center;border:none;">
                <i class="bi bi-check-circle"></i> Save Status Update
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW -->
    <?php
    $filter_status = $_GET['status'] ?? '';
    $filter_doc    = $_GET['doc'] ?? '';
    $search        = trim($_GET['q'] ?? '');
    try {
        $where = []; $params = [];
        if ($filter_status) { $where[] = "status = :status"; $params['status'] = $filter_status; }
        if ($filter_doc)    { $where[] = "document_type = :doc"; $params['doc'] = $filter_doc; }
        if ($search)        { $where[] = "(reference_no LIKE :q OR requester_name LIKE :q)"; $params['q'] = "%$search%"; }
        $sql = "SELECT * FROM document_requests" . ($where ? " WHERE ".implode(' AND ',$where) : "") . " ORDER BY date_requested DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $list = $stmt->fetchAll();

        // Count by status for summary
        $counts = $pdo->query("SELECT status, COUNT(*) as c FROM document_requests GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) { $list = []; $counts = []; }

    $pending_count = $counts['Pending'] ?? 0;
    ?>

    <!-- Quick stat pills -->
    <div style="display:flex;flex-wrap:wrap;gap:0.6rem;margin-bottom:1.5rem;">
        <?php foreach ($statuses as $st):
            [$sbg,$sfg] = $status_colors[$st];
            $cnt = $counts[$st] ?? 0;
        ?>
            <a href="manage_requests.php?status=<?php echo urlencode($st); ?>" style="background:<?php echo $sbg;?>;color:<?php echo $sfg;?>;padding:0.35rem 1rem;border-radius:999px;font-weight:700;font-size:0.82rem;text-decoration:none;border:1.5px solid transparent;<?php echo $filter_status===$st?'border-color:'.$sfg.';':'' ?>">
                <?php echo $st; ?> (<?php echo $cnt; ?>)
            </a>
        <?php endforeach; ?>
        <?php if ($filter_status || $search || $filter_doc): ?>
            <a href="manage_requests.php" style="background:#e2e8f0;color:var(--color-text-muted);padding:0.35rem 0.85rem;border-radius:999px;font-size:0.82rem;text-decoration:none;font-weight:600;">✕ Clear</a>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 style="color:var(--color-primary);">Document Requests
                <?php if ($pending_count > 0): ?>
                    <span style="background:#fee2e2;color:#ef4444;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.75rem;font-weight:700;margin-left:0.5rem;"><?php echo $pending_count; ?> Pending</span>
                <?php endif; ?>
            </h3>
            <!-- Search -->
            <form method="GET" style="display:flex;gap:0.5rem;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name / ref no." class="form-control" style="width:220px;">
                <button type="submit" class="btn-action btn-edit" style="border:none;">Search</button>
            </form>
        </div>

        <?php if (!empty($list)): ?>
            <table class="admin-table">
                <thead><tr><th>Ref. No.</th><th>Document</th><th>Name</th><th>Date Filed</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($list as $row):
                    [$sbg,$sfg] = $status_colors[$row['status']] ?? ['#e2e8f0','#475569'];
                ?>
                    <tr>
                        <td><strong style="font-family:monospace;font-size:0.82rem;"><?php echo htmlspecialchars($row['reference_no']); ?></strong></td>
                        <td style="font-size:0.88rem;"><?php echo htmlspecialchars($row['document_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['requester_name']); ?></td>
                        <td style="font-size:0.82rem;color:var(--color-text-muted);"><?php echo date('M d, Y', strtotime($row['date_requested'])); ?></td>
                        <td><span style="background:<?php echo $sbg;?>;color:<?php echo $sfg;?>;padding:0.2rem 0.6rem;border-radius:999px;font-weight:700;font-size:0.75rem;"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td style="text-align:right;">
                            <a href="manage_requests.php?action=update&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Update</a>
                            <a href="manage_requests.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this request record?');"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:3rem;text-align:center;" class="text-muted">No document requests found.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
