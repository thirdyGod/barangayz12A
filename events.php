<?php
/**
 * Events & Programs Calendar - Barangay Zone 12-A
 */
require_once 'config.php';

$page_title = 'Events & Programs';
$active_page = 'events';

// Parse image helper
function get_event_images($data) {
    if (empty($data)) return [];
    $d = json_decode($data, true);
    return is_array($d) ? array_values($d) : [$data];
}

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `event_date` DATE NOT NULL,
        `event_time` TIME DEFAULT NULL,
        `location` VARCHAR(255) DEFAULT NULL,
        `category` VARCHAR(100) DEFAULT 'General',
        `image` TEXT DEFAULT NULL,
        `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {}

// Separate upcoming from past
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $all_events = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_events = [];
}

$today = date('Y-m-d');
$upcoming = array_filter($all_events, fn($e) => $e['event_date'] >= $today);
$past     = array_filter($all_events, fn($e) => $e['event_date'] < $today);

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Events & <span>Programs</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Upcoming barangay activities, health programs, community drives, and SK events.</p>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section class="section">
    <div class="container">
        <h2 style="color: var(--color-primary); margin-bottom: 1.75rem; font-size: 1.35rem; border-left: 4px solid var(--color-accent); padding-left: 0.75rem;">
            Upcoming Events
        </h2>

        <?php if (!empty($upcoming)): ?>
            <div class="events-list">
                <?php foreach ($upcoming as $ev):
                    $imgs = get_event_images($ev['image']);
                    $main_img = $imgs[0] ?? null;
                    $ev_date = new DateTime($ev['event_date']);
                ?>
                <div class="event-card">
                    <div class="event-date-block">
                        <span class="event-month"><?php echo $ev_date->format('M'); ?></span>
                        <span class="event-day"><?php echo $ev_date->format('d'); ?></span>
                        <span class="event-year"><?php echo $ev_date->format('Y'); ?></span>
                    </div>
                    <?php if ($main_img): ?>
                        <div class="event-img-wrap">
                            <img src="<?php echo htmlspecialchars($main_img); ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" class="event-img">
                        </div>
                    <?php endif; ?>
                    <div class="event-body">
                        <div class="event-category-badge cat-<?php echo strtolower(preg_replace('/\s+/', '-', $ev['category'])); ?>">
                            <?php echo htmlspecialchars($ev['category']); ?>
                        </div>
                        <h3 class="event-title"><?php echo htmlspecialchars($ev['title']); ?></h3>
                        <div class="event-meta-row">
                            <?php if ($ev['event_time']): ?>
                                <span><i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($ev['event_time'])); ?></span>
                            <?php endif; ?>
                            <?php if ($ev['location']): ?>
                                <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ev['location']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($ev['description']): ?>
                            <p class="event-desc text-muted"><?php echo nl2br(htmlspecialchars($ev['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>No upcoming events scheduled. Check back soon.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($past)): ?>
            <h2 style="color: var(--color-text-muted); margin: 3rem 0 1.5rem; font-size: 1.2rem; border-left: 4px solid var(--color-border); padding-left: 0.75rem;">
                Past Events
            </h2>
            <div class="events-list events-past">
                <?php foreach (array_reverse(array_values($past)) as $ev):
                    $ev_date = new DateTime($ev['event_date']);
                ?>
                <div class="event-card past">
                    <div class="event-date-block muted">
                        <span class="event-month"><?php echo $ev_date->format('M'); ?></span>
                        <span class="event-day"><?php echo $ev_date->format('d'); ?></span>
                        <span class="event-year"><?php echo $ev_date->format('Y'); ?></span>
                    </div>
                    <div class="event-body">
                        <div class="event-category-badge" style="background:#e2e8f0;color:#64748b;">
                            <?php echo htmlspecialchars($ev['category']); ?>
                        </div>
                        <h3 class="event-title" style="color:var(--color-text-muted);"><?php echo htmlspecialchars($ev['title']); ?></h3>
                        <div class="event-meta-row">
                            <?php if ($ev['location']): ?>
                                <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ev['location']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.events-list { display: flex; flex-direction: column; gap: 1.25rem; }

.event-card {
    display: flex;
    align-items: stretch;
    background: var(--color-card-bg);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    border: 1px solid var(--color-border);
    transition: var(--transition);
}
.event-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }
.event-card.past { opacity: 0.72; }

.event-date-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 80px;
    background: var(--color-primary);
    color: #fff;
    padding: 1.25rem 1rem;
    text-align: center;
    flex-shrink: 0;
}
.event-date-block.muted { background: #94a3b8; }
.event-month { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.85; }
.event-day   { font-size: 2rem; font-weight: 800; line-height: 1; }
.event-year  { font-size: 0.7rem; opacity: 0.7; margin-top: 2px; }

.event-img-wrap { width: 140px; flex-shrink: 0; }
.event-img { width: 100%; height: 100%; object-fit: cover; display: block; }

.event-body { padding: 1.25rem 1.5rem; flex: 1; display: flex; flex-direction: column; gap: 0.4rem; }

.event-category-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: #dbeafe;
    color: #1e40af;
    width: fit-content;
}
.cat-health     { background: #fee2e2; color: #ef4444; }
.cat-education  { background: #dbeafe; color: #2563eb; }
.cat-livelihood { background: #d1fae5; color: #059669; }
.cat-sk, .cat-youth { background: #ede9fe; color: #7c3aed; }
.cat-clean-up, .cat-environment { background: #dcfce7; color: #16a34a; }

.event-title { font-size: 1.05rem; font-weight: 700; color: var(--color-text-dark); margin: 0; }

.event-meta-row { display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.82rem; color: var(--color-text-muted); }
.event-meta-row i { margin-right: 0.25rem; }

.event-desc { font-size: 0.88rem; margin-top: 0.25rem; line-height: 1.55; }

.empty-state {
    text-align: center;
    padding: 3.5rem 2rem;
    color: var(--color-text-muted);
    background: var(--color-card-bg);
    border-radius: var(--radius-md);
    border: 1px dashed var(--color-border);
}
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.4; }

@media (max-width: 600px) {
    .event-card { flex-direction: column; }
    .event-date-block { flex-direction: row; min-width: unset; padding: 0.75rem 1rem; gap: 0.5rem; }
    .event-day { font-size: 1.4rem; }
    .event-img-wrap { width: 100%; height: 150px; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
