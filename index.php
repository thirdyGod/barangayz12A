<?php
/**
 * Homepage - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$page_title = 'Welcome';
$active_page = 'home';

// Fetch the latest population stats (usually year 2020 from demographics)
try {
    $pop_stmt = $pdo->query("SELECT population, growth_rate FROM demographics ORDER BY year DESC LIMIT 1");
    $latest_demographics = $pop_stmt->fetch();
    $population = $latest_demographics ? number_format($latest_demographics['population']) : '12,419';
    $growth_rate = $latest_demographics ? $latest_demographics['growth_rate'] : '3.76';
} catch (PDOException $e) {
    $population = '12,419';
    $growth_rate = '3.76';
}

// Fetch 3 most recent news articles
try {
    $news_stmt = $pdo->query("SELECT * FROM news ORDER BY date_posted DESC LIMIT 3");
    $recent_news = $news_stmt->fetchAll();
} catch (PDOException $e) {
    $recent_news = [];
}

// Fetch top 2 officials for leadership preview
try {
    $off_stmt = $pdo->query("SELECT * FROM officials ORDER BY order_display ASC LIMIT 2");
    $key_officials = $off_stmt->fetchAll();
} catch (PDOException $e) {
    $key_officials = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="hero-badge">Official Portal</span>
            <h1>Barangay <span>Zone 12-A</span></h1>
            <p class="hero-tagline">A progressive, resilient, and united community in the heart of Talisay City, Negros Occidental.</p>
            
            <div class="hero-stats">
                <div class="hero-stat-card">
                    <div class="hero-stat-num"><?php echo $population; ?></div>
                    <div class="hero-stat-lbl">Total Population</div>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-num">11.40%</div>
                    <div class="hero-stat-lbl">of Talisay's Population</div>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-num"><?php echo $growth_rate; ?>%</div>
                    <div class="hero-stat-lbl">Growth Rate</div>
                </div>
            </div>
            
            <div class="hero-actions">
                <a href="about.php" class="btn btn-primary">Explore About Us</a>
                <a href="contact.php" class="btn btn-outline">Get in Touch</a>
            </div>
        </div>
    </div>
</section>

<!-- Quick Navigation Cards -->
<section class="section">
    <div class="container">
        <div class="grid-3">
            <div class="card text-center">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-primary);"><i class="bi bi-clock-history"></i></div>
                <h3>Barangay History</h3>
                <p class="text-muted" style="margin: 1rem 0; font-size: 0.95rem;">Learn about Zone 12-A, its geography, coordinates, elevation, and historical background.</p>
                <a href="about.php" class="btn btn-blue" style="font-size: 0.85rem; padding: 0.5rem 1.25rem;">View Profile</a>
            </div>
            <div class="card text-center">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-primary);"><i class="bi bi-bar-chart-line"></i></div>
                <h3>Barangay Demographics</h3>
                <p class="text-muted" style="margin: 1rem 0; font-size: 0.95rem;">View the historical population census charts, household numbers, and age range distributions.</p>
                <a href="demographics.php" class="btn btn-blue" style="font-size: 0.85rem; padding: 0.5rem 1.25rem;">View Charts</a>
            </div>
            <div class="card text-center">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-primary);"><i class="bi bi-building"></i></div>
                <h3>Public Facilities</h3>
                <p class="text-muted" style="margin: 1rem 0; font-size: 0.95rem;">Browse public health centers, utility facilities, and local educational institutions in our zone.</p>
                <a href="facilities.php" class="btn btn-blue" style="font-size: 0.85rem; padding: 0.5rem 1.25rem;">Explore Directory</a>
            </div>
        </div>
    </div>
</section>

<!-- "Did You Know" Section -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="featured-info">
            <div class="featured-icon"><i class="bi bi-lightbulb"></i></div>
            <div class="featured-text">
                <h3>Did you know?</h3>
                <p><strong>Barangay Zone 12-A</strong> is the largest barangay in Talisay City by population. Additionally, its population growth rate of <strong>3.76%</strong> is nearly three times higher than the citywide average growth rate of <strong>1.34%</strong>, reflecting its rapid urbanization and strategic economic position in the municipality.</p>
            </div>
        </div>
    </div>
</section>

<!-- Key Leadership Preview -->
<section class="section leadership-section">
    <!-- Officials photo at low opacity — mirrors hero section -->
    <div class="leadership-bg" aria-hidden="true"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="section-title-wrapper text-center">
            <span class="section-subtitle">Barangay Administration</span>
            <h2 class="section-title">Barangay Leadership</h2>
        </div>
        
        <?php if (!empty($key_officials)): ?>
            <div class="grid-2" style="max-width: 800px; margin: 0 auto;">
                <?php foreach ($key_officials as $official): ?>
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
                            <div class="official-name"><?php echo htmlspecialchars($official['name']); ?></div>
                            <div class="official-position"><?php echo htmlspecialchars($official['position']); ?></div>
                            <div class="official-term"><i class="bi bi-calendar3"></i> Term: <?php echo htmlspecialchars($official['term_start']); ?> – <?php echo htmlspecialchars($official['term_end']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center" style="margin-top: 3rem;">
                <a href="officials.php" class="btn btn-blue">Meet All Officials</a>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No officials data found.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Recent News / Announcements -->
<section class="section">
    <div class="container">
        <div class="section-title-wrapper">
            <span class="section-subtitle">Announcements</span>
            <h2 class="section-title">Community News</h2>
        </div>
        
        <?php if (!empty($recent_news)): ?>
            <div class="grid-3">
                <?php foreach ($recent_news as $article): ?>
                    <?php 
                    $card_imgs = [];
                    if (!empty($article['image'])) {
                        $dec = json_decode($article['image'], true);
                        $card_imgs = is_array($dec) ? array_values($dec) : [$article['image']];
                    }
                    ?>
                    <div class="card news-card">
                        <?php if (!empty($card_imgs)): ?>
                            <div style="height: 160px; overflow: hidden; border-bottom: 1px solid var(--color-border); position: relative;">
                                <img src="<?php echo htmlspecialchars($card_imgs[0]); ?>" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php if (count($card_imgs) > 1): ?>
                                    <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(15, 23, 42, 0.85); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="bi bi-images"></i> <?php echo count($card_imgs); ?> photos
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="news-card-body">
                            <div class="news-meta">
                                <i class="bi bi-calendar-event"></i> <?php echo date("F d, Y", strtotime($article['date_posted'])); ?> | <i class="bi bi-person"></i> <?php echo htmlspecialchars($article['author']); ?>
                            </div>
                            <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p class="news-excerpt">
                                <?php 
                                $content = htmlspecialchars($article['content']);
                                echo strlen($content) > 120 ? substr($content, 0, 117) . '...' : $content; 
                                ?>
                            </p>
                            <a href="news.php?id=<?php echo $article['id']; ?>" class="btn btn-blue" style="font-size: 0.85rem; padding: 0.5rem 1.25rem; margin-top: auto; align-self: flex-start;">Read Article</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No news updates available at this moment. Please check back later.</p>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
