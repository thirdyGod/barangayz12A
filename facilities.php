<?php
/**
 * Facilities Page - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$page_title = 'Public Facilities';
$active_page = 'facilities';

// Helper: normalize image data (JSON array or legacy single path) → flat array
function get_facility_images($image_data) {
    if (empty($image_data)) return [];
    $decoded = json_decode($image_data, true);
    if (is_array($decoded)) return array_values($decoded);
    return [$image_data];
}

// Fetch facilities and group them in PHP
try {
    $stmt = $pdo->query("SELECT * FROM facilities ORDER BY type ASC, name ASC");
    $facilities = $stmt->fetchAll();
    
    // Group facilities by type
    $grouped_facilities = [];
    foreach ($facilities as $facility) {
        $grouped_facilities[$facility['type']][] = $facility;
    }
} catch (PDOException $e) {
    $grouped_facilities = [];
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Public <span>Facilities & Services</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Directory of health, utility, education, and government facilities serving Zone 12-A.</p>
        </div>
    </div>
</section>

<!-- Facilities Directory -->
<section class="section">
    <div class="container">
        <?php if (!empty($grouped_facilities)): ?>
            <?php foreach ($grouped_facilities as $type => $type_facilities): ?>
                <div class="facility-group">
                    <h2 class="facility-group-title">
                        <?php 
                        // Add icons depending on the facility type
                        $icon = '<i class="bi bi-bank"></i>';
                        if (strtolower($type) === 'health') $icon = '<i class="bi bi-hospital"></i>';
                        elseif (strtolower($type) === 'utility') $icon = '<i class="bi bi-plug"></i>';
                        elseif (strtolower($type) === 'education') $icon = '<i class="bi bi-book"></i>';
                        
                        echo $icon . ' ' . htmlspecialchars($type); 
                        ?>
                    </h2>
                    
                    <div class="grid-3">
                        <?php foreach ($type_facilities as $facility): ?>
                            <?php
                            $f_imgs = isset($facility['image']) ? get_facility_images($facility['image']) : [];
                            $f_main = !empty($f_imgs) ? $f_imgs[0] : null;
                            ?>
                            <div class="card facility-card">
                                <?php if ($f_main): ?>
                                    <div class="facility-photo-wrap" id="facility-photo-wrap-<?php echo $facility['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($f_main); ?>" alt="<?php echo htmlspecialchars($facility['name']); ?>" class="facility-main-photo" id="facility-main-<?php echo $facility['id']; ?>">
                                        <?php if (count($f_imgs) > 1): ?>
                                            <div class="facility-thumb-strip">
                                                <?php foreach ($f_imgs as $t_idx => $t_img): ?>
                                                    <img src="<?php echo htmlspecialchars($t_img); ?>" alt="Photo <?php echo $t_idx + 1; ?>" class="facility-thumb <?php echo $t_idx === 0 ? 'active' : ''; ?>" onclick="switchFacilityPhoto(<?php echo $facility['id']; ?>, '<?php echo htmlspecialchars($t_img); ?>', this)">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <h3 class="facility-card-name"><?php echo htmlspecialchars($facility['name']); ?></h3>

                                <div class="facility-meta">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($facility['address']); ?>
                                </div>

                                <?php if ($facility['contact_number']): ?>
                                    <div class="facility-meta">
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($facility['contact_number']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($facility['description']): ?>
                                    <p class="facility-desc text-muted">
                                        <?php echo htmlspecialchars($facility['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">No public facilities listed in the database directory.</p>
        <?php endif; ?>
    </div>
</section>

<style>
.facility-photo-wrap {
    width: 100%;
    margin-bottom: 1rem;
    border-radius: var(--radius-sm);
    overflow: hidden;
}
.facility-main-photo {
    width: 100%;
    height: 190px;
    object-fit: cover;
    display: block;
    border-radius: var(--radius-sm);
    transition: opacity 0.25s ease;
}
.facility-thumb-strip {
    display: flex;
    gap: 0.4rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}
.facility-thumb {
    width: 52px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.65;
    transition: opacity 0.2s, border-color 0.2s;
}
.facility-thumb:hover,
.facility-thumb.active {
    opacity: 1;
    border-color: var(--color-accent, #3b82f6);
}
</style>

<script>
function switchFacilityPhoto(facilityId, imgSrc, thumbEl) {
    const mainImg = document.getElementById('facility-main-' + facilityId);
    if (mainImg) {
        mainImg.style.opacity = '0.5';
        setTimeout(() => {
            mainImg.src = imgSrc;
            mainImg.style.opacity = '1';
        }, 150);
    }
    // Update active thumb
    const wrap = document.getElementById('facility-photo-wrap-' + facilityId);
    if (wrap) {
        wrap.querySelectorAll('.facility-thumb').forEach(t => t.classList.remove('active'));
    }
    if (thumbEl) thumbEl.classList.add('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
