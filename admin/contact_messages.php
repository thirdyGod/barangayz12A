<?php
/**
 * Contact Feedback Messages Listing - Barangay Zone 12-A
 */
$active_tab = 'messages';
$admin_title = 'User Feedback Messages';

require_once 'includes/admin_header.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$message = '';
$error = '';

// Toggle Read/Unread action
if ($action === 'toggle' && $id) {
    try {
        // SQL Toggle: 1 - is_read converts 0 to 1, and 1 to 0
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 - is_read WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = "Message read status updated successfully.";
    } catch (PDOException $e) {
        $error = "Failed to update read status: " . $e->getMessage();
    }
}

// Delete message action
if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = "Feedback message deleted successfully.";
    } catch (PDOException $e) {
        $error = "Failed to delete message record: " . $e->getMessage();
    }
}
?>

<!-- Alerts -->
<?php if ($message): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="table-card">
    <div class="table-header">
        <h3 style="color: var(--color-primary);">Inboxes & Submissions</h3>
        <span class="text-muted" style="font-size: 0.85rem;">Chronological feed of user queries</span>
    </div>
    
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY date_sent DESC");
        $messages_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        $messages_list = [];
    }
    ?>
    
    <?php if (!empty($messages_list)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date Sent</th>
                    <th>Sender Info</th>
                    <th>Email Address</th>
                    <th style="width: 45%;">Message Content</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages_list as $row): ?>
                    <tr style="<?php echo $row['is_read'] == 0 ? 'background-color: rgba(255, 222, 78, 0.05); font-weight: 500;' : ''; ?>">
                        <td style="font-size: 0.85rem; color: var(--color-text-muted);">
                            <?php echo date("M d, Y h:i A", strtotime($row['date_sent'])); ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                        </td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" style="color: var(--color-primary); text-decoration: underline;">
                                <?php echo htmlspecialchars($row['email']); ?>
                            </a>
                        </td>
                        <td>
                            <div style="white-space: pre-wrap; font-size: 0.9rem; line-height: 1.4; color: #334155;">
                                <?php echo htmlspecialchars($row['message']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($row['is_read'] == 0): ?>
                                <span class="badge badge-unread">Unread</span>
                            <?php else: ?>
                                <span class="badge badge-read">Read</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="contact_messages.php?action=toggle&id=<?php echo $row['id']; ?>" class="btn-action <?php echo $row['is_read'] == 0 ? 'btn-success' : 'btn-edit'; ?>" style="font-size: 0.75rem;">
                                <?php echo $row['is_read'] == 0 ? '<i class="bi bi-check2-circle"></i> Mark Read' : '<i class="bi bi-envelope"></i> Mark Unread'; ?>
                            </a>
                            <a href="contact_messages.php?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" style="font-size: 0.75rem;" onclick="return confirm('Are you sure you want to delete this message?');">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 4rem 2rem; text-align: center;" class="text-muted">
            <h3>No feedback messages found</h3>
            <p style="margin-top: 0.5rem; font-size: 0.95rem;">Submissions from the public contact page will show up here automatically.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/admin_footer.php';
?>
