<?php
/**
 * Budget Transparency Page - Barangay Zone 12-A
 * DILG Full Disclosure Policy compliance
 */
require_once 'config.php';

$page_title  = 'Budget Transparency';
$active_page = 'transparency';

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

// Get available years
try {
    $stmt = $pdo->query("SELECT DISTINCT year FROM budget_entries ORDER BY year DESC");
    $years = $stmt->fetchColumn() !== false ? $pdo->query("SELECT DISTINCT year FROM budget_entries ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (PDOException $e) { $years = []; }

$selected_year = isset($_GET['year']) && in_array((int)$_GET['year'], array_map('intval', $years))
    ? (int)$_GET['year']
    : ($years[0] ?? null);

$income_entries  = [];
$expense_entries = [];
$total_income    = 0;
$total_expense   = 0;

if ($selected_year) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM budget_entries WHERE year = :year ORDER BY type ASC, sector ASC, label ASC");
        $stmt->execute(['year' => $selected_year]);
        $all_entries = $stmt->fetchAll();
        foreach ($all_entries as $e) {
            if ($e['type'] === 'income') {
                $income_entries[$e['sector']][] = $e;
                $total_income += $e['amount'];
            } else {
                $expense_entries[$e['sector']][] = $e;
                $total_expense += $e['amount'];
            }
        }
    } catch (PDOException $e) {}
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Budget <span>Transparency</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Annual budget disclosure in compliance with the DILG Full Disclosure Policy.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if (empty($years)): ?>
            <div class="tp-empty">
                <i class="bi bi-bar-chart-line"></i>
                <p>No budget data has been published yet.</p>
            </div>
        <?php else: ?>

        <!-- Year Selector -->
        <div class="tp-year-bar">
            <span class="tp-year-label">Fiscal Year:</span>
            <?php foreach ($years as $yr): ?>
                <a href="?year=<?php echo $yr; ?>" class="tp-year-btn <?php echo $yr == $selected_year ? 'active' : ''; ?>">
                    <?php echo $yr; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Summary Cards -->
        <div class="tp-summary-grid">
            <div class="tp-summary-card income">
                <div class="tp-summary-label"><i class="bi bi-arrow-down-circle-fill"></i> Total Income</div>
                <div class="tp-summary-amount">₱<?php echo number_format($total_income, 2); ?></div>
            </div>
            <div class="tp-summary-card expense">
                <div class="tp-summary-label"><i class="bi bi-arrow-up-circle-fill"></i> Total Expenditure</div>
                <div class="tp-summary-amount">₱<?php echo number_format($total_expense, 2); ?></div>
            </div>
            <div class="tp-summary-card balance <?php echo ($total_income - $total_expense) >= 0 ? 'surplus' : 'deficit'; ?>">
                <div class="tp-summary-label"><i class="bi bi-wallet2"></i> Balance</div>
                <div class="tp-summary-amount">₱<?php echo number_format(abs($total_income - $total_expense), 2); ?>
                    <small><?php echo ($total_income - $total_expense) >= 0 ? 'Surplus' : 'Deficit'; ?></small>
                </div>
            </div>
        </div>

        <!-- Progress bar: spending rate -->
        <?php if ($total_income > 0): ?>
        <div class="tp-progress-wrap">
            <?php $pct = min(100, round(($total_expense / $total_income) * 100)); ?>
            <div style="display:flex;justify-content:space-between;font-size:0.82rem;color:var(--color-text-muted);margin-bottom:0.35rem;">
                <span>Budget Utilization</span>
                <span><?php echo $pct; ?>%</span>
            </div>
            <div class="tp-progress-bar">
                <div class="tp-progress-fill" style="width:<?php echo $pct; ?>%;"></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="tp-columns">
            <!-- Income -->
            <div class="tp-block">
                <div class="tp-block-header income-h">
                    <i class="bi bi-arrow-down-circle"></i> Income Sources
                </div>
                <?php if (!empty($income_entries)): ?>
                    <?php foreach ($income_entries as $sector => $rows): ?>
                        <div class="tp-sector-label"><?php echo htmlspecialchars($sector); ?></div>
                        <?php foreach ($rows as $r): ?>
                            <div class="tp-row">
                                <span><?php echo htmlspecialchars($r['label']); ?></span>
                                <span class="tp-amount">₱<?php echo number_format($r['amount'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <div class="tp-total income-total">
                        <span>Total Income</span>
                        <span>₱<?php echo number_format($total_income, 2); ?></span>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="padding:1rem;font-size:0.88rem;">No income entries recorded.</p>
                <?php endif; ?>
            </div>

            <!-- Expenditure -->
            <div class="tp-block">
                <div class="tp-block-header expense-h">
                    <i class="bi bi-arrow-up-circle"></i> Expenditures
                </div>
                <?php if (!empty($expense_entries)): ?>
                    <?php foreach ($expense_entries as $sector => $rows): ?>
                        <div class="tp-sector-label"><?php echo htmlspecialchars($sector); ?></div>
                        <?php foreach ($rows as $r): ?>
                            <div class="tp-row">
                                <span><?php echo htmlspecialchars($r['label']); ?></span>
                                <span class="tp-amount">₱<?php echo number_format($r['amount'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <div class="tp-total expense-total">
                        <span>Total Expenditure</span>
                        <span>₱<?php echo number_format($total_expense, 2); ?></span>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="padding:1rem;font-size:0.88rem;">No expenditure entries recorded.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; ?>
    </div>
</section>

<style>
.tp-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--color-text-muted);
    background: var(--color-card-bg);
    border-radius: var(--radius-md);
    border: 1px dashed var(--color-border);
}
.tp-empty i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.4; }

.tp-year-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
}
.tp-year-label { font-weight: 600; color: var(--color-text-muted); font-size: 0.88rem; margin-right: 0.25rem; }
.tp-year-btn {
    padding: 0.35rem 1rem;
    border-radius: 999px;
    border: 1.5px solid var(--color-border);
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--color-text-dark);
    text-decoration: none;
    transition: var(--transition);
}
.tp-year-btn:hover, .tp-year-btn.active {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}

.tp-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.75rem;
}
@media (max-width: 640px) { .tp-summary-grid { grid-template-columns: 1fr; } }

.tp-summary-card {
    border-radius: var(--radius-md);
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--color-border);
    box-shadow: var(--shadow-sm);
}
.tp-summary-card.income  { background: #f0fdf4; border-color: #bbf7d0; }
.tp-summary-card.expense { background: #fff7ed; border-color: #fed7aa; }
.tp-summary-card.surplus { background: #eff6ff; border-color: #bfdbfe; }
.tp-summary-card.deficit { background: #fff1f2; border-color: #fecdd3; }
.tp-summary-label { font-size: 0.82rem; font-weight: 700; color: var(--color-text-muted); margin-bottom: 0.5rem; display:flex;align-items:center;gap:0.4rem; }
.tp-summary-amount { font-size: 1.5rem; font-weight: 800; color: var(--color-text-dark); }
.tp-summary-amount small { font-size: 0.72rem; font-weight: 600; display:block; color: var(--color-text-muted); margin-top:2px; }

.tp-progress-wrap { margin-bottom: 2rem; }
.tp-progress-bar {
    height: 10px;
    background: var(--color-border);
    border-radius: 999px;
    overflow: hidden;
}
.tp-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color-primary), #3b82f6);
    border-radius: 999px;
    transition: width 0.6s ease;
}

.tp-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 768px) { .tp-columns { grid-template-columns: 1fr; } }

.tp-block {
    background: var(--color-card-bg);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.tp-block-header {
    padding: 0.9rem 1.25rem;
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.income-h  { background: #f0fdf4; color: #15803d; border-bottom: 1px solid #bbf7d0; }
.expense-h { background: #fff7ed; color: #c2410c; border-bottom: 1px solid #fed7aa; }

.tp-sector-label {
    padding: 0.5rem 1.25rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--color-text-muted);
    background: #f8fafc;
    border-bottom: 1px solid var(--color-border);
}
.tp-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.55rem 1.25rem;
    border-bottom: 1px solid var(--color-border);
    font-size: 0.88rem;
}
.tp-row:last-of-type { border-bottom: none; }
.tp-amount { font-weight: 600; color: var(--color-text-dark); white-space: nowrap; margin-left: 1rem; }

.tp-total {
    display: flex;
    justify-content: space-between;
    padding: 0.85rem 1.25rem;
    font-weight: 800;
    font-size: 0.92rem;
    border-top: 2px solid;
}
.income-total  { border-color: #86efac; background: #f0fdf4; color: #15803d; }
.expense-total { border-color: #fdba74; background: #fff7ed; color: #c2410c; }
</style>

<?php require_once 'includes/footer.php'; ?>
