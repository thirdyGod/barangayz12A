<?php
/**
 * Admin Panel Header Layout - Shared template
 * Starts session, runs security guard, and establishes navigation.
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config.php';

$active_tab = isset($active_tab) ? $active_tab : 'dashboard';
$admin_title = isset($admin_title) ? $admin_title : 'Admin Dashboard';

// Calculate admin notifications
$unread_messages_count = 0;
$pending_requests_count = 0;
$open_blotter_count = 0;

if (isset($pdo)) {
    try {
        // Unread contact messages
        $msg_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
        $unread_messages_count = $msg_stmt ? (int)$msg_stmt->fetchColumn() : 0;
        
        // Pending document requests
        $req_stmt = $pdo->query("SELECT COUNT(*) FROM document_requests WHERE status = 'Pending'");
        $pending_requests_count = $req_stmt ? (int)$req_stmt->fetchColumn() : 0;
        
        // Open blotter records
        $blotter_stmt = $pdo->query("SELECT COUNT(*) FROM blotter_records WHERE status = 'Open'");
        $open_blotter_count = $blotter_stmt ? (int)$blotter_stmt->fetchColumn() : 0;
    } catch (PDOException $e) {
        // Fail silently
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($admin_title); ?> - Barangay Zone 12-A Admin</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/admin_style.css">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header" style="display: flex; align-items: center; gap: 0.75rem;">
            <img src="../assets/images/logo.png" alt="Logo" style="height: 35px; width: 35px; object-fit: contain;">
            <div>
                <div class="sidebar-title">ZONE 12-A</div>
                <div class="sidebar-badge">Admin System</div>
            </div>
        </div>
        
        <ul class="admin-sidebar-menu">
            <li class="sidebar-item <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'officials' ? 'active' : ''; ?>">
                <a href="manage_officials.php"><i class="bi bi-people"></i> Manage Officials</a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'news' ? 'active' : ''; ?>">
                <a href="manage_news.php"><i class="bi bi-newspaper"></i> Manage News</a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'facilities' ? 'active' : ''; ?>">
                <a href="manage_facilities.php"><i class="bi bi-building"></i> Manage Facilities</a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'messages' ? 'active' : ''; ?>">
                <a href="contact_messages.php"><i class="bi bi-envelope"></i> Feedback Messages<?php if ($unread_messages_count > 0): ?><span class="admin-sidebar-badge danger" title="Unread Messages"><?php echo $unread_messages_count; ?></span><?php endif; ?></a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'events' ? 'active' : ''; ?>">
                <a href="manage_events.php"><i class="bi bi-calendar-event"></i> Manage Events</a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'requests' ? 'active' : ''; ?>">
                <a href="manage_requests.php"><i class="bi bi-file-earmark-check"></i> Doc. Requests<?php if ($pending_requests_count > 0): ?><span class="admin-sidebar-badge warning" title="Pending Requests"><?php echo $pending_requests_count; ?></span><?php endif; ?></a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'blotter' ? 'active' : ''; ?>">
                <a href="manage_blotter.php"><i class="bi bi-journal-text"></i> Blotter Records<?php if ($open_blotter_count > 0): ?><span class="admin-sidebar-badge info" title="Open Blotters"><?php echo $open_blotter_count; ?></span><?php endif; ?></a>
            </li>
            <li class="sidebar-item <?php echo $active_tab === 'budget' ? 'active' : ''; ?>">
                <a href="manage_budget.php"><i class="bi bi-cash-stack"></i> Budget Transparency</a>
            </li>
            <li class="sidebar-item">
                <a href="../index.php" target="_blank"><i class="bi bi-globe"></i> View Public Site</a>
            </li>
        </ul>
        
        <div class="admin-sidebar-footer">
            <div class="sidebar-user"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
            <a href="logout.php" style="color: var(--color-accent); font-weight: bold;"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>
    </aside>

    <!-- Main Content Container -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="admin-header-title"><?php echo htmlspecialchars($admin_title); ?></div>
            <div style="font-size: 0.85rem; font-weight: 500; color: var(--color-text-muted);">
                Negros Occidental, PH
            </div>
        </header>
        
        <div class="admin-content">
