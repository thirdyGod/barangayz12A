<?php
/**
 * Admin Panel Dashboard - Barangay Zone 12-A
 */
$active_tab = 'dashboard';
$admin_title = 'Control Panel Dashboard';

require_once 'includes/admin_header.php';

// Fetch quick statistic metrics
$count_officials         = 0;
$count_news              = 0;
$count_facilities        = 0;
$count_unread_messages   = 0;
$count_pending_requests  = 0;
$count_open_blotter      = 0;
$count_upcoming_events   = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM officials");
    $count_officials = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM news");
    $count_news = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM facilities");
    $count_facilities = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $count_unread_messages = $stmt->fetchColumn();

    // New feature counts
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM document_requests WHERE status = 'Pending'");
        $count_pending_requests = $stmt->fetchColumn();
    } catch (PDOException $e) {}

    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM blotter_records WHERE status = 'Open'");
        $count_open_blotter = $stmt->fetchColumn();
    } catch (PDOException $e) {}

    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()");
        $count_upcoming_events = $stmt->fetchColumn();
    } catch (PDOException $e) {}

} catch (PDOException $e) {
    // Quiet failure for stats in dashboard
}

// Fetch the 3 latest contact messages for quick view
$recent_messages = [];
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY date_sent DESC LIMIT 3");
    $recent_messages = $stmt->fetchAll();
} catch (PDOException $e) {
    // Quiet failure
}
?>

<!-- Statistics Overview -->
<div class="admin-stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-title">Barangay Officials</div>
            <div class="stat-value"><?php echo $count_officials; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-people"></i></div>
    </div>

    <div class="stat-card accent">
        <div>
            <div class="stat-title">News Announcements</div>
            <div class="stat-value"><?php echo $count_news; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-newspaper"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Public Facilities</div>
            <div class="stat-value"><?php echo $count_facilities; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-building"></i></div>
    </div>

    <div class="stat-card accent" style="border-left-color: var(--color-danger);">
        <div>
            <div class="stat-title">Unread Messages</div>
            <div class="stat-value" style="color: var(--color-danger);"><?php echo $count_unread_messages; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-envelope"></i></div>
    </div>

    <div class="stat-card accent" style="border-left-color: #d97706;">
        <div>
            <div class="stat-title">Pending Doc. Requests</div>
            <div class="stat-value" style="color:#d97706;"><?php echo $count_pending_requests; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-file-earmark-check"></i></div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Open Blotter Cases</div>
            <div class="stat-value"><?php echo $count_open_blotter; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
    </div>

    <div class="stat-card accent" style="border-left-color: #059669;">
        <div>
            <div class="stat-title">Upcoming Events</div>
            <div class="stat-value" style="color:#059669;"><?php echo $count_upcoming_events; ?></div>
        </div>
        <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
    </div>
</div>

<!-- Welcome Message -->
<div class="card" style="margin-bottom: 2rem; border-top: 4px solid var(--color-primary);">
    <h3 style="margin-bottom: 0.5rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h3>
    <p class="text-muted">Use this administration portal to keep officials directories, news updates, and municipal facilities up to date. You can also view feedback submitted by barangay residents in the contact forms.</p>
</div>

<!-- Recent Messages Preview -->
<div class="table-card">
    <div class="table-header">
        <h3 style="color: var(--color-primary);">Recent Feedback Messages</h3>
        <a href="contact_messages.php" class="btn-action btn-edit" style="font-size: 0.8rem; padding: 0.4rem 1rem;">View All Messages</a>
    </div>
    
    <?php if (!empty($recent_messages)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date Sent</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message Snippet</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_messages as $msg): ?>
                    <tr>
                        <td style="font-size: 0.85rem; color: var(--color-text-muted);">
                            <?php echo date("M d, Y h:i A", strtotime($msg['date_sent'])); ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td>
                            <?php 
                            $text = htmlspecialchars($msg['message']);
                            echo strlen($text) > 60 ? substr($text, 0, 57) . '...' : $text; 
                            ?>
                        </td>
                        <td>
                            <?php if ($msg['is_read'] == 0): ?>
                                <span class="badge badge-unread">Unread</span>
                            <?php else: ?>
                                <span class="badge badge-read">Read</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 2rem; text-align: center;" class="text-muted">
            No feedback messages have been received yet.
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/admin_footer.php';
?>
