<?php
/**
 * Document Request Form & Status Checker - Barangay Zone 12-A
 */
require_once 'config.php';

$page_title  = 'Document Request';
$active_page = 'request';

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

$message = '';
$error   = '';
$status_result = null;
$new_reference = null;

// --- STATUS CHECK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_status'])) {
    $ref = strtoupper(trim($_POST['reference_no'] ?? ''));
    if (empty($ref)) {
        $error = "Please enter a reference number.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM document_requests WHERE reference_no = :ref");
            $stmt->execute(['ref' => $ref]);
            $status_result = $stmt->fetch();
            if (!$status_result) {
                $error = "No request found with reference number <strong>" . htmlspecialchars($ref) . "</strong>. Please check and try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error. Please try again later.";
        }
    }
}

// --- NEW REQUEST SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $doc_type   = trim($_POST['document_type'] ?? '');
    $req_name   = trim($_POST['requester_name'] ?? '');
    $req_addr   = trim($_POST['requester_address'] ?? '');
    $req_contact= trim($_POST['requester_contact'] ?? '');
    $purpose    = trim($_POST['purpose'] ?? '');

    if (empty($doc_type) || empty($req_name) || empty($req_addr) || empty($purpose)) {
        $error = "Document type, full name, address, and purpose are all required.";
    } else {
        // Generate a unique reference number: BRY-YYYYMMDD-XXXX
        $ref_no = 'BRY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        try {
            $stmt = $pdo->prepare("INSERT INTO document_requests
                (reference_no, document_type, requester_name, requester_address, requester_contact, purpose)
                VALUES (:ref, :doc, :name, :addr, :contact, :purpose)");
            $stmt->execute([
                'ref'     => $ref_no,
                'doc'     => $doc_type,
                'name'    => $req_name,
                'addr'    => $req_addr,
                'contact' => empty($req_contact) ? null : $req_contact,
                'purpose' => $purpose,
            ]);
            $new_reference = $ref_no;
            $message = "Your request has been submitted successfully.";
        } catch (PDOException $e) {
            $error = "Could not submit request. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Document <span>Request</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Request barangay documents online and track your request status using your reference number.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if ($new_reference): ?>
            <!-- SUCCESS — show reference number prominently -->
            <div class="req-success-card">
                <i class="bi bi-check-circle-fill"></i>
                <h2>Request Submitted!</h2>
                <p>Your document request has been received. Please save your reference number below and bring it when you pick up your document.</p>
                <div class="ref-display"><?php echo htmlspecialchars($new_reference); ?></div>
                <p class="text-muted" style="margin-top:0.75rem; font-size:0.9rem;">You can use this reference number on this page to check your request status at any time.</p>
                <a href="request.php" class="req-btn-outline" style="margin-top:1.25rem; display:inline-block;">Submit Another Request</a>
            </div>

        <?php else: ?>

        <div class="req-two-col">

            <!-- LEFT: Submit Form -->
            <div class="req-panel">
                <h2 class="req-panel-title"><i class="bi bi-file-earmark-plus"></i> Submit a New Request</h2>

                <?php if ($error && !isset($_POST['check_status'])): ?>
                    <div class="req-alert req-alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="req-form">
                    <div class="req-form-group">
                        <label for="document_type">Document Type</label>
                        <select name="document_type" id="document_type" required>
                            <option value="">-- Select Document --</option>
                            <option value="Barangay Clearance">Barangay Clearance</option>
                            <option value="Certificate of Residency">Certificate of Residency</option>
                            <option value="Certificate of Indigency">Certificate of Indigency</option>
                            <option value="Certificate of Good Moral Character">Certificate of Good Moral Character</option>
                            <option value="Business Clearance">Business Clearance</option>
                            <option value="Certificate of Cohabitation">Certificate of Cohabitation</option>
                            <option value="First Time Jobseeker Certification">First Time Jobseeker Certification</option>
                            <option value="Other">Other (Specify in Purpose)</option>
                        </select>
                    </div>
                    <div class="req-form-group">
                        <label for="requester_name">Full Name</label>
                        <input type="text" name="requester_name" id="requester_name" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="req-form-group">
                        <label for="requester_address">Home Address</label>
                        <input type="text" name="requester_address" id="requester_address" placeholder="Purok / Street, Zone 12-A, Talisay City" required>
                    </div>
                    <div class="req-form-group">
                        <label for="requester_contact">Contact Number <span style="color:var(--color-text-muted); font-weight:400;">(optional)</span></label>
                        <input type="text" name="requester_contact" id="requester_contact" placeholder="e.g. 09XX-XXX-XXXX">
                    </div>
                    <div class="req-form-group">
                        <label for="purpose">Purpose of Request</label>
                        <textarea name="purpose" id="purpose" rows="3" placeholder="e.g. For employment / scholarship application / loan requirement" required></textarea>
                    </div>
                    <button type="submit" name="submit_request" class="req-btn">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                </form>
            </div>

            <!-- RIGHT: Status Checker -->
            <div class="req-panel">
                <h2 class="req-panel-title"><i class="bi bi-search"></i> Check Request Status</h2>

                <?php if ($error && isset($_POST['check_status'])): ?>
                    <div class="req-alert req-alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="req-form">
                    <div class="req-form-group">
                        <label for="reference_no">Reference Number</label>
                        <input type="text" name="reference_no" id="reference_no" placeholder="e.g. BRY-20260812-A3F2" style="text-transform:uppercase;" value="<?php echo htmlspecialchars($_POST['reference_no'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="check_status" class="req-btn req-btn-secondary">
                        <i class="bi bi-arrow-right-circle"></i> Check Status
                    </button>
                </form>

                <?php if ($status_result): ?>
                    <?php
                    $s = $status_result['status'];
                    $status_colors = [
                        'Pending'                    => ['#fef3c7','#d97706'],
                        'Processing'                 => ['#dbeafe','#2563eb'],
                        'Ready for Pickup'           => ['#d1fae5','#059669'],
                        'Released'                   => ['#f0fdf4','#16a34a'],
                        'Cancelled'                  => ['#fee2e2','#ef4444'],
                    ];
                    [$bg,$fg] = $status_colors[$s] ?? ['#e2e8f0','#475569'];
                    ?>
                    <div class="req-status-card" style="margin-top:1.5rem;">
                        <div class="req-status-ref"><?php echo htmlspecialchars($status_result['reference_no']); ?></div>
                        <table class="req-status-table">
                            <tr><th>Document</th><td><?php echo htmlspecialchars($status_result['document_type']); ?></td></tr>
                            <tr><th>Name</th><td><?php echo htmlspecialchars($status_result['requester_name']); ?></td></tr>
                            <tr><th>Date Filed</th><td><?php echo date('M d, Y g:i A', strtotime($status_result['date_requested'])); ?></td></tr>
                            <tr><th>Last Updated</th><td><?php echo date('M d, Y g:i A', strtotime($status_result['date_updated'])); ?></td></tr>
                            <tr>
                                <th>Status</th>
                                <td><span style="background:<?php echo $bg;?>;color:<?php echo $fg;?>;padding:0.2rem 0.7rem;border-radius:999px;font-weight:700;font-size:0.85rem;"><?php echo htmlspecialchars($s); ?></span></td>
                            </tr>
                            <?php if ($status_result['admin_notes']): ?>
                            <tr><th>Notes</th><td><?php echo nl2br(htmlspecialchars($status_result['admin_notes'])); ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- .req-two-col -->

        <?php endif; ?>

    </div>
</section>

<style>
.req-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}
@media (max-width: 768px) {
    .req-two-col { grid-template-columns: 1fr; }
}

.req-panel {
    background: var(--color-card-bg);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    box-shadow: var(--shadow-md);
    padding: 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.req-panel:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}
.req-panel-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.req-form { display: flex; flex-direction: column; gap: 1rem; }
.req-form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.req-form-group label { font-size: 0.88rem; font-weight: 600; color: var(--color-text-dark); }
.req-form-group input,
.req-form-group select,
.req-form-group textarea {
    padding: 0.65rem 0.9rem;
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-family: var(--font-sans);
    font-size: 0.93rem;
    color: var(--color-text-dark);
    background: var(--color-bg);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.req-form-group input:hover,
.req-form-group select:hover,
.req-form-group textarea:hover {
    border-color: #cbd5e1;
    background-color: #ffffff;
}
.req-form-group input:focus,
.req-form-group select:focus,
.req-form-group textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    background-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(17, 46, 129, 0.12);
}

.req-btn {
    padding: 0.7rem 1.5rem;
    background: var(--color-primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-family: var(--font-sans);
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 10px rgba(17, 46, 129, 0.15);
}
.req-btn:hover {
    background: var(--color-primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(17, 46, 129, 0.25);
}
.req-btn-secondary {
    background: var(--color-text-dark);
    box-shadow: 0 4px 10px rgba(30, 41, 59, 0.15);
}
.req-btn-secondary:hover {
    background: #0f172a;
    box-shadow: 0 6px 15px rgba(15, 23, 42, 0.25);
}
.req-btn-outline {
    padding: 0.65rem 1.5rem;
    border: 2px solid var(--color-primary);
    color: var(--color-primary);
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 10px rgba(17, 46, 129, 0.05);
}
.req-btn-outline:hover {
    background: var(--color-primary);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(17, 46, 129, 0.2);
}

.req-alert {
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}
.req-alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

.req-success-card {
    text-align: center;
    background: var(--color-card-bg);
    border-radius: var(--radius-md);
    border: 1px solid #bbf7d0;
    box-shadow: var(--shadow-md);
    padding: 3rem 2rem;
    max-width: 520px;
    margin: 0 auto;
}
.req-success-card i { font-size: 3rem; color: #16a34a; display: block; margin-bottom: 1rem; }
.req-success-card h2 { font-size: 1.5rem; color: var(--color-primary); margin-bottom: 0.75rem; }
.req-success-card p { color: var(--color-text-muted); font-size: 0.95rem; }
.ref-display {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: var(--color-primary);
    background: #eff6ff;
    border: 2px dashed var(--color-primary);
    border-radius: var(--radius-sm);
    padding: 0.75rem 1.5rem;
    margin: 1rem auto;
    display: inline-block;
}

.req-status-card {
    background: #f8fafc;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    padding: 1.25rem;
}
.req-status-ref {
    font-size: 1rem;
    font-weight: 800;
    color: var(--color-primary);
    letter-spacing: 0.08em;
    margin-bottom: 0.85rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--color-border);
}
.req-status-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.req-status-table th,
.req-status-table td { padding: 0.45rem 0.25rem; text-align: left; vertical-align: top; }
.req-status-table th { width: 38%; color: var(--color-text-muted); font-weight: 600; }
.req-status-table td { color: var(--color-text-dark); font-weight: 500; }
</style>

<?php require_once 'includes/footer.php'; ?>
