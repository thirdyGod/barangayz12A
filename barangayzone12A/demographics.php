<?php
/**
 * Demographics Page - Barangay Zone 12-A Information System
 */
require_once 'config.php';

$page_title = 'Demographics';
$active_page = 'demographics';

// Fetch Demographics history
try {
    $demo_stmt = $pdo->query("SELECT year, population, growth_rate FROM demographics ORDER BY year ASC");
    $demographics_data = $demo_stmt->fetchAll();
} catch (PDOException $e) {
    $demographics_data = [];
}

// Fetch Age Groups (Census 2015)
try {
    $age_stmt = $pdo->query("SELECT age_range, population, percentage FROM age_groups WHERE census_year = 2015 ORDER BY id ASC");
    $age_groups_data = $age_stmt->fetchAll();
} catch (PDOException $e) {
    $age_groups_data = [];
}

// Format data for Chart.js
$years = [];
$populations = [];
$growth_rates = [];

foreach ($demographics_data as $row) {
    $years[] = $row['year'];
    $populations[] = $row['population'];
    $growth_rates[] = $row['growth_rate'];
}

$age_ranges = [];
$age_populations = [];
$age_percentages = [];

foreach ($age_groups_data as $row) {
    $age_ranges[] = $row['age_range'];
    $age_populations[] = $row['population'];
    $age_percentages[] = $row['percentage'];
}

require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="hero" style="padding: 3rem 0; text-align: center;">
    <div class="container">
        <div class="hero-box">
            <h1>Barangay <span>Demographics</span></h1>
            <p class="hero-tagline" style="margin-bottom: 0;">Comprehensive statistical overview of our population, households, and age distribution.</p>
        </div>
    </div>
</section>

<!-- Key Stats Cards -->
<section class="section">
    <div class="container">
        <div class="grid-3 demo-stat-grid">
            <div class="card text-center" style="border-top: 5px solid var(--color-primary);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--color-primary);"><i class="bi bi-house"></i></div>
                <h3>Households</h3>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-primary); margin: 0.5rem 0;">2,395</div>
                <p class="text-muted" style="font-size: 0.95rem;">Average Household Size: <strong>4.35</strong> members</p>
            </div>
            
            <div class="card text-center" style="border-top: 5px solid var(--color-accent);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--color-primary);"><i class="bi bi-people"></i></div>
                <h3>Median Age</h3>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-primary); margin: 0.5rem 0;">29.29</div>
                <p class="text-muted" style="font-size: 0.95rem;">Reflecting a young and highly productive population base</p>
            </div>
            
            <div class="card text-center" style="border-top: 5px solid var(--color-primary);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--color-primary);"><i class="bi bi-graph-up-arrow"></i></div>
                <h3>Dependency Ratios</h3>
                <div style="font-size: 1.15rem; font-weight: 600; text-align: left; margin: 1rem 0; padding-left: 1.5rem;">
                    <div>Youth Dependency: <strong style="color: var(--color-primary); float: right; padding-right: 1.5rem;">35.53</strong></div>
                    <div style="margin: 0.5rem 0;">Old-Age Dependency: <strong style="color: var(--color-primary); float: right; padding-right: 1.5rem;">9.68</strong></div>
                    <div style="border-top: 1px solid var(--color-border); padding-top: 0.5rem;">Total Dependency: <strong style="color: var(--color-primary); float: right; padding-right: 1.5rem;">45.21</strong></div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid-2" style="margin-bottom: 3.5rem;">
            <div class="card chart-card">
                <h3 style="margin-bottom: 1rem;">Population Growth (1990 - 2020)</h3>
                <canvas id="growthChart"></canvas>
            </div>
            <div class="card chart-card-doughnut">
                <h3 style="margin-bottom: 1rem;">Age Distribution (2015 Census)</h3>
                <canvas id="ageChart"></canvas>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="card">
            <h3 style="margin-bottom: 1.5rem;">Demographics History Data</h3>
            <div style="overflow-x: auto;">
                <table class="info-table" style="margin: 0; box-shadow: none;">
                    <thead>
                        <tr style="background-color: rgba(17, 46, 129, 0.05);">
                            <th>Census Year</th>
                            <th>Total Population</th>
                            <th>Growth Rate (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demographics_data as $row): ?>
                            <tr>
                                <td><strong><?php echo $row['year']; ?></strong></td>
                                <td><?php echo number_format($row['population']); ?></td>
                                <td><?php echo $row['growth_rate'] !== null ? $row['growth_rate'] . '%' : '<span class="text-muted">N/A</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- CDN Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Population Growth Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($years); ?>,
            datasets: [{
                label: 'Barangay Population',
                data: <?php echo json_encode($populations); ?>,
                borderColor: '#112E81',
                backgroundColor: 'rgba(17, 46, 129, 0.08)',
                borderWidth: 3,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#FFDE4E',
                pointBorderColor: '#112E81',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Age Distribution Chart (Horizontal Bar Chart for readability)
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($age_ranges); ?>,
            datasets: [{
                label: 'Population',
                data: <?php echo json_encode($age_populations); ?>,
                backgroundColor: 'rgba(17, 46, 129, 0.8)',
                hoverBackgroundColor: '#FFDE4E',
                borderRadius: 4,
                borderWidth: 0
            }]
        },
        options: {
            indexAxis: 'y', // Makes the bar chart horizontal
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const population = context.raw;
                            const percentages = <?php echo json_encode($age_percentages); ?>;
                            return `Population: ${population.toLocaleString()} (${percentages[index]}%)`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
