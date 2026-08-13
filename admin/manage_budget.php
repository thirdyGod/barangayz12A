<?php
/**
 * Budget Transparency Admin Manager - Barangay Zone 12-A
 */
$active_tab  = 'budget';
$admin_title = 'Manage Budget Entries';

require_once 'includes/admin_header.php';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `budget_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `year` INT NOT NULL,
        `type` ENUM('income','expense') NOT NULL,
        `sector` VARCHAR(150) NOT NULL,
        `label` VARCHAR(255) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error   = '';

// DELETE
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM budget_entries WHERE id=:id")->execute(['id'=>$id]);
        $message = "Budget entry deleted.";
        $action  = 'list';
    } catch (PDOException $e) { $error = "Delete failed."; $action = 'list'; }
}

// ADD / EDIT POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add','edit'])) {
    $year   = filter_input(INPUT_POST, 'year',   FILTER_VALIDATE_INT);
    $type   = trim($_POST['type']   ?? '');
    $sector = trim(filter_input(INPUT_POST, 'sector', FILTER_SANITIZE_SPECIAL_CHARS));
    $label  = trim(filter_input(INPUT_POST, 'label',  FILTER_SANITIZE_SPECIAL_CHARS));
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if (!$year || !in_array($type,['income','expense']) || empty($sector) || empty($label) || $amount === false || $amount < 0) {
        $error = "All fields are required and amount must be a valid positive number.";
    } else {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO budget_entries (year,type,sector,label,amount) VALUES (:y,:t,:s,:l,:a)");
            } else {
                $stmt = $pdo->prepare("UPDATE budget_entries SET year=:y,type=:t,sector=:s,label=:l,amount=:a WHERE id=:id");
            }
            $params = ['y'=>$year,'t'=>$type,'s'=>$sector,'l'=>$label,'a'=>$amount];
            if ($action === 'edit') $params['id'] = $id;
            $stmt->execute($params);
            $message = $action === 'add' ? "Budget entry added." : "Budget entry updated.";
            $action  = 'list';
        } catch (PDOException $e) { $error = "DB error: " . $e->getMessage(); }
    }
}

// Fetch for edit
$entry = null;
if ($action === 'edit' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM budget_entries WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        $entry = $stmt->fetch();
        if (!$entry) { $error = "Entry not found."; $action = 'list'; }
    } catch (PDOException $e) { $error = "Fetch failed."; $action = 'list'; }
}

// Common sectors
$income_sectors  = ['Internal Revenue Allotment (IRA)','Local Taxes','Business Permits','Fees & Charges','Grants / Donations','Other Income'];
$expense_sectors = ['Personnel Services','Maintenance & Other Operating Expenses','Capital Outlay','Health & Sanitation','Social Services & Welfare','Peace & Order','Youth & Sports Development','Environmental Management','Infrastructure','Other Expenditures'];
?>

<?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div style="margin-bottom:1.5rem;"><a href="manage_budget.php" class="btn-action btn-edit">← Back to List</a></div>
    <div class="form-card">
        <h3 class="form-title"><?php echo $action === 'add' ? 'Add Budget Entry' : 'Edit Budget Entry'; ?></h3>
        <form action="manage_budget.php?action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Fiscal Year</label>
                    <input type="number" name="year" class="form-control" value="<?php echo htmlspecialchars($entry['year'] ?? date('Y')); ?>" min="2000" max="2100" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" id="type-select" class="form-control" onchange="updateSectors()" required>
                        <option value="income"  <?php echo ($entry['type'] ?? '') === 'income'  ? 'selected' : ''; ?>>Income</option>
                        <option value="expense" <?php echo ($entry['type'] ?? '') === 'expense' ? 'selected' : ''; ?>>Expenditure</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sector / Category</label>
                    <input type="text" name="sector" id="sector-input" class="form-control" list="sector-suggestions" value="<?php echo htmlspecialchars($entry['sector'] ?? ''); ?>" placeholder="Select or type a sector name" required>
                    <datalist id="sector-suggestions">
                        <?php foreach ($income_sectors as $s): ?><option value="<?php echo htmlspecialchars($s); ?>"><?php endforeach; ?>
                        <?php foreach ($expense_sectors as $s): ?><option value="<?php echo htmlspecialchars($s); ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Line Item Label</label>
                    <input type="text" name="label" class="form-control" value="<?php echo htmlspecialchars($entry['label'] ?? ''); ?>" placeholder="e.g. Share in IRA - National Government" required>
                </div>
                <div class="form-group form-grid-full">
                    <label class="form-label">Amount (₱)</label>
                    <input type="number" name="amount" class="form-control" value="<?php echo htmlspecialchars($entry['amount'] ?? ''); ?>" min="0" step="0.01" placeholder="e.g. 1250000.00" required>
                </div>
            </div>
            <button type="submit" class="btn-action btn-add" style="margin-top:1.5rem;font-size:0.95rem;width:100%;justify-content:center;border:none;">
                Save Entry
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- LIST VIEW grouped by year -->
    <?php
    $filter_year = isset($_GET['year']) ? (int)$_GET['year'] : null;
    try {
        $years_stmt = $pdo->query("SELECT DISTINCT year FROM budget_entries ORDER BY year DESC");
        $years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) { $years = []; }
    if (!$filter_year && !empty($years)) $filter_year = $years[0];

    try {
        if ($filter_year) {
            $stmt = $pdo->prepare("SELECT * FROM budget_entries WHERE year=:y ORDER BY type ASC, sector ASC, label ASC");
            $stmt->execute(['y'=>$filter_year]);
        } else {
            $stmt = $pdo->query("SELECT * FROM budget_entries ORDER BY year DESC, type ASC, sector ASC");
        }
        $list = $stmt->fetchAll();
    } catch (PDOException $e) { $list = []; }

    $total_income = array_sum(array_column(array_filter($list, fn($r)=>$r['type']==='income'), 'amount'));
    $total_expense= array_sum(array_column(array_filter($list, fn($r)=>$r['type']==='expense'), 'amount'));
    ?>
    <div class="table-card">
        <div class="table-header">
            <h3 style="color:var(--color-primary);">Budget Entries</h3>
            <a href="manage_budget.php?action=add" class="btn-action btn-add">+ Add Entry</a>
        </div>

        <!-- Year tabs -->
        <?php if (!empty($years)): ?>
        <div style="padding:0.75rem 1.5rem;display:flex;flex-wrap:wrap;gap:0.5rem;background:#f8fafc;border-bottom:1px solid var(--color-border);align-items:center;">
            <span style="font-size:0.82rem;font-weight:600;color:var(--color-text-muted);">Year:</span>
            <?php foreach ($years as $yr): ?>
                <a href="manage_budget.php?year=<?php echo $yr;?>" style="padding:0.25rem 0.85rem;border-radius:999px;font-size:0.82rem;font-weight:700;text-decoration:none;<?php echo $yr==$filter_year ? 'background:var(--color-primary);color:#fff;' : 'background:#e2e8f0;color:var(--color-text-dark);'; ?>"><?php echo $yr; ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($list)): ?>
            <!-- Summary row -->
            <div style="display:flex;gap:1.5rem;padding:1rem 1.5rem;background:#fff;border-bottom:1px solid var(--color-border);flex-wrap:wrap;">
                <span style="font-size:0.88rem;">Total Income: <strong style="color:#15803d;">₱<?php echo number_format($total_income,2); ?></strong></span>
                <span style="font-size:0.88rem;">Total Expenditure: <strong style="color:#c2410c;">₱<?php echo number_format($total_expense,2); ?></strong></span>
                <span style="font-size:0.88rem;">Balance: <strong style="color:var(--color-primary);">₱<?php echo number_format(abs($total_income-$total_expense),2); ?> <?php echo $total_income >= $total_expense ? 'surplus' : 'deficit'; ?></strong></span>
            </div>
            <table class="admin-table">
                <thead><tr><th>Type</th><th>Sector</th><th>Label</th><th>Amount (₱)</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($list as $row): ?>
                    <tr>
                        <td>
                            <?php if ($row['type']==='income'): ?>
                                <span class="badge" style="background:#d1fae5;color:#059669;">Income</span>
                            <?php else: ?>
                                <span class="badge" style="background:#fff7ed;color:#c2410c;">Expense</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.85rem;color:var(--color-text-muted);"><?php echo htmlspecialchars($row['sector']); ?></td>
                        <td><?php echo htmlspecialchars($row['label']); ?></td>
                        <td style="font-weight:700;">₱<?php echo number_format($row['amount'],2); ?></td>
                        <td style="text-align:right;">
                            <a href="manage_budget.php?action=edit&id=<?php echo $row['id']; ?>" class="btn-action btn-edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="manage_budget.php?action=delete&id=<?php echo $row['id']; ?><?php echo $filter_year ? '&year='.$filter_year : ''; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this entry?');"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding:3rem;text-align:center;" class="text-muted">No budget entries yet. Click "+ Add Entry" to start building the budget disclosure.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/admin_footer.php'; ?>
