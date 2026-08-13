<?php
/**
 * Officials Page - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$page_title = 'Barangay Officials';
$active_page = 'officials';

// Fetch officials ordered by order_display
try {
    $stmt = $pdo->query("SELECT * FROM officials ORDER BY order_display ASC");
    $officials = $stmt->fetchAll();
} catch (PDOException $e) {
    $officials = [];
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Barangay <span>Officials</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Dedicated public servants leading Zone 12-A towards progressive development.</p>
        </div>
    </div>
</section>

<!-- Officials Listing -->
<section class="section">
    <div class="container">
        <?php if (!empty($officials)): ?>
            <div class="grid-3 officials-grid">
                <?php foreach ($officials as $official): ?>
                    <div class="card official-card">
                        <div class="official-avatar">
                            <?php if ($official['photo']): ?>
                                <img class="avatar-bg" src="<?php echo htmlspecialchars($official['photo']); ?>" alt="" aria-hidden="true">
                                <img class="avatar-fg" src="<?php echo htmlspecialchars($official['photo']); ?>" alt="<?php echo htmlspecialchars($official['name']); ?>">
                            <?php else: ?>
                                <i class="bi bi-person-fill"></i>
                            <?php endif; ?>
                        </div>
                        <div class="official-photo-bar"></div>
                        <div class="official-info">
                            <h3 class="official-name"><?php echo htmlspecialchars($official['name']); ?></h3>
                            <div class="official-position"><?php echo htmlspecialchars($official['position']); ?></div>
                            <div class="official-term"><i class="bi bi-calendar3"></i> Term: <?php echo htmlspecialchars($official['term_start']); ?> – <?php echo htmlspecialchars($official['term_end']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted" style="margin-bottom: 3.5rem;">No officials data found in the system database.</p>
        <?php endif; ?>


    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
