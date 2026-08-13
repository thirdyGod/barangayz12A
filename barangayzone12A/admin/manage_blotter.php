<?php
/**
 * Barangay Blotter Log Admin Manager - Barangay Zone 12-A
 */
$active_tab  = 'blotter';
$admin_title = 'Blotter Records';

require_once 'includes/admin_header.php';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blotter_records` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `case_no` VARCHAR(50) NOT NULL UNIQUE,
        `date_filed` DATE NOT NULL,
        `incident_type` VARCHAR(100) NOT NULL,
        `complainant` VARCHAR(255) NOT NULL,
        `respondent` VARCHAR(255) DEFAULT NULL,
        `incident_location` VARCHAR(255) DEFAULT NULL,
        `narrative` TEXT NOT NULL,
        `status` ENUM('Open','Under Mediation','Resolved','Referred to Higher Authority') DEFAULT 'Open',
        `date_resolved` DATE DEFAULT NULL,
        `remarks` TEXT DEFAULT NULL,
        `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error   = '';

// DELETE
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM blotter_records WHERE id = :id")->execute(['id' => $id]);
        $message = "Blotter record deleted.";
        $action  = 'list';
    } catch (PDOException $e) { $error = "Delete failed."; $action = 'list'; }
}

// ADD / EDIT POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add','edit'])) {
    $case_no      = strtoupper(trim(filter_input(INPUT_POST, 'case_no',           FILTER_SANITIZE_SPECIAL_CHARS)));
    $date_filed   = trim(filter_input(INPUT_POST, 'date_filed',                   FILTER_SANITIZE_SPECIAL_CHARS));
    $incident_type= trim(filter_input(INPUT_POST, 'incident_type',                FILTER_SANITIZE_SPECIAL_CHARS));
    $complainant  = trim(filter_input(INPUT_POST, 'complainant',                  FILTER_SANITIZE_SPECIAL_CHARS));
    $respondent   = trim(filter_input(INPUT_POST, 'respondent',                   FILTER_SANITIZE_SPECIAL_CHARS));
    $inc_location = trim(filter_input(INPUT_POST, 'incident_location',            FILTER_SANITIZE_SPECIAL_CHARS));
    $narrative    = trim(filter_input(INPUT_POST, 'narrative',                    FILTER_SANITIZE_SPECIAL_CHARS));
    $status       = trim(filter_input(INPUT_POST, 'status',                       FILTER_SANITIZE_SPECIAL_CHARS));
    $date_resolved= trim(filter_input(INPUT_POST, 'date_resolved',                FILTER_SANITIZE_SPECIAL_CHARS));
    $remarks      = trim(filter_input(INPUT_POST, 'remarks',                      FILTER_SANITIZE_SPECIAL_CHARS));

    if (empty($case_no) || empty($date_filed) || empty($incident_type) || empty($complainant) || empty($narrative)) {
        $error = "Case No., Date Filed, Incident Type, Complainant, and Narrative are required.";
    } else {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO blotter_records (case_no,date_filed,incident_type,complainant,respondent,incident_location,narrative,status,date_resolved,remarks)
                    VALUES (:cn,:df,:it,:comp,:resp,:loc,:narr,:stat,:dr,:rem)");
            } else {
                $stmt = $pdo->prepare("UPDATE blotter_records SET case_no=:cn,date_filed=:df,incident_type=:it,complainant=:comp,respondent=:resp,incident_location=:loc,narrative=:narr,status=:stat,date_resolved=:dr,remarks=:rem WHERE id=:id");
            }
            $params = [
                'cn'=>$case_no,'df'=>$date_filed,'it'=>$incident_type,'comp'=>$complainant,
                'resp'=>$respondent?:null,'loc'=>$inc_location?:null,'narr'=>$narrative,
                'stat'=>$status,'dr'=>$date_resolved?:null,'rem'=>$remarks?:null
            ];
            if ($action === 'edit') $params['id'] = $id;
            $stmt->execute($params);
            $message = $action === 'add' ? "Blotter entry logged." : "Blotter entry updated.";
            $action  = 'list';
        } catch (PDOException $e) {
            $error = str_contains($e->getMessage(), 'Duplicate') ? "Case number already exists." : "DB error: " . $e->getMessage();
        }
    }
}

// Fetch for edit
$rec = null;
if ($action === 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blotter_records WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        $rec = $stmt->fetch();
        if (!$rec) { $error = "Record not found."; $action = 'list'; }
    } catch (PDOException $e) { $error = "Fetch failed."; $action = 'list'; }
}

// View single record
$view_rec = null;
if ($action === 'view' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blotter_records WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        $view_rec = $stmt->fetch();
        if (!$view_rec) { $error = "Record not found."; $action = 'list'; }
    } catch (PDOException $e) { $error = "Fetch failed."; $action = 'list'; }
}

$incident_types = ['Physical Injury','Oral Defamation','Trespassing','Threat','Unjust Vexation','Dispute (Land)','Dispute (Family)','Noise Complaint','Property Damage','Theft','Other'];
$statuses = ['Open','Under Mediation','Resolved','Referred to Higher Authority'];

// Generate next case number suggestion
$next_case = '';
try {
    $yr = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM blotter_records WHERE YEAR(date_filed)=$yr");
    $count = (int)$stmt->fetchColumn() + 1;
    $next_case = 'BLT-' . $yr . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
} catch (PDOException $e) { $next_case = 'BLT-' . date('Y') . '-0001'; }
?>

<?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<?php if ($action === 'view' && $view_rec): ?>
    <!-- VIEW SINGLE RECORD -->
    <div style="margin-bottom:1.5rem;display:flex;gap:0.75rem;">
        <a href="manage_blotter.php" class="btn-action btn-edit">← Back to List</a>
        <a href="manage_blotter.php?action=edit&id=<?php echo $view_rec['id']; ?>" class="btn-action btn-add"><i class="bi bi-pencil-square"></i> Edit</a>
    </div>
    <?php
    $sc = ['Open'=>['#fee2e2','#ef4444'],'Under Mediation'=>['#fef3c7','#d97706'],'Resolved'=>['#d1fae5','#059669'],'Referred to Higher Authority'=>['#dbeafe','#2563eb']];
    [$sbg,$sfg] = $sc[$view_rec['status']] ?? ['#e2e8f0','#475569'];
    ?>
    <div class="form-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <h3 class="form-title" style="margin-bottom:0.25rem;">Blotter Entry</h3>
                <div style="font-size:1.15rem;font-weight:800;color:var(--color-primary);letter-spacing:0.06em;"><?php echo htmlspecialchars($view_rec['case_no']); ?></div>
            </div>
            <span style="background:<?php echo $sbg;?>;color:<?php echo $sfg;?>;padding:0.35rem 1rem;border-radius:999px;font-weight:700;font-size:0.85rem;height:fit-content;"><?php echo htmlspecialchars($view_rec['status']); ?></span>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:0.92rem;" class="blotter-view-table">
            <tr><th>Date Filed</th><td><?php echo date('F d, Y', strtotime($view_rec['date_filed'])); ?></td></tr>
            <tr><th>Incident Type</th><td><?php echo htmlspecialchars($view_rec['incident_type']); ?></td></tr>
            <tr><th>Complainant</th><td><?php echo htmlspecialchars($view_rec['complainant']); ?></td></tr>
            <tr><th>Respondent</th><td><?php echo htmlspecialchars($view_rec['respondent'] ?? '—'); ?></td></tr>
            <tr><th>Location</th><td><?php echo htmlspecialchars($view_rec['incident_location'] ?? '—'); ?></td></tr>
            <tr><th>Narrative</th><td style="white-space:pre-wrap;"><?php echo htmlspecialchars($view_rec['narrative']); ?></td></tr>
            <?php if ($view_rec['date_resolved']): ?>
            <tr><th>Date Resolved</th><td><?php echo date('F d, Y', strtotime($view_rec['date_resolved'])); ?></td></tr>
            <?php endif; ?>
            <?php if ($view_rec['remarks']): ?>
            <tr><th>Remarks</th><td style="white-space:pre-wrap;"><?php echo htmlspecialchars($view_rec['remarks']); ?></td></tr>
            <?php endif; ?>
            <tr><th>Date Logged</th><td style="color:var(--color-text-muted);font-size:0.82rem;"><?php echo date('M d, Y h:i A', strtotime($view_rec['date_created'])); ?></td></tr>
        </table>
    </div>
    <style>
    .blotter-view-table th, .blotter-view-table td { padding: 0.65rem 0.75rem; border-bottom: 1px solid var(--color-border); text-align:left; vertical-align:top; }
    .blotter-view-table th { width:30%; font-weight:700; color:var(--color-text-muted); font-size:0.82rem; text-transform:uppercase; letter-spacing:0.05em; }
    </style>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- ADD / EDIT FORM -->
    <div style="margin-bottom:1.5rem;"><a href="manage_blotter.php" class="btn-action btn-edit">← Back to List</a></div>
    <div class="form-card">
        <h3 class="form-title"><?php echo $action === 'add' ? 'Log New Blotter Entry' : 'Edit Blotter Entry'; ?></h3>
        <form action="manage_blotter.php?action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Case Number</label>
                    <input type="text" name="case_no" class="form-control" value="<?php echo htmlspecialchars($rec['case_no'] ?? $next_case); ?>" placeholder="e.g. BLT-2026-0001" style="text-transform:uppercase;" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Filed</label>
                    <input type="date" name="date_filed" class="form-control" value="<?php echo htmlspecialchars($rec['date_filed'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Incident Type</label>
                    <select name="incident_type" class="form-control" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($incident_types as $it): ?>
                            <option value="<?php echo $it; ?>" <?php echo ($rec['incident_type'] ?? '') === $it ? 'selected' : ''; ?>><?php echo $it; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo ($rec['status'] ?? 'Open') === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Complainant</label>
                    <input type="text" name="complainant" class="form-control" value="<?php echo htmlspecialchars($rec['complainant'] ?? ''); ?>" placeholder="Full name of complainant" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Respondent <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
                    <input type="text" name="respondent" class="form-control" value="<?php echo htmlspecialchars($rec['respondent'] ?? ''); ?>" placeholder="Full name of respondent">
                </div>
                <div class="form-group form-grid-full">
                    <label class="form-label">Incident Location <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
                    <input type="text" name="incident_location" class="form-control" value="<?php echo htmlspecialchars($rec['incident_location'] ?? ''); ?>" placeholder="e.g. Purok 3, Zone 12-A">
                </div>
                <div class="form-group form-grid-full">
                    <label class="form-label">Narrative / Incident Summary</label>
                    <textarea name="narrative" class="form-control" style="min-height:130px;" placeholder="Briefly describe what happened..." required><?php echo htmlspecialchars($rec['narrative'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Resolved <span style="font-weight:400;color:var(--color-text-muted)">(if applicable)</span></label>
                    <input type="date" name="date_resolved" class="form-control" value="<?php echo htmlspecialchars($rec['date_resolved'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Remarks <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
                    <textarea name="remarks" class="form-control" style="min-height:70px;" placeholder="Additional notes or follow-up..."><?php echo htmlspecialchars($rec['remarks'] ?? ''); ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn-action btn-add" style="margin-top:1.5rem;font-size:0.95rem;width:100%;justify-content:center;border:none;">
                Save Blotter Entry
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW with search/filter -->
    <?php
    $filter_status = $_GET['status'] ?? '';
    $search = trim($_GET['q'] ?? '');
    try {
        $where = [];
        $params = [];
        if ($filter_status) { $where[] = "status = :status"; $params['status'] = $filter_status; }
        if ($search) { $where[] = "(case_no LIKE :q OR complainant LIKE :q OR respondent LIKE :q OR incident_type LIKE :q)"; $params['q'] = "%$search%"; }
        $sql = "SELECT * FROM blotter_records" . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY date_filed DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $list = $stmt->fetchAll();
    } catch (PDOException $e) { $list = []; }
    ?>
    <div class="table-card">
        <div class="table-header">
            <h3 style="color:var(--color-primary);">Blotter Records</h3>
            <a href="manage_blotter.php?action=add" class="btn-action btn-add">+ Log New Entry</a>
        </div>

        <!-- Filter bar -->
        <form method="GET" style="padding: 1rem 1.5rem; display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center; background:#f8fafc; border-bottom:1px solid var(--color-border);">
            <input type="hidden" name="action" value="list">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search case no., name, type..." class="form-control" style="flex:1;min-width:200px;max-width:320px;">
            <select name="status" class="form-control" style="width:200px;">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo $st; ?>" <?php echo $filter_status === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-action btn-edit" style="border:none;">Filter</button>
            <?php if ($search || $filter_status): ?>
                <a href="manage_blotter.php" class="btn-action" style="background:#e2e8f0;color:var(--color-text-dark);">Clear</a>
            <?php endif; ?>
        </form>

        <?php
        $sc = ['Open'=>['#fee2e2','#ef4444'],'Under Mediation'=>['#fef3c7','#d97706'],'Resolved'=>['#d1fae5','#059669'],'Referred to Higher Authority'=>['#dbeafe','#2563eb']];
        ?>
        <?php if (!empty($list)): ?>
            <table class="admin-table">
                <thead><tr><th>Case No.</th><th>Date Filed</th><th>Type</th><th>Complainant</th><th>Respondent</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($list as $row):
                    [$sbg,$sfg] = $sc[$row['status']] ?? ['#e2e8f0','#475569'];
                ?>
                    <tr>
                        <td><strong style="font-family:monospace;"><?php echo htmlspecialchars($row['case_no']); ?></strong></td>
                        <td style="font-size:0.85rem;"><?php echo date('M d, Y', strtotime($row['date_filed'])); ?></td>
                        <td><?php echo htmlspecialchars($row['incident_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['complainant']); ?></td>
                        <td style="color:var(--color-text-muted);font-size:0.85rem;"><?php echo htmlspecialchars($row['respondent'] ?? '—'); ?></td>
                        <td><span style="background:<?php echo $sbg;?>;color:<?php echo $sfg;?>;padding:0.2rem 0.6rem;border-radius:999px;font-weight:700;font-size:0.75rem;"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td style="text-align:right;">
                            <a href="manage_blotter.php?action=view&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-eye"></i> View</a>
                            <a href="manage_blotter.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="manage_blotter.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Permanently delete this blotter entry?');"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:3rem;text-align:center;" class="text-muted">No blotter entries found.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
