<?php
/**
 * Shared Header Template
 * Variables:
 *  - $page_title: Title of the current page
 *  - $active_page: Name of the active menu link
 */
$page_title = isset($page_title) ? $page_title : 'Barangay Information System';
$active_page = isset($active_page) ? $active_page : 'home';

// Notification tracking values for public navigation items
$news_val = '0-0';
$events_val = '0-0';
$officials_val = '0-0';
$facilities_val = '0-0';
$demographics_val = '0-0';
$transparency_val = '0-0';
$upcoming_events_count = 0;

// Track what update version the user is currently looking at
$active_page_val = '';

if (isset($pdo)) {
    // News updates tracking
    try {
        $news_q = $pdo->query("SELECT CONCAT(IFNULL(MAX(id), 0), '-', COUNT(*)) FROM news");
        if ($news_q) {
            $news_val = $news_q->fetchColumn();
        }
    } catch (PDOException $e) {}
    
    // Events updates tracking
    try {
        $events_q = $pdo->query("SELECT CONCAT(IFNULL(MAX(id), 0), '-', COUNT(*)) FROM events WHERE event_date >= CURDATE()");
        if ($events_q) {
            $events_val = $events_q->fetchColumn();
        }
        $events_count_q = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()");
        if ($events_count_q) {
            $upcoming_events_count = (int)$events_count_q->fetchColumn();
        }
    } catch (PDOException $e) {}
    
    // Officials updates tracking
    try {
        $officials_q = $pdo->query("SELECT CONCAT(IFNULL(MAX(id), 0), '-', COUNT(*)) FROM officials");
        if ($officials_q) {
            $officials_val = $officials_q->fetchColumn();
        }
    } catch (PDOException $e) {}
    
    // Facilities updates tracking
    try {
        $facilities_q = $pdo->query("SELECT CONCAT(IFNULL(MAX(id), 0), '-', COUNT(*)) FROM facilities");
        if ($facilities_q) {
            $facilities_val = $facilities_q->fetchColumn();
        }
    } catch (PDOException $e) {}
    
    // Demographics updates tracking
    try {
        $demo_q = $pdo->query("SELECT CONCAT(IFNULL(MAX(id), 0), '-', COUNT(*)) FROM demographics");
        if ($demo_q) {
            $demographics_val = $demo_q->fetchColumn();
        }
    } catch (PDOException $e) {}
    
    // Budget transparency updates tracking
    try {
        $trans_q = $pdo->query("SELECT CONCAT(IFNULL(MAX(id), 0), '-', COUNT(*)) FROM budget_entries");
        if ($trans_q) {
            $transparency_val = $trans_q->fetchColumn();
        }
    } catch (PDOException $e) {}

    // Identify active page tracking value to mark as read
    switch ($active_page) {
        case 'news': $active_page_val = $news_val; break;
        case 'events': $active_page_val = $events_val; break;
        case 'officials': $active_page_val = $officials_val; break;
        case 'facilities': $active_page_val = $facilities_val; break;
        case 'demographics': $active_page_val = $demographics_val; break;
        case 'transparency': $active_page_val = $transparency_val; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Official website of Barangay Zone 12-A, Talisay City, Negros Occidental. Find demographics, officials, news, facilities, and contact information.">
    <title><?php echo htmlspecialchars($page_title); ?> - Barangay Zone 12-A</title>
    
    <!-- Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="main-header">
    <div class="container">
        <nav class="navbar" id="navbar">
            <a href="index.php" class="nav-brand">
                <img src="assets/images/logo.png" alt="Barangay Zone 12-A Logo" style="height: 45px; width: 45px; object-fit: contain;">
                <div>
                    <div class="nav-brand-title">ZONE 12-A</div>
                    <div class="nav-brand-subtitle">Talisay City, Negros Occidental</div>
                </div>
            </a>
            
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle Navigation">
                &#9776;
            </button>
            
            <ul class="nav-menu" id="nav-menu">
                <li><a href="index.php" class="nav-link <?php echo $active_page === 'home' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="about.php" class="nav-link <?php echo $active_page === 'about' ? 'active' : ''; ?>">About</a></li>
                <li><a href="demographics.php" class="nav-link <?php echo $active_page === 'demographics' ? 'active' : ''; ?>" data-nav-key="demographics" data-nav-val="<?php echo $demographics_val; ?>">Demographics<span class="badge-dot" title="Demographics Updated"></span></a></li>
                <li><a href="officials.php" class="nav-link <?php echo $active_page === 'officials' ? 'active' : ''; ?>" data-nav-key="officials" data-nav-val="<?php echo $officials_val; ?>">Officials<span class="badge-dot" title="Officials List Updated"></span></a></li>
                <li><a href="facilities.php" class="nav-link <?php echo $active_page === 'facilities' ? 'active' : ''; ?>" data-nav-key="facilities" data-nav-val="<?php echo $facilities_val; ?>">Facilities<span class="badge-dot" title="Facilities Updated"></span></a></li>
                <li><a href="news.php" class="nav-link <?php echo $active_page === 'news' ? 'active' : ''; ?>" data-nav-key="news" data-nav-val="<?php echo $news_val; ?>">News<span class="badge-dot" title="New Announcement Uploaded"></span></a></li>
                <li><a href="events.php" class="nav-link <?php echo $active_page === 'events' ? 'active' : ''; ?>" data-nav-key="events" data-nav-val="<?php echo $events_val; ?>">Events<?php if ($upcoming_events_count > 0): ?><span class="badge-count" title="Upcoming Events Scheduled"><?php echo $upcoming_events_count; ?></span><?php endif; ?></a></li>
                <li><a href="request.php" class="nav-link <?php echo $active_page === 'request' ? 'active' : ''; ?>">Doc. Request</a></li>
                <li><a href="transparency.php" class="nav-link <?php echo $active_page === 'transparency' ? 'active' : ''; ?>" data-nav-key="transparency" data-nav-val="<?php echo $transparency_val; ?>">Transparency<span class="badge-dot" title="Transparency Budget Updated"></span></a></li>
                <li><a href="contact.php" class="nav-link <?php echo $active_page === 'contact' ? 'active' : ''; ?>">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const isFirstVisit = !localStorage.getItem('site_has_visited');
    
    // Check and update notification badges
    document.querySelectorAll('[data-nav-key]').forEach(link => {
        const key = link.getAttribute('data-nav-key');
        const val = link.getAttribute('data-nav-val');
        const badge = link.querySelector('.badge-dot, .badge-count');
        
        if (badge) {
            if (isFirstVisit) {
                // Initialize for first-time visitors so they aren't overwhelmed with dots
                localStorage.setItem('nav_viewed_' + key, val);
                badge.style.display = 'none';
            } else {
                // For returning users, hide if already seen this update version
                if (localStorage.getItem('nav_viewed_' + key) === val) {
                    badge.style.display = 'none';
                }
            }
            
            // Instantly hide badge when clicked
            link.addEventListener('click', function() {
                badge.style.display = 'none';
            });
        }
    });
    
    // Set the first visit flag
    localStorage.setItem('site_has_visited', 'true');
    
    // Auto-update active page to marked as viewed in LocalStorage
    <?php if (!empty($active_page) && !empty($active_page_val)): ?>
    localStorage.setItem('nav_viewed_<?php echo htmlspecialchars($active_page); ?>', '<?php echo htmlspecialchars($active_page_val); ?>');
    <?php endif; ?>
});
</script>
