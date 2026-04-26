<?php
/**
 * Samburu EWS: Scientific Data
 *
 * Seasonal KMD forecast history, trend charts, and current NDMA status
 * for Samburu County, all sourced from real bulletin data.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';
require __DIR__ . '/includes/Db.php';

$pageTitle   = 'Scientific Data';
$kmd         = DataRepository::load('kmd_summary.json');
$ndma        = DataRepository::load('ndma_latest.json');
$seasonal    = DataRepository::load('kmd_seasonal.json');
$bulletins   = DataRepository::load('kmd_bulletins.json') ?? [];
$ndmaHistory = DataRepository::load('ndma_history.json') ?? [];

$seasons         = $seasonal['source_only_data']['seasons']                                     ?? [];
$tercileChart    = $seasonal['visualization_ready_data']['forecast_tercile_probabilities_chart'] ?? [];
$rainfallActuals = $seasonal['visualization_ready_data']['rainfall_review_actuals_chart']        ?? [];
$spiChart        = $seasonal['visualization_ready_data']['drought_spi_probabilities_chart']      ?? [];

// Live bulletin links from DB; fall back to homepages if not synced
$liveKmdLink  = 'https://meteo.go.ke/our-products/monthly-forecast/';
$liveNdmaLink = 'https://ndma.go.ke/drought-information/';
try {
    $kmdRow  = Db::fetch('SELECT pdf_url, page_url FROM kmd_ndma_reports WHERE source_org = ? ORDER BY synced_at DESC LIMIT 1', ['KMD']);
    $ndmaRow = Db::fetch('SELECT pdf_url, page_url FROM kmd_ndma_reports WHERE source_org = ? ORDER BY synced_at DESC LIMIT 1', ['NDMA']);
    if ($kmdRow)  $liveKmdLink  = $kmdRow['pdf_url']  ?: $kmdRow['page_url'];
    if ($ndmaRow) $liveNdmaLink = $ndmaRow['pdf_url'] ?: $ndmaRow['page_url'];
} catch (Throwable) {}

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Scientific Data</h1>
        <p>Kenya Meteorological Department seasonal forecasts and NDMA drought assessments for Samburu County, extracted from official bulletins, 2021–2026.</p>
    </div>
</section>

<!-- Current Status Cards (from NDMA latest) -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Current Drought Status: Samburu County</h2>
            <p>From NDMA Bulletin (<?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?>). Phase: <strong><?= htmlspecialchars($ndma['phase'] ?? '—') ?></strong>, <?= htmlspecialchars($ndma['phase_justification'] ?? '') ?></p>
        </div>

        <div class="grid grid-4 grid-auto">
            <!-- Phase -->
            <?php
            $phase = strtoupper($ndma['phase'] ?? '');
            $phaseColor = match($phase) {
                'NORMAL'    => ['bg' => 'var(--clr-primary-pale)',  'fg' => 'var(--clr-primary)'],
                'WATCH'     => ['bg' => 'var(--clr-info-light)',    'fg' => 'var(--clr-info)'],
                'ALERT'     => ['bg' => 'var(--clr-warning-light)', 'fg' => 'var(--clr-warning)'],
                'ALARM'     => ['bg' => '#fde2de',                  'fg' => 'var(--clr-danger)'],
                'EMERGENCY' => ['bg' => '#5a0000',                  'fg' => '#fff'],
                default     => ['bg' => 'var(--clr-info-light)',    'fg' => 'var(--clr-info)'],
            };
            ?>
            <div class="card sci-stat-card" style="border-top:4px solid <?= $phaseColor['fg'] ?>;">
                <div class="sci-stat-label">NDMA Drought Phase</div>
                <div class="sci-stat-value" style="color:<?= $phaseColor['fg'] ?>;"><?= htmlspecialchars($ndma['phase'] ?? '—') ?></div>
                <div class="sci-stat-sub"><?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?></div>
            </div>

            <!-- Food Security -->
            <?php $fs = $ndma['food_security'] ?? []; ?>
            <div class="card sci-stat-card" style="border-top:4px solid var(--clr-warning);">
                <div class="sci-stat-label">Food Security</div>
                <div class="sci-stat-value" style="color:var(--clr-warning);"><?= $fs['acceptable_pct'] ?? '—' ?>%</div>
                <div class="sci-stat-sub">Acceptable · <?= $fs['borderline_pct'] ?? '—' ?>% Borderline · <?= $fs['poor_pct'] ?? '—' ?>% Poor</div>
            </div>

            <!-- Livestock -->
            <?php $lv = $ndma['livestock'] ?? []; ?>
            <div class="card sci-stat-card" style="border-top:4px solid var(--clr-success);">
                <div class="sci-stat-label">Livestock Condition</div>
                <div class="sci-stat-value" style="color:var(--clr-success);font-size:var(--fs-lg);">Fair / Good</div>
                <div class="sci-stat-sub"><?= htmlspecialchars($lv['body_condition'] ?? '—') ?> · <?= htmlspecialchars($lv['migration_status'] ?? '') ?></div>
            </div>

            <!-- Water Access -->
            <?php $wa = $ndma['water'] ?? []; ?>
            <div class="card sci-stat-card" style="border-top:4px solid var(--clr-info);">
                <div class="sci-stat-label">Distance to Water</div>
                <div class="sci-stat-value" style="color:var(--clr-info);"><?= $wa['distance_to_water_km'] ?? '—' ?> km</div>
                <div class="sci-stat-sub">Normal: <?= $wa['normal_distance_km'] ?? '—' ?> km, closer than average</div>
            </div>
        </div>

        <!-- Vegetation sub-county breakdown -->
        <?php $sc = $ndma['samburu_specific'] ?? []; ?>
        <?php if (!empty($sc)): ?>
        <div class="card mt-lg" style="border-left:4px solid var(--clr-primary-light);">
            <h3 class="card-title mb-sm">Vegetation Condition by Sub-County</h3>
            <div class="grid grid-3 grid-auto">
                <?php if (!empty($sc['vegetation_east'])): ?>
                <div class="sci-veg-item sci-veg-stress">
                    <div class="sci-veg-label">Samburu East</div>
                    <p><?= htmlspecialchars($sc['vegetation_east']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($sc['vegetation_north'])): ?>
                <div class="sci-veg-item sci-veg-moderate">
                    <div class="sci-veg-label">Samburu North</div>
                    <p><?= htmlspecialchars($sc['vegetation_north']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($sc['vegetation_central'])): ?>
                <div class="sci-veg-item sci-veg-normal">
                    <div class="sci-veg-label">Samburu Central</div>
                    <p><?= htmlspecialchars($sc['vegetation_central']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- NDMA Monthly Trend -->
<?php if (!empty($ndmaHistory)): ?>
<section class="page-section" id="ndmaTrend">
    <div class="container">
        <div class="section-header">
            <h2>NDMA Ground Conditions: 4-Month Trend</h2>
            <p>What actually happened on the ground in Samburu County, measured monthly by NDMA field teams from November 2025 to February 2026.</p>
        </div>

        <!-- Phase transition timeline -->
        <div class="ndma-tl-wrap">
            <?php foreach ($ndmaHistory as $i => $h):
                $ph = strtoupper($h['phase'] ?? '');
                $phColor = match($ph) {
                    'NORMAL'    => ['bg' => '#f0fdf4', 'border' => '#16a34a', 'text' => '#14532d', 'badge' => '#16a34a', 'label' => 'Normal'],
                    'WATCH'     => ['bg' => '#eff6ff', 'border' => '#2563eb', 'text' => '#1e3a8a', 'badge' => '#2563eb', 'label' => 'Watch'],
                    'ALERT'     => ['bg' => '#fffbeb', 'border' => '#d97706', 'text' => '#78350f', 'badge' => '#d97706', 'label' => 'Alert'],
                    'ALARM'     => ['bg' => '#fff1f2', 'border' => '#e11d48', 'text' => '#881337', 'badge' => '#e11d48', 'label' => 'Alarm'],
                    'EMERGENCY' => ['bg' => '#1c0000', 'border' => '#ef4444', 'text' => '#fca5a5', 'badge' => '#ef4444', 'label' => 'Emergency'],
                    default     => ['bg' => '#f8f9fa', 'border' => '#6c757d', 'text' => '#374151', 'badge' => '#6c757d', 'label' => $h['phase']],
                };
                $isFirst = $i === 0;
                $isLast  = $i === count($ndmaHistory) - 1;
            ?>
            <div class="ndma-tl-col">
                <!-- Connector -->
                <div class="ndma-tl-connector">
                    <div class="ndma-tl-half" style="<?= $isFirst ? 'opacity:0' : '' ?>"></div>
                    <div class="ndma-tl-dot" style="background:<?= $phColor['border'] ?>;box-shadow:0 0 0 3px #fff,0 0 0 5px <?= $phColor['border'] ?>;"></div>
                    <div class="ndma-tl-half" style="<?= $isLast ? 'opacity:0' : '' ?>"></div>
                </div>
                <!-- Card -->
                <div class="ndma-tl-card" style="border-top:4px solid <?= $phColor['border'] ?>;background:<?= $phColor['bg'] ?>;">
                    <?php if ($isLast): ?><span class="ndma-tl-badge-current">Current</span><?php endif; ?>
                    <div class="ndma-tl-month"><?= htmlspecialchars($h['month']) ?></div>
                    <div class="ndma-tl-phase" style="color:<?= $phColor['badge'] ?>;"><?= htmlspecialchars($phColor['label']) ?></div>
                    <div class="ndma-tl-divider" style="border-color:<?= $phColor['border'] ?>33;"></div>
                    <div class="ndma-tl-stats">
                        <div class="ndma-tl-stat">
                            <span class="ndma-tl-stat-k" style="color:<?= $phColor['text'] ?>;">VCI</span>
                            <span class="ndma-tl-stat-v" style="color:<?= $phColor['text'] ?>;"><?= $h['vci'] !== null ? $h['vci'] : '—' ?></span>
                        </div>
                        <div class="ndma-tl-stat">
                            <span class="ndma-tl-stat-k" style="color:<?= $phColor['text'] ?>;">Rainfall</span>
                            <span class="ndma-tl-stat-v" style="color:<?= $phColor['text'] ?>;"><?= $h['rainfall_mm'] ?> mm</span>
                        </div>
                        <div class="ndma-tl-stat">
                            <span class="ndma-tl-stat-k" style="color:<?= $phColor['text'] ?>;">% of LTM</span>
                            <span class="ndma-tl-stat-v" style="color:<?= $phColor['text'] ?>;"><?= $h['rainfall_pct_ltm'] ?>%</span>
                        </div>
                        <div class="ndma-tl-stat">
                            <span class="ndma-tl-stat-k" style="color:<?= $phColor['text'] ?>;">Food FCS</span>
                            <span class="ndma-tl-stat-v" style="color:<?= $phColor['text'] ?>;"><?= $h['food_fcs'] ?> / 42</span>
                        </div>
                        <div class="ndma-tl-stat">
                            <span class="ndma-tl-stat-k" style="color:<?= $phColor['text'] ?>;">Water</span>
                            <span class="ndma-tl-stat-v" style="color:<?= $phColor['text'] ?>;"><?= $h['water_km'] ?> km</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Transition note -->
        <div class="ndma-transition-note">
            <strong>Key finding:</strong>
            The county transitioned from <span style="color:#16a34a;font-weight:600;">Normal</span> phase
            (November–December 2025) to
            <span style="color:#d97706;font-weight:600;">Alert</span> phase
            (January–February 2026), driven by a January rainfall collapse to
            <strong>0mm (33% of LTM)</strong> and rising water distances.
            February off-season rains (265% of LTM) provided temporary relief
            but vegetation remains below normal (VCI 24.19 vs normal 35).
        </div>

        <!-- Trend charts -->
        <div class="ndma-trend-grid mt-lg">
            <div class="card sci-chart-card">
                <h3 class="card-title">VCI (Vegetation) Trend</h3>
                <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Vegetation Condition Index (0–100 scale). Above 35 = healthy pasture. Below 35 = vegetation deficit. Red dashed line marks the normal threshold.</p>
                <div class="sci-canvas-wrap" style="height:220px;">
                    <canvas id="chartVCI"></canvas>
                </div>
                <div class="chart-insight">
                    <div class="chart-insight-title">Insight</div>
                    <p>Pasture health collapsed sharply: from a healthy <strong>65.2 in November 2025</strong> to a severe deficit of <strong>24.19 by February 2026</strong>, 31% below the minimum healthy threshold. Samburu East is worst affected. Livestock on deteriorating pasture lose body condition rapidly, raising mortality risk ahead of the Long Rains.</p>
                    <div class="chart-insight-action"><strong>Decision signal:</strong> Pre-position livestock feed supplements and consider controlled destocking in Samburu East. Do not wait for the phase to escalate to Alarm before acting.</div>
                </div>
            </div>
            <div class="card sci-chart-card">
                <h3 class="card-title">Rainfall % of Long-Term Mean</h3>
                <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">How much rain fell relative to the historical monthly average. 100% = normal. Below 50% = drought stress. Green bars = above normal; amber = below normal; red = severe deficit.</p>
                <div class="sci-canvas-wrap" style="height:220px;">
                    <canvas id="chartRainfallTrend"></canvas>
                </div>
                <div class="chart-insight">
                    <div class="chart-insight-title">Insight</div>
                    <p>January 2026 recorded <strong>zero rainfall</strong> (33% of LTM), the key trigger for the Normal-to-Alert phase transition. February's 265% LTM spike was off-season and irregular, not the start of the Long Rains (MAM). This creates a false confidence risk: ground conditions remain stressed despite the rainfall figure.</p>
                    <div class="chart-insight-action"><strong>Decision signal:</strong> Drought response should not be wound down based on the February spike. Monitor MAM onset closely. Off-season rains can cause premature planting, leading to crop failure if followed by dry conditions.</div>
                </div>
            </div>
            <div class="card sci-chart-card">
                <h3 class="card-title">Food Consumption Score Trend</h3>
                <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Household food access score (0–42 scale). Above 35 = acceptable. 21–35 = borderline hardship. Below 21 = poor access. Red dashed line marks the acceptable threshold at 35.</p>
                <div class="sci-canvas-wrap" style="height:220px;">
                    <canvas id="chartFCS"></canvas>
                </div>
                <div class="chart-insight">
                    <div class="chart-insight-title">Insight</div>
                    <p>Food access has been borderline for four consecutive months. January 2026 recorded the lowest score (<strong>30.8/42</strong>), reflecting the rainfall failure. While February improved slightly to <strong>35.4/42</strong>, 46% of sampled households still scored borderline and 1% poor. Pastoral households are most exposed, relying on livestock as both income and food.</p>
                    <div class="chart-insight-action"><strong>Decision signal:</strong> Food assistance programmes (cash transfers, food distribution) should remain active. Targeting should prioritise pastoral zones and female-headed households, who typically show the steepest FCS declines during drought.</div>
                </div>
            </div>
            <div class="card sci-chart-card">
                <h3 class="card-title">Distance to Water Source</h3>
                <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Average km livestock must walk to reach water. Normal baseline is 7 km. Rising distance = increasing drought stress on livestock and herder time. Red dashed line marks the 7 km normal baseline.</p>
                <div class="sci-canvas-wrap" style="height:220px;">
                    <canvas id="chartWater"></canvas>
                </div>
                <div class="chart-insight">
                    <div class="chart-insight-title">Insight</div>
                    <p>Water distances increased 35% from <strong>4 km (November–December)</strong> to <strong>5.4 km in January 2026</strong> as surface water dried up. Although still below the 7 km normal baseline, the rate of increase is a leading indicator, as water stress typically precedes livestock deaths by 4-6 weeks. February's slight easing (5 km) reflects the off-season rains.</p>
                    <div class="chart-insight-action"><strong>Decision signal:</strong> If distances exceed 6 km in any sub-county, activate emergency water trucking and prioritise borehole rehabilitation. Current figures suggest Samburu East is the highest-risk sub-county.</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- KMD Monthly Forecast History -->
<?php if (!empty($bulletins)): ?>
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>KMD Monthly Forecast History</h2>
            <p>Official Kenya Meteorological Department monthly forecasts for Samburu County, latest first. These are rainfall outlooks issued before each month, showing what KMD predicted would happen.</p>
        </div>

        <!-- KMD Outlook trend timeline -->
        <div class="kmd-tl-wrap">
            <?php
            $bulletinsReversed = array_reverse($bulletins);
            $kmdCount = count($bulletinsReversed);
            foreach ($bulletinsReversed as $i => $b):
                $tc = match($b['outlook_category'] ?? '') {
                    'below_average'        => ['border' => '#dc2626', 'bg' => '#fff1f2', 'text' => '#7f1d1d', 'badge' => '#dc2626'],
                    'near_to_below_normal' => ['border' => '#d97706', 'bg' => '#fffbeb', 'text' => '#78350f', 'badge' => '#d97706'],
                    'near_to_above_normal',
                    'above_average'        => ['border' => '#16a34a', 'bg' => '#f0fdf4', 'text' => '#14532d', 'badge' => '#16a34a'],
                    default                => ['border' => '#6c757d', 'bg' => '#f8f9fa', 'text' => '#374151', 'badge' => '#6c757d'],
                };
                $isFirst = $i === 0;
                $isLast  = $i === $kmdCount - 1;
            ?>
            <div class="kmd-tl-col">
                <div class="kmd-tl-connector">
                    <div class="kmd-tl-half" style="<?= $isFirst ? 'opacity:0' : '' ?>"></div>
                    <div class="kmd-tl-dot" style="background:<?= $tc['border'] ?>;box-shadow:0 0 0 3px #fff,0 0 0 5px <?= $tc['border'] ?>;"></div>
                    <div class="kmd-tl-half" style="<?= $isLast ? 'opacity:0' : '' ?>"></div>
                </div>
                <div class="kmd-tl-card" style="border-top:4px solid <?= $tc['border'] ?>;background:<?= $tc['bg'] ?>;">
                    <?php if ($isLast): ?><span class="kmd-tl-badge-latest">Latest</span><?php endif; ?>
                    <div class="kmd-tl-month"><?= htmlspecialchars($b['valid_period']) ?></div>
                    <div class="kmd-tl-label" style="color:<?= $tc['badge'] ?>;"><?= htmlspecialchars($b['outlook_label'] ?? '—') ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bulletin-timeline mt-lg">
        <?php foreach ($bulletins as $i => $b):
            $catClass = match($b['outlook_category'] ?? '') {
                'below_average'        => 'bul-dry',
                'near_to_below_normal' => 'bul-mixed',
                'near_to_above_normal',
                'above_average'        => 'bul-wet',
                default                => 'bul-mixed',
            };
            $isLatest = $i === 0;
        ?>
        <div class="bulletin-card <?= $catClass ?>">
            <div class="bulletin-card-header">
                <div class="bulletin-card-title">
                    <div class="bulletin-month">
                        <?= htmlspecialchars($b['valid_period']) ?>
                        <?php if ($isLatest): ?>
                        <span class="bulletin-latest-badge">Latest</span>
                        <?php endif; ?>
                    </div>
                    <div class="bulletin-outlook-label"><?= htmlspecialchars($b['outlook_label'] ?? '—') ?></div>
                </div>
                <div class="bulletin-updated">Updated: <?= htmlspecialchars($b['updated_at']) ?></div>
            </div>
            <div class="bulletin-card-body">
                <div class="bulletin-samburu-extract">
                    <div class="bulletin-extract-label">Samburu County: KMD Statement</div>
                    <p>"<?= htmlspecialchars($b['samburu_specific']) ?>"</p>
                </div>
                <div class="bulletin-meta-row">
                    <?php if (!empty($b['temperature_max_celsius']) || !empty($b['temperature_min_celsius'])): ?>
                    <div class="bulletin-meta-item">
                        <span class="bulletin-meta-key">Temperature</span>
                        <span class="bulletin-meta-val"><?php
                            $parts = [];
                            if (!empty($b['temperature_min_celsius'])) $parts[] = 'Min ' . $b['temperature_min_celsius'];
                            if (!empty($b['temperature_max_celsius'])) $parts[] = 'Max ' . $b['temperature_max_celsius'];
                            echo htmlspecialchars(implode(' · ', $parts));
                        ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($b['advisory'])): ?>
                    <div class="bulletin-meta-item">
                        <span class="bulletin-meta-key">Advisory</span>
                        <span class="bulletin-meta-val"><?= htmlspecialchars($b['advisory']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:var(--sp-md);text-align:center;">
            Source: Kenya Meteorological Department (KMD) monthly forecasts.
            <a href="https://meteo.go.ke/our-products/monthly-forecast/" target="_blank" rel="noopener" style="color:var(--clr-primary);">meteo.go.ke →</a>
        </p>
    </div>
</section>
<?php endif; ?>

<!-- Charts -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Bulletin Data Charts: 2021 to 2026</h2>
            <p>Charts built directly from quantitative values in official KMD and NDMA bulletins. Hover over bars for the exact source statement.</p>
        </div>

        <div class="sci-chart-note">
            <strong>Data source: Kenya Meteorological Department (KMD) only.</strong>
            Tercile probabilities extracted from KMD Seasonal Climate Outlook maps for the zone including Samburu.
            Rainfall actuals from KMD seasonal rainfall review bulletins (% of Long-Term Mean).
            SPI drought probabilities from KMD Drought Monitor seasonal outlooks.
            All values are from official KMD bulletins, MAM 2021 to MAM 2026.
        </div>

        <!-- Chart 1: Tercile Probabilities -->
        <div class="card sci-chart-card">
            <h3 class="card-title">KMD Seasonal Forecast: Tercile Probabilities</h3>
            <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Percentage probability assigned to each rainfall tercile (Above Normal / Near Normal / Below Normal) for the zone including Samburu. Bars sum to 100%. Equal chances = 33% each tercile.</p>
            <div class="sci-canvas-wrap" style="height:300px;">
                <canvas id="chartTercile"></canvas>
            </div>
            <div class="chart-insight">
                <div class="chart-insight-title">Insight</div>
                <p>Each bar shows what KMD predicted before the season. When the <strong style="color:#dc2626;">Below Normal</strong> bar is tallest, KMD anticipated drought-like conditions. Bars at 33% each mean equal probability with no strong signal, essentially a climatological guess. Dominant "Below Normal" forecasts in consecutive seasons are a systemic warning that the region is in a dry cycle.</p>
                <div class="chart-insight-action"><strong>Decision signal:</strong> When the Below Normal probability exceeds 40% for an upcoming season, government and NGO drought response plans should move from standby to activation, pre-positioning supplies before conditions deteriorate on the ground.</div>
            </div>
        </div>

        <!-- Chart 2: Rainfall Actuals % LTM -->
        <div class="card sci-chart-card mt-lg">
            <h3 class="card-title">Recorded Rainfall: % of Long-Term Mean</h3>
            <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Actual seasonal rainfall at reference stations as a percentage of the Long-Term Mean (LTM). Reference line at 100% = normal. Values well below 100% indicate drought conditions.</p>
            <div class="sci-canvas-wrap" style="height:300px;">
                <canvas id="chartRainfallActuals"></canvas>
            </div>
            <?php
            $mmOnly = array_filter($rainfallActuals, fn($r) => $r['percent_of_long_term_mean'] === null);
            if (!empty($mmOnly)): ?>
            <div class="sci-mm-table-wrap">
                <p class="sci-mm-note"><strong>Entries recorded in mm only</strong> (no % LTM available in bulletin):</p>
                <div class="sci-mm-grid">
                <?php foreach ($mmOnly as $r): ?>
                    <span class="sci-mm-pill" title="<?= htmlspecialchars($r['source_text']) ?>">
                        <?= htmlspecialchars($r['season']) ?> · <?= htmlspecialchars($r['station']) ?>, <?= $r['recorded_rainfall_mm'] !== null ? $r['recorded_rainfall_mm'].' mm' : '—' ?>
                    </span>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="chart-insight">
                <div class="chart-insight-title">Insight</div>
                <p>This chart reveals whether actual rainfall matched the historical norm. Seasons with bars well below 100% confirm real drought conditions on the ground. Comparing this against the Tercile Forecast chart above lets you evaluate <strong>KMD forecast accuracy</strong>: did a high "Below Normal" forecast probability correctly predict a below-100% LTM outcome? Repeated below-75% LTM seasons point to long-term pasture degradation, reducing carrying capacity for livestock even in "good" years.</p>
                <div class="chart-insight-action"><strong>Decision signal:</strong> Two or more consecutive seasons below 75% of LTM are the historical trigger for multi-agency drought declarations in Samburu County. Track seasonal sequences, not individual data points.</div>
            </div>
        </div>

        <!-- Chart 3: SPI Drought Probabilities -->
        <div class="card sci-chart-card mt-lg">
            <h3 class="card-title">Drought SPI Forecast Probabilities: OND Seasons</h3>
            <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Forecasted probability of SPI falling into Alert (SPI &lt; −0.09) or Alarm (SPI &lt; −0.98) thresholds. Dashed reference lines show climatological baselines (46% Alert · 16% Alarm). Bars show forecast midpoints.</p>
            <div class="sci-canvas-wrap" style="height:320px;">
                <canvas id="chartSPI"></canvas>
            </div>
            <div class="chart-insight">
                <div class="chart-insight-title">Insight</div>
                <p>The Standardised Precipitation Index (SPI) measures rainfall deviation from the long-term average. When KMD's <strong style="color:#d97706;">Alert forecast</strong> exceeds the 46% climatological baseline, drought conditions are more likely than normal for that season. When the <strong style="color:#dc2626;">Alarm forecast</strong> exceeds 16%, there is heightened risk of severe multi-month drought. Bars significantly above the dashed baselines signal an active drought outlook.</p>
                <div class="chart-insight-action"><strong>Decision signal:</strong> An Alarm probability above 20–25% should trigger emergency pre-positioning of food, water, and veterinary supplies. Cross-reference these forecasts with the NDMA monthly ground observations above to confirm whether forecast drought is materialising on the ground.</div>
            </div>
        </div>

    </div>
</section>

<!-- Seasonal Source Table -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Seasonal Forecast Record: Full Table</h2>
            <p>All <?= count($seasons) ?> bulletin entries extracted from official KMD seasonal forecasts, 2021–2026. Source text available on hover.</p>
        </div>
        <div class="table-wrap">
            <table class="data-table sci-table">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Sub-Region</th>
                        <th>Rainfall Outlook</th>
                        <th>Temperature</th>
                        <th>Vegetation / Pasture</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($seasons as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['season_name']) ?></strong></td>
                    <td style="font-size:var(--fs-xs);color:var(--clr-text-muted);"><?= htmlspecialchars($row['source_region']) ?></td>
                    <td>
                        <?php if (!empty($row['rainfall']['qualitative_category'])): ?>
                        <span class="sci-cat-badge sci-cat-rain-<?= preg_replace('/[^a-z]/', '-', strtolower($row['rainfall']['qualitative_category'])) ?>"
                              title="<?= htmlspecialchars($row['rainfall']['source_text'] ?? '') ?>">
                            <?= htmlspecialchars($row['rainfall']['qualitative_category']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:var(--fs-xs);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['temperature']['qualitative_category'])): ?>
                        <span class="sci-cat-badge sci-cat-temp-<?= str_contains(strtolower($row['temperature']['qualitative_category']), 'cool') ? 'cool' : (str_contains(strtolower($row['temperature']['qualitative_category']), 'warm') || str_contains(strtolower($row['temperature']['qualitative_category']), 'above') ? 'warm' : 'near') ?>"
                              title="<?= htmlspecialchars($row['temperature']['source_text'] ?? '') ?>">
                            <?= htmlspecialchars($row['temperature']['qualitative_category']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:var(--fs-xs);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['vegetation_pasture']['qualitative_category'])): ?>
                        <span title="<?= htmlspecialchars($row['vegetation_pasture']['source_text'] ?? '') ?>"
                              style="font-size:var(--fs-xs);cursor:help;">
                            <?= htmlspecialchars($row['vegetation_pasture']['qualitative_category']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:var(--fs-xs);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:var(--sp-sm);">Hover over rainfall/temperature badges or vegetation text to read the exact KMD bulletin source statement.</p>
    </div>
</section>

<!-- Data Sources -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Data Sources</h2>
        </div>
        <div class="grid grid-2 grid-auto">
            <div class="card">
                <h4 class="card-title">Kenya Meteorological Department (KMD)</h4>
                <p class="text-muted" style="font-size:var(--fs-sm);margin:var(--sp-sm) 0;">Provides seasonal rainfall and temperature outlooks (monthly and seasonal). Data extracted from official KMD Seasonal Climate Outlook bulletins, MAM 2021 – MAM 2026.</p>
                <a href="<?= htmlspecialchars($liveKmdLink) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">View Latest KMD Bulletin →</a>
            </div>
            <div class="card">
                <h4 class="card-title">National Drought Management Authority (NDMA)</h4>
                <p class="text-muted" style="font-size:var(--fs-sm);margin:var(--sp-sm) 0;">Provides current drought phase, food security, livestock condition, and vegetation status. Data from NDMA Samburu County Drought Bulletin, <?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?>.</p>
                <a href="<?= htmlspecialchars($liveNdmaLink) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">View Latest NDMA Bulletin →</a>
            </div>
        </div>
    </div>
</section>

<style>
/* ── Bulletin timeline cards ──────────────────── */
.bulletin-timeline {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--sp-lg);
}
.bulletin-card {
    background: var(--clr-surface); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm); overflow: hidden; border-top: 4px solid;
    transition: transform .2s, box-shadow .2s;
}
.bulletin-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.bul-dry   { border-color: var(--clr-danger); }
.bul-mixed { border-color: var(--clr-warning); }
.bul-wet   { border-color: var(--clr-primary-light); }
.bulletin-card-header {
    display: flex; align-items: flex-start; gap: var(--sp-md);
    padding: var(--sp-md) var(--sp-lg) var(--sp-sm);
    border-bottom: 1px solid var(--clr-border-light);
}
.bulletin-month-icon { font-size: 1.6rem; flex-shrink: 0; line-height: 1; margin-top: 2px; }
.bulletin-card-title { flex: 1; }
.bulletin-month {
    font-size: var(--fs-md); font-weight: var(--fw-bold); color: var(--clr-text);
    display: flex; align-items: center; gap: var(--sp-xs); flex-wrap: wrap;
}
.bulletin-latest-badge {
    font-size: var(--fs-xs); padding: 2px 8px; border-radius: var(--radius-pill);
    background: var(--clr-primary); color: #fff; font-weight: var(--fw-semi);
}
.bulletin-outlook-label { font-size: var(--fs-sm); color: var(--clr-text-muted); margin-top: 2px; }
.bulletin-updated { font-size: var(--fs-xs); color: var(--clr-text-muted); white-space: nowrap; flex-shrink: 0; }
.bulletin-card-body { padding: var(--sp-md) var(--sp-lg) var(--sp-lg); }
.bulletin-samburu-extract {
    background: var(--clr-bg); border-left: 3px solid var(--clr-primary-light);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    padding: var(--sp-sm) var(--sp-md); margin-bottom: var(--sp-md);
}
.bul-dry   .bulletin-samburu-extract { border-color: var(--clr-danger); }
.bul-mixed .bulletin-samburu-extract { border-color: var(--clr-warning); }
.bulletin-extract-label {
    font-size: var(--fs-xs); font-weight: var(--fw-semi); color: var(--clr-primary);
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: var(--sp-xs);
}
.bulletin-samburu-extract p {
    font-size: var(--fs-sm); color: var(--clr-text); font-style: italic;
    line-height: 1.6; margin: 0;
}
.bulletin-meta-row { display: flex; flex-direction: column; gap: var(--sp-sm); }
.bulletin-meta-item { display: flex; flex-direction: column; gap: 2px; }
.bulletin-meta-key {
    font-size: var(--fs-xs); font-weight: var(--fw-semi); color: var(--clr-text-muted);
    text-transform: uppercase; letter-spacing: .05em;
}
.bulletin-meta-val { font-size: var(--fs-sm); color: var(--clr-text); line-height: 1.5; }

/* Status cards */
.grid-4 { grid-template-columns: repeat(4, 1fr); }
@media (max-width: 900px) { .grid-4 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .grid-4 { grid-template-columns: 1fr; } }

.sci-stat-card { padding: var(--sp-lg); text-align: center; }
.sci-stat-label {
    font-size: var(--fs-xs); text-transform: uppercase; letter-spacing: .06em;
    color: var(--clr-text-muted); font-weight: var(--fw-semi); margin-bottom: var(--sp-xs);
}
.sci-stat-value {
    font-size: var(--fs-2xl); font-weight: var(--fw-extra); line-height: 1.1;
    margin-bottom: 4px;
}
.sci-stat-sub { font-size: var(--fs-xs); color: var(--clr-text-muted); line-height: 1.4; }

/* Vegetation sub-county */
.sci-veg-item {
    padding: var(--sp-md);
    border-radius: var(--radius-md);
    border-left: 3px solid;
    font-size: var(--fs-sm);
    line-height: 1.6;
}
.sci-veg-item p { margin: 0; color: var(--clr-text-muted); }
.sci-veg-label { font-weight: var(--fw-semi); margin-bottom: 4px; }
.sci-veg-stress   { background: var(--clr-danger-light);  border-color: var(--clr-danger);  }
.sci-veg-stress   .sci-veg-label { color: var(--clr-danger); }
.sci-veg-moderate { background: var(--clr-warning-light); border-color: var(--clr-warning); }
.sci-veg-moderate .sci-veg-label { color: var(--clr-warning); }
.sci-veg-normal   { background: var(--clr-success-light); border-color: var(--clr-success); }
.sci-veg-normal   .sci-veg-label { color: var(--clr-success); }

/* Chart cards */
.sci-chart-note {
    background: var(--clr-bg);
    border: 1px solid var(--clr-border-light);
    border-left: 4px solid var(--clr-primary-light);
    border-radius: var(--radius-md);
    padding: var(--sp-md) var(--sp-lg);
    font-size: var(--fs-xs);
    color: var(--clr-text-muted);
    margin-bottom: var(--sp-lg);
    line-height: 1.6;
}
.sci-chart-card { padding: var(--sp-xl); }
.sci-canvas-wrap { position: relative; height: 280px; }

/* mm-only entries table */
.sci-mm-table-wrap {
    margin-top: var(--sp-md);
    padding-top: var(--sp-md);
    border-top: 1px solid var(--clr-border-light);
}
.sci-mm-note {
    font-size: var(--fs-xs);
    color: var(--clr-text-muted);
    margin-bottom: var(--sp-xs);
}
.sci-mm-grid {
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-xs);
}
.sci-mm-pill {
    display: inline-block;
    font-size: 0.68rem;
    padding: 3px 9px;
    border-radius: var(--radius-pill);
    background: var(--clr-bg-alt);
    border: 1px solid var(--clr-border-light);
    color: var(--clr-text-muted);
    cursor: help;
    white-space: nowrap;
}

/* Table category badges */
.sci-cat-badge {
    display: inline-block;
    font-size: 0.68rem;
    padding: 2px 8px;
    border-radius: var(--radius-pill);
    font-weight: var(--fw-semi);
    cursor: help;
    white-space: nowrap;
}
/* Rainfall colours */
.sci-cat-badge[class*="sci-cat-rain-above"],
.sci-cat-badge[class*="sci-cat-rain-enhanced"],
.sci-cat-badge[class*="sci-cat-rain-slightly-wetter"],
.sci-cat-badge[class*="sci-cat-rain-near-average-to-above"] {
    background: var(--clr-success-light); color: var(--clr-success);
}
.sci-cat-badge[class*="sci-cat-rain-near-to-above"] {
    background: #d4edda; color: #155724;
}
.sci-cat-badge[class*="sci-cat-rain-near"],
.sci-cat-badge[class*="sci-cat-rain-near-"] {
    background: var(--clr-info-light); color: var(--clr-info);
}
.sci-cat-badge[class*="sci-cat-rain-depressed"],
.sci-cat-badge[class*="sci-cat-rain-below"],
.sci-cat-badge[class*="sci-cat-rain-highly"] {
    background: var(--clr-danger-light); color: var(--clr-danger);
}
/* Temperature colours */
.sci-cat-badge.sci-cat-temp-warm { background: #fff3cd; color: #856404; }
.sci-cat-badge.sci-cat-temp-cool { background: var(--clr-info-light); color: var(--clr-info); }
.sci-cat-badge.sci-cat-temp-near { background: var(--clr-primary-pale); color: var(--clr-primary); }

/* ── NDMA timeline (equal-grid) ─────────────────── */
.ndma-tl-wrap {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--sp-md);
}
.ndma-tl-col { display: flex; flex-direction: column; }
.ndma-tl-connector {
    display: flex; align-items: center; height: 36px;
    margin-bottom: var(--sp-xs);
}
.ndma-tl-half {
    flex: 1; height: 2px; background: #cbd5e1;
    transition: opacity .2s;
}
.ndma-tl-dot {
    width: 18px; height: 18px; border-radius: 50%;
    flex-shrink: 0; z-index: 1;
}
.ndma-tl-card {
    flex: 1; border-radius: var(--radius-lg);
    padding: var(--sp-lg); position: relative;
    min-height: 210px; display: flex; flex-direction: column;
    box-shadow: var(--shadow-sm);
}
.ndma-tl-badge-current {
    position: absolute; top: -10px; right: var(--sp-md);
    background: var(--clr-primary); color: #fff;
    font-size: 0.65rem; font-weight: var(--fw-semi);
    padding: 3px 10px; border-radius: var(--radius-pill);
    letter-spacing: .04em; text-transform: uppercase;
}
.ndma-tl-month {
    font-size: 0.65rem; font-weight: var(--fw-bold);
    text-transform: uppercase; letter-spacing: .08em;
    color: #64748b; margin-bottom: var(--sp-xs);
}
.ndma-tl-phase {
    font-size: var(--fs-lg); font-weight: var(--fw-extra);
    margin-bottom: var(--sp-sm); line-height: 1.1;
}
.ndma-tl-divider {
    border: none; border-top: 1px solid;
    margin: var(--sp-sm) 0;
}
.ndma-tl-stats { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.ndma-tl-stat {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: var(--sp-xs);
}
.ndma-tl-stat-k {
    font-size: var(--fs-xs); font-weight: var(--fw-medium);
    white-space: nowrap; opacity: 0.8;
}
.ndma-tl-stat-v {
    font-size: var(--fs-xs); font-weight: var(--fw-bold);
    text-align: right; white-space: nowrap;
}

/* ── KMD timeline (equal-grid) ──────────────────── */
.kmd-tl-wrap {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--sp-md);
    margin-bottom: var(--sp-xl);
}
.kmd-tl-col { display: flex; flex-direction: column; }
.kmd-tl-connector {
    display: flex; align-items: center; height: 32px;
    margin-bottom: var(--sp-xs);
}
.kmd-tl-half {
    flex: 1; height: 2px; background: #cbd5e1;
}
.kmd-tl-dot {
    width: 16px; height: 16px; border-radius: 50%;
    flex-shrink: 0; z-index: 1;
}
.kmd-tl-card {
    flex: 1; border-radius: var(--radius-lg);
    padding: var(--sp-md) var(--sp-lg);
    position: relative; min-height: 80px;
    display: flex; flex-direction: column; justify-content: center;
    box-shadow: var(--shadow-sm);
}
.kmd-tl-badge-latest {
    position: absolute; top: -10px; right: var(--sp-md);
    background: var(--clr-primary); color: #fff;
    font-size: 0.65rem; font-weight: var(--fw-semi);
    padding: 3px 10px; border-radius: var(--radius-pill);
    letter-spacing: .04em; text-transform: uppercase;
}
.kmd-tl-month {
    font-size: 0.65rem; font-weight: var(--fw-bold);
    text-transform: uppercase; letter-spacing: .08em;
    color: #64748b; margin-bottom: 4px;
}
.kmd-tl-label {
    font-size: var(--fs-md); font-weight: var(--fw-extra);
    line-height: 1.25;
}

/* ── NDMA-stat rows (in current status cards) ───── */
.ndma-stat-row {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: var(--sp-sm);
}
.ndma-stat-key {
    font-size: var(--fs-xs); color: var(--clr-text-muted);
    font-weight: var(--fw-medium); white-space: nowrap;
}
.ndma-stat-val {
    font-size: var(--fs-xs); color: var(--clr-text);
    font-weight: var(--fw-semi); text-align: right;
}

/* ── Transition note ────────────────────────────── */
.ndma-transition-note {
    background: var(--clr-info-light);
    border-left: 4px solid var(--clr-info);
    border-radius: var(--radius-md);
    padding: var(--sp-md) var(--sp-lg);
    font-size: var(--fs-sm); color: var(--clr-text);
    line-height: 1.7; margin-top: var(--sp-lg);
}

/* ── NDMA trend chart grid ──────────────────────── */
.ndma-trend-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: var(--sp-lg);
}

/* ── Chart insight panels ───────────────────────── */
.chart-insight {
    margin-top: var(--sp-lg);
    padding: var(--sp-md) var(--sp-lg);
    background: #f0f9ff;
    border-left: 4px solid #0ea5e9;
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    font-size: var(--fs-sm); color: var(--clr-text);
    line-height: 1.75;
}
.chart-insight-title {
    font-size: 0.65rem; font-weight: var(--fw-bold);
    text-transform: uppercase; letter-spacing: .1em;
    color: #0369a1; margin-bottom: var(--sp-xs);
}
.chart-insight p { margin: 0 0 var(--sp-xs); }
.chart-insight-action {
    margin-top: var(--sp-sm);
    font-size: var(--fs-xs); color: #334155;
    padding-top: var(--sp-xs);
    border-top: 1px solid #bae6fd;
    line-height: 1.6;
}

/* ── Responsive: stack both timelines on mobile ── */
@media (max-width: 720px) {
    .ndma-tl-wrap,
    .kmd-tl-wrap  { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .ndma-tl-wrap,
    .kmd-tl-wrap  { grid-template-columns: 1fr; }
    .ndma-tl-connector,
    .kmd-tl-connector { display: none; }
}

/* Table */
.sci-table td { vertical-align: top; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Raw data from PHP
    const tercileData    = <?= json_encode($tercileChart,    JSON_UNESCAPED_UNICODE) ?>;
    const rainfallActual = <?= json_encode($rainfallActuals, JSON_UNESCAPED_UNICODE) ?>;
    const spiData        = <?= json_encode($spiChart,        JSON_UNESCAPED_UNICODE) ?>;

    // Colour palette
    const C_RED   = '#dc2626';
    const C_AMBER = '#d97706';
    const C_BLUE  = '#2563eb';
    const C_GREEN = '#16a34a';
    const C_GREY  = '#64748b';
    const C_ALERT = '#d97706';   // amber: Alert phase
    const C_ALARM = '#dc2626';   // red:   Alarm phase
    const C_ALERT_LIGHT = '#fef3c7';
    const C_ALARM_LIGHT = '#fee2e2';

    // Wrap source_text to ~55 chars per line
    function wrapText(txt, w) {
        if (!txt) return [];
        const words = txt.split(' ');
        const lines = [];
        let cur = '';
        words.forEach(word => {
            if ((cur + ' ' + word).trim().length > w) {
                lines.push(cur.trim());
                cur = word;
            } else {
                cur = cur ? cur + ' ' + word : word;
            }
        });
        if (cur) lines.push(cur.trim());
        return lines;
    }

    // Shared base options
    function baseOpts(yLabel, yMin, yMax) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: { font: { size: 10 }, maxRotation: 45, autoSkip: false },
                    grid: { display: false }
                },
                y: {
                    min: yMin,
                    max: yMax,
                    title: { display: true, text: yLabel, font: { size: 11 } },
                    grid: { color: '#e2e8f0' }
                }
            }
        };
    }

    // CHART 1: Tercile Probabilities (stacked bar)
    new Chart(document.getElementById('chartTercile'), {
        type: 'bar',
        data: {
            labels: tercileData.map(d => [d.season, d.region]),
            datasets: [
                {
                    label: 'Below Normal',
                    data: tercileData.map(d => d.below_normal_percent),
                    backgroundColor: C_RED,
                    stack: 'tercile',
                    borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 4, bottomRight: 4 }
                },
                {
                    label: 'Near Normal',
                    data: tercileData.map(d => d.near_normal_percent),
                    backgroundColor: C_GREY,
                    stack: 'tercile',
                    borderRadius: 0
                },
                {
                    label: 'Above Normal',
                    data: tercileData.map(d => d.above_normal_percent),
                    backgroundColor: C_GREEN,
                    stack: 'tercile',
                    borderRadius: { topLeft: 4, topRight: 4, bottomLeft: 0, bottomRight: 0 }
                }
            ]
        },
        options: {
            ...baseOpts('Probability (%)', 0, 100),
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { size: 11 }, boxWidth: 14, padding: 14 }
                },
                tooltip: {
                    callbacks: {
                        title(items) {
                            const d = tercileData[items[0].dataIndex];
                            return d.season + ', ' + d.region;
                        },
                        label(item) {
                            return ' ' + item.dataset.label + ': ' + item.parsed.y + '%';
                        },
                        afterBody(items) {
                            const d = tercileData[items[0].dataIndex];
                            return ['', 'Bulletin: ' + d.source_text];
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    ticks: { font: { size: 10 }, maxRotation: 45, autoSkip: false },
                    grid: { display: false }
                },
                y: {
                    stacked: true,
                    min: 0, max: 100,
                    title: { display: true, text: 'Probability (%)', font: { size: 11 } },
                    grid: { color: '#e2e8f0' }
                }
            }
        }
    });

    // CHART 2: Rainfall Actuals (% of LTM)
    const ltmData = rainfallActual.filter(d => d.percent_of_long_term_mean !== null);

    function ltmColor(v) {
        if (v >= 110) return C_GREEN;
        if (v >= 75)  return C_BLUE;
        if (v >= 30)  return C_AMBER;
        return C_RED;
    }

    new Chart(document.getElementById('chartRainfallActuals'), {
        type: 'bar',
        data: {
            labels: ltmData.map(d => [d.season, d.station]),
            datasets: [
                {
                    label: '% of Long-Term Mean',
                    data: ltmData.map(d => d.percent_of_long_term_mean),
                    backgroundColor: ltmData.map(d => ltmColor(d.percent_of_long_term_mean)),
                    borderColor:     ltmData.map(d => ltmColor(d.percent_of_long_term_mean)),
                    borderWidth: 0,
                    borderRadius: 4
                },
                {
                    label: 'Normal (100%)',
                    type: 'line',
                    data: Array(ltmData.length).fill(100),
                    borderColor: C_GREY,
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                }
            ]
        },
        options: {
            ...baseOpts('% of Long-Term Mean', 0, 250),
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { size: 11 }, boxWidth: 14, padding: 14 }
                },
                tooltip: {
                    callbacks: {
                        title(items) {
                            const d = ltmData[items[0].dataIndex];
                            return d.season + ' · ' + d.station;
                        },
                        label(item) {
                            if (item.datasetIndex === 1) return ' Normal: 100%';
                            const d = ltmData[item.dataIndex];
                            const lines = [' % LTM: ' + d.percent_of_long_term_mean + '%'];
                            if (d.recorded_rainfall_mm !== null) lines.push(' Recorded: ' + d.recorded_rainfall_mm + ' mm');
                            return lines;
                        },
                        afterBody(items) {
                            if (items[0].datasetIndex !== 0) return [];
                            return ['', ...wrapText(ltmData[items[0].dataIndex].source_text, 55)];
                        }
                    }
                }
            }
        }
    });

    // CHART 3: Drought SPI Probabilities
    // Split into Alert and Alarm series (same season order)
    const spiSeasons = [...new Set(spiData.map(d => d.season))];
    const alertData  = spiSeasons.map(s => spiData.find(d => d.season === s && d.metric.startsWith('SPI < -0.09')));
    const alarmData  = spiSeasons.map(s => spiData.find(d => d.season === s && d.metric.startsWith('SPI < -0.98')));

    new Chart(document.getElementById('chartSPI'), {
        type: 'bar',
        data: {
            labels: spiSeasons,
            datasets: [
                {
                    label: 'Alert Forecast (SPI < −0.09)',
                    data: alertData.map(d => d ? d.forecast_midpoint_pct : null),
                    backgroundColor: C_ALERT,
                    borderRadius: 4,
                    barPercentage: 0.4,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Alarm Forecast (SPI < −0.98)',
                    data: alarmData.map(d => d ? d.forecast_midpoint_pct : null),
                    backgroundColor: C_ALARM,
                    borderRadius: 4,
                    barPercentage: 0.4,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Alert Baseline (46%)',
                    type: 'line',
                    data: Array(spiSeasons.length).fill(46),
                    borderColor: C_ALERT,
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                },
                {
                    label: 'Alarm Baseline (16%)',
                    type: 'line',
                    data: Array(spiSeasons.length).fill(16),
                    borderColor: C_ALARM,
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                }
            ]
        },
        options: {
            ...baseOpts('Probability (%)', 0, 100),
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { size: 11 }, boxWidth: 14, padding: 10 }
                },
                tooltip: {
                    callbacks: {
                        title(items) { return items[0].label; },
                        label(item) {
                            if (item.datasetIndex >= 2) {
                                return ' ' + item.dataset.label;
                            }
                            const arr   = item.datasetIndex === 0 ? alertData : alarmData;
                            const d     = arr[item.dataIndex];
                            const lines = [' Forecast: ' + item.parsed.y + '% (bulletin: ' + (d ? d.forecasted_chance_percent_text : '—') + ')'];
                            return lines;
                        },
                        afterBody(items) {
                            const idx = items[0].dataIndex;
                            const ds  = items[0].datasetIndex;
                            if (ds >= 2) return [];
                            const arr = ds === 0 ? alertData : alarmData;
                            const d   = arr[idx];
                            if (!d) return [];
                            return ['', ...wrapText(d.source_text, 55)];
                        }
                    }
                }
            }
        }
    });

    // ── NDMA TREND CHARTS ──────────────────────────────────────────

    const ndmaHistory = <?= json_encode($ndmaHistory, JSON_UNESCAPED_UNICODE) ?>;

    if (ndmaHistory && ndmaHistory.length) {
        const months      = ndmaHistory.map(d => d.month);
        const vciVals     = ndmaHistory.map(d => d.vci);
        const rainPct     = ndmaHistory.map(d => d.rainfall_pct_ltm);
        const fcsVals     = ndmaHistory.map(d => d.food_fcs);
        const waterVals   = ndmaHistory.map(d => d.water_km);

        const gridLines = { color: 'rgba(0,0,0,.06)' };
        const baseFont  = { family: 'Inter, sans-serif', size: 11 };

        function phaseColor(phase) {
            const map = { Normal:'#16a34a', Alert:'#d97706', Alarm:'#dc2626', Emergency:'#7f1d1d' };
            return map[phase] || '#6c757d';
        }
        const pointColors = ndmaHistory.map(d => phaseColor(d.phase));

        // ── VCI Chart ──────────────────────────────────────────────
        const ctxVCI = document.getElementById('chartVCI');
        if (ctxVCI) {
            new Chart(ctxVCI, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'VCI',
                        data: vciVals,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: pointColors,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.35,
                        fill: true,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        annotation: {},
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const v = ctx.parsed.y;
                                    return v === null ? 'No data' : `VCI: ${v} (Normal ≥ 35)`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { font: baseFont }, grid: gridLines },
                        y: {
                            min: 0, max: 100,
                            ticks: { font: baseFont },
                            grid: gridLines,
                            title: { display: true, text: 'VCI (0–100)', font: baseFont }
                        }
                    }
                },
                plugins: [{
                    id: 'vciNormalLine',
                    afterDraw(chart) {
                        const { ctx, chartArea, scales } = chart;
                        if (!chartArea) return;
                        const y35 = scales.y.getPixelForValue(35);
                        ctx.save();
                        ctx.setLineDash([5, 4]);
                        ctx.strokeStyle = '#dc2626';
                        ctx.lineWidth = 1.5;
                        ctx.beginPath();
                        ctx.moveTo(chartArea.left, y35);
                        ctx.lineTo(chartArea.right, y35);
                        ctx.stroke();
                        ctx.fillStyle = '#dc2626';
                        ctx.font = '10px Inter, sans-serif';
                        ctx.fillText('Normal threshold (35)', chartArea.left + 4, y35 - 4);
                        ctx.restore();
                    }
                }]
            });
        }

        // ── Rainfall % LTM Chart ───────────────────────────────────
        const ctxRain = document.getElementById('chartRainfallTrend');
        if (ctxRain) {
            new Chart(ctxRain, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: '% of LTM',
                        data: rainPct,
                        backgroundColor: rainPct.map(v => v >= 100 ? 'rgba(22,163,74,.75)' : v >= 50 ? 'rgba(217,119,6,.75)' : 'rgba(220,38,38,.75)'),
                        borderRadius: 5,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `${ctx.parsed.y}% of LTM` } }
                    },
                    scales: {
                        x: { ticks: { font: baseFont }, grid: { display: false } },
                        y: {
                            ticks: { font: baseFont, callback: v => v + '%' },
                            grid: gridLines,
                            title: { display: true, text: '% of Long-Term Mean', font: baseFont }
                        }
                    }
                },
                plugins: [{
                    id: 'rainNormalLine',
                    afterDraw(chart) {
                        const { ctx, chartArea, scales } = chart;
                        if (!chartArea) return;
                        const y100 = scales.y.getPixelForValue(100);
                        ctx.save();
                        ctx.setLineDash([5, 4]);
                        ctx.strokeStyle = '#16a34a';
                        ctx.lineWidth = 1.5;
                        ctx.beginPath();
                        ctx.moveTo(chartArea.left, y100);
                        ctx.lineTo(chartArea.right, y100);
                        ctx.stroke();
                        ctx.fillStyle = '#16a34a';
                        ctx.font = '10px Inter, sans-serif';
                        ctx.fillText('Normal (100%)', chartArea.left + 4, y100 - 4);
                        ctx.restore();
                    }
                }]
            });
        }

        // ── Food FCS Chart ─────────────────────────────────────────
        const ctxFCS = document.getElementById('chartFCS');
        if (ctxFCS) {
            new Chart(ctxFCS, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Food FCS',
                        data: fcsVals,
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(8,145,178,.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: pointColors,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.35,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `FCS: ${ctx.parsed.y} / 42` } }
                    },
                    scales: {
                        x: { ticks: { font: baseFont }, grid: gridLines },
                        y: {
                            min: 0, max: 42,
                            ticks: { font: baseFont },
                            grid: gridLines,
                            title: { display: true, text: 'FCS (0–42)', font: baseFont }
                        }
                    }
                },
                plugins: [{
                    id: 'fcsZones',
                    afterDraw(chart) {
                        const { ctx, chartArea, scales } = chart;
                        if (!chartArea) return;
                        const y35 = scales.y.getPixelForValue(35);
                        const y21 = scales.y.getPixelForValue(21);
                        ctx.save();
                        // Acceptable zone line
                        ctx.setLineDash([5, 4]);
                        ctx.strokeStyle = '#16a34a'; ctx.lineWidth = 1.5;
                        ctx.beginPath(); ctx.moveTo(chartArea.left, y35); ctx.lineTo(chartArea.right, y35); ctx.stroke();
                        ctx.fillStyle = '#16a34a'; ctx.font = '10px Inter, sans-serif';
                        ctx.fillText('Acceptable (35)', chartArea.left + 4, y35 - 4);
                        // Borderline zone line
                        ctx.strokeStyle = '#d97706';
                        ctx.beginPath(); ctx.moveTo(chartArea.left, y21); ctx.lineTo(chartArea.right, y21); ctx.stroke();
                        ctx.fillStyle = '#d97706';
                        ctx.fillText('Borderline (21)', chartArea.left + 4, y21 - 4);
                        ctx.restore();
                    }
                }]
            });
        }

        // ── Water Distance Chart ────────────────────────────────────
        const ctxWater = document.getElementById('chartWater');
        if (ctxWater) {
            new Chart(ctxWater, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Water Distance (km)',
                        data: waterVals,
                        borderColor: '#6f42c1',
                        backgroundColor: 'rgba(111,66,193,.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: pointColors,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        tension: 0.35,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => `${ctx.parsed.y} km to water` } }
                    },
                    scales: {
                        x: { ticks: { font: baseFont }, grid: gridLines },
                        y: {
                            min: 0,
                            ticks: { font: baseFont, callback: v => v + ' km' },
                            grid: gridLines,
                            title: { display: true, text: 'Distance (km)', font: baseFont }
                        }
                    }
                },
                plugins: [{
                    id: 'waterNormalLine',
                    afterDraw(chart) {
                        const { ctx, chartArea, scales } = chart;
                        if (!chartArea) return;
                        const y7 = scales.y.getPixelForValue(7);
                        ctx.save();
                        ctx.setLineDash([5, 4]);
                        ctx.strokeStyle = '#16a34a'; ctx.lineWidth = 1.5;
                        ctx.beginPath(); ctx.moveTo(chartArea.left, y7); ctx.lineTo(chartArea.right, y7); ctx.stroke();
                        ctx.fillStyle = '#16a34a'; ctx.font = '10px Inter, sans-serif';
                        ctx.fillText('Normal (7 km)', chartArea.left + 4, y7 - 4);
                        ctx.restore();
                    }
                }]
            });
        }
    }

})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
