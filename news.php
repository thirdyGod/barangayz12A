<?php
/**
 * News Page - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$active_page = 'news';

// Helper function to decode images JSON or single string path
function get_article_images($image_data) {
    if (empty($image_data)) return [];
    $decoded = json_decode($image_data, true);
    if (is_array($decoded)) return array_values($decoded);
    return [$image_data];
}

// Determine if we are viewing a single article or the list
$article_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$single_article = null;
$error_message = '';

if ($article_id !== null && $article_id !== false) {
    // Fetch single article
    try {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
        $stmt->execute(['id' => $article_id]);
        $single_article = $stmt->fetch();
        
        if ($single_article) {
            $page_title = $single_article['title'];
        } else {
            $error_message = 'Article not found.';
            $page_title = 'News & Announcements';
        }
    } catch (PDOException $e) {
        $error_message = 'Failed to load article details.';
        $page_title = 'News & Announcements';
    }
} else {
    // Fetch all articles
    try {
        $stmt = $pdo->query("SELECT * FROM news ORDER BY date_posted DESC");
        $news_articles = $stmt->fetchAll();
        $page_title = 'News & Announcements';
    } catch (PDOException $e) {
        $news_articles = [];
        $page_title = 'News & Announcements';
    }
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>News & <span>Announcements</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Stay informed with the latest updates and activities from Barangay Zone 12-A.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($single_article): ?>
            <?php $article_images = get_article_images($single_article['image']); ?>
            
            <!-- Single Article View -->
            <div style="margin-bottom: 2rem;">
                <a href="news.php" class="btn btn-back-nav">
                    <i class="bi bi-arrow-left"></i> Back to All News
                </a>
            </div>
            
            <article class="news-detail-container">
                <header class="news-detail-meta">
                    <h2 class="news-detail-title">
                        <?php echo htmlspecialchars($single_article['title']); ?>
                    </h2>
                    <div class="news-detail-badges">
                        <span class="news-detail-badge-item">
                            <i class="bi bi-calendar-event" style="color: var(--color-primary);"></i> Posted on <?php echo date("F d, Y", strtotime($single_article['date_posted'])); ?>
                        </span>
                        <span class="news-detail-badge-item">
                            <i class="bi bi-person-circle" style="color: var(--color-primary);"></i> Author: <?php echo htmlspecialchars($single_article['author']); ?>
                        </span>
                        <?php if (count($article_images) > 1): ?>
                            <span class="news-detail-badge-item" style="background-color: #dbeafe; color: #1e40af; border-color: #bfdbfe;">
                                <i class="bi bi-images"></i> <?php echo count($article_images); ?> Photos Attached
                            </span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <?php if (!empty($article_images)): ?>
                    <div class="news-gallery-container" style="margin-bottom: 2.5rem;">
                        <!-- Main Display Image -->
                        <div class="news-detail-image-wrapper">
                            <img id="main-featured-photo" src="<?php echo htmlspecialchars($article_images[0]); ?>" alt="<?php echo htmlspecialchars($single_article['title']); ?>">
                        </div>
                        
                        <!-- Gallery Thumbnails (if multiple images) -->
                        <?php if (count($article_images) > 1): ?>
                            <div class="news-gallery-thumbs" style="display: flex; gap: 0.75rem; overflow-x: auto; padding: 0.5rem 0;">
                                <?php foreach ($article_images as $idx => $img_src): ?>
                                    <div class="gallery-thumb-item <?php echo $idx === 0 ? 'active-thumb' : ''; ?>" 
                                         onclick="switchNewsPhoto(this, '<?php echo htmlspecialchars($img_src); ?>')" 
                                         style="width: 100px; height: 75px; flex-shrink: 0; border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; border: 3px solid <?php echo $idx === 0 ? 'var(--color-primary)' : 'transparent'; ?>; transition: var(--transition);">
                                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Photo <?php echo $idx + 1; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="news-detail-content">
                    <p style="white-space: pre-line;"><?php echo htmlspecialchars($single_article['content']); ?></p>
                </div>
            </article>

            <script>
            function switchNewsPhoto(element, src) {
                const mainPhoto = document.getElementById('main-featured-photo');
                if (mainPhoto) {
                    mainPhoto.src = src;
                }
                const thumbs = document.querySelectorAll('.gallery-thumb-item');
                thumbs.forEach(t => t.style.borderColor = 'transparent');
                element.style.borderColor = 'var(--color-primary)';
            }
            </script>
            
        <?php else: ?>
            <!-- News Grid List View -->
            <?php if (!empty($news_articles)): ?>
                <div class="grid-3">
                    <?php foreach ($news_articles as $article): ?>
                        <?php $card_images = get_article_images($article['image']); ?>
                        <div class="card news-card">
                            <?php if (!empty($card_images)): ?>
                                <div style="height: 190px; overflow: hidden; border-bottom: 1px solid var(--color-border); position: relative;">
                                    <img src="<?php echo htmlspecialchars($card_images[0]); ?>" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php if (count($card_images) > 1): ?>
                                        <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(15, 23, 42, 0.85); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-images"></i> <?php echo count($card_images); ?> photos
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
                                    echo strlen($content) > 130 ? substr($content, 0, 127) . '...' : $content; 
                                    ?>
                                </p>
                                <a href="news.php?id=<?php echo $article['id']; ?>" class="btn btn-blue" style="font-size: 0.85rem; padding: 0.5rem 1.25rem; margin-top: auto; align-self: flex-start;">Read More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No news updates or articles are currently available.</p>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
