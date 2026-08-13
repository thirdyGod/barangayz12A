<?php
/**
 * About Page - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$page_title = 'About Barangay';
$active_page = 'about';

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>About <span>Barangay Zone 12-A</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Learn more about our history, location, and civic goals.</p>
        </div>
    </div>
</section>

<!-- Overview Section -->
<section class="section">
    <div class="container">
        <div class="grid-2 about-hero-grid">
            <div>
                <h2 style="margin-bottom: 1.5rem;">Historical Overview</h2>
                <p style="margin-bottom: 1.25rem; font-size: 1.05rem;">
                    <strong>Barangay Zone 12-A</strong> (historically known as part of the <em>Poblacion</em> or urban core) is situated in the <strong>City of Talisay</strong>, within the province of <strong>Negros Occidental</strong>, under the newly established <strong>Negros Island Region</strong> of the Philippines.
                </p>
                <p style="margin-bottom: 1.25rem;">
                    As the heart of the city's commercial and residential expansion, Zone 12-A has transitioned from a simple settlement into the largest and most populous barangay in Talisay City. With a rich cultural background tied closely to the sugarcane heritage of Negros Occidental, it stands today as a modern, progressive urban community.
                </p>
                <p>
                    Today, the barangay serves as a critical administrative and geographic hub, hosting various civic facilities, local businesses, and public services that support the rapidly expanding population of Talisay City.
                </p>
            </div>
            <div>
                <div class="card" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--color-accent); padding-bottom: 0.5rem;">Barangay Profile</h3>
                    <div class="table-responsive">
                        <table class="info-table" style="margin: 0; box-shadow: none;">
                            <tbody>
                                <tr>
                                    <th>Classification</th>
                                    <td>Urban (Poblacion)</td>
                                </tr>
                                <tr>
                                    <th>City</th>
                                    <td>Talisay City</td>
                                </tr>
                                <tr>
                                    <th>Province</th>
                                    <td>Negros Occidental</td>
                                </tr>
                                <tr>
                                    <th>Region</th>
                                    <td>Negros Island Region</td>
                                </tr>
                                <tr>
                                    <th>Postal Code</th>
                                    <td>6115</td>
                                </tr>
                                <tr>
                                    <th>Elevation</th>
                                    <td>10.9 meters (35.8 feet) above sea level</td>
                                </tr>
                                <tr>
                                    <th>Coordinates</th>
                                    <td>10.7419° N, 122.9764° E</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="section section-bg-white">
    <div class="container">
        <div class="grid-2">
            <div class="card" style="border-top: 5px solid var(--color-primary);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-primary);"><i class="bi bi-eye"></i></div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Our Vision</h3>
                <p style="line-height: 1.8; color: var(--color-text-muted);">
                    We envision Barangay Zone 12-A as a model, self-reliant urban community in Talisay City, populated by healthy, productive, and God-fearing citizens living in a safe and clean environment. Led by transparent, responsive, and united leaders, we strive for sustainable economic growth and social justice.
                </p>
            </div>
            <div class="card" style="border-top: 5px solid var(--color-accent);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-primary);"><i class="bi bi-rocket-takeoff"></i></div>
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Our Mission</h3>
                <p style="line-height: 1.8; color: var(--color-text-muted);">
                    To improve the quality of life for all residents of Zone 12-A by delivering efficient public services, maintaining peace and order, encouraging community-wide participation in local governance, and implementing sustainable programs in health, education, infrastructure, and environmental preservation.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
