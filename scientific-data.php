<?php
/**
 * Samburu EWS — Scientific Data
 *
 * Seasonal KMD forecast history, trend charts, and current NDMA status
 * for Samburu County — all sourced from real bulletin data.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';
require __DIR__ . '/includes/Db.php';

$pageTitle   = 'Scientific Data';
$kmd         = DataRepository::load('kmd_summary.json');
$ndma        = DataRepository::load('ndma_latest.json');
$seasonal    = DataRepository::load('kmd_seasonal.json');

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

<!-- ── Hero ─────────────────────────────────────────────────────── -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Scientific Data</h1>
        <p>Kenya Meteorological Department seasonal forecasts and NDMA drought assessments for Samburu County — extracted from official bulletins, 2021–2026.</p>
    </div>
</section>

<!-- ── Current Status Cards (from NDMA latest) ───────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Current Drought Status — Samburu County</h2>
            <p>From NDMA Bulletin (<?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?>). Phase: <strong><?= htmlspecialchars($ndma['phase'] ?? '—') ?></strong> — <?= htmlspecialchars($ndma['phase_justification'] ?? '') ?></p>
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
                <div class="sci-stat-sub">Normal: <?= $wa['normal_distance_km'] ?? '—' ?> km — closer than average</div>
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

<!-- ── Charts ────────────────────────────────────────────────────── -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Bulletin Data Charts — 2021 to 2026</h2>
            <p>Charts built directly from quantitative values in official KMD and NDMA bulletins. Hover over bars for the exact source statement.</p>
        </div>

        <div class="sci-chart-note">
            <strong>Data sources:</strong>
            Tercile probabilities extracted from KMD Seasonal Climate Outlook maps (Zone incl. Samburu).
            Rainfall actuals from KMD seasonal rainfall review bulletins (% of Long-Term Mean).
            SPI drought probabilities from KMD Drought Monitor seasonal outlooks.
        </div>

        <!-- Chart 1 — Tercile Probabilities -->
        <div class="card sci-chart-card">
            <h3 class="card-title">KMD Seasonal Forecast — Tercile Probabilities</h3>
            <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Percentage probability assigned to each rainfall tercile (Above Normal / Near Normal / Below Normal) for the zone including Samburu. Bars sum to 100%. Equal chances = 33% each tercile.</p>
            <div class="sci-canvas-wrap" style="height:300px;">
                <canvas id="chartTercile"></canvas>
            </div>
        </div>

        <!-- Chart 2 — Rainfall Actuals % LTM -->
        <div class="card sci-chart-card mt-lg">
            <h3 class="card-title">Recorded Rainfall — % of Long-Term Mean</h3>
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
                        <?= htmlspecialchars($r['season']) ?> · <?= htmlspecialchars($r['station']) ?> — <?= $r['recorded_rainfall_mm'] !== null ? $r['recorded_rainfall_mm'].' mm' : '—' ?>
                    </span>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Chart 3 — SPI Drought Probabilities -->
        <div class="card sci-chart-card mt-lg">
            <h3 class="card-title">Drought SPI Forecast Probabilities — OND Seasons</h3>
            <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">Forecasted probability of SPI falling into Alert (SPI &lt; −0.09) or Alarm (SPI &lt; −0.98) thresholds. Dashed reference lines show climatological baselines (46% Alert · 16% Alarm). Bars show forecast midpoints.</p>
            <div class="sci-canvas-wrap" style="height:320px;">
                <canvas id="chartSPI"></canvas>
            </div>
        </div>

    </div>
</section>

<!-- ── Seasonal Source Table ─────────────────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Seasonal Forecast Record — Full Table</h2>
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

<!-- ── Data Sources ──────────────────────────────────────────────── -->
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
/* ── Status cards ───────────────────────────────── */
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

/* ── Vegetation sub-county ──────────────────────── */
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

/* ── Chart cards ────────────────────────────────── */
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

/* ── mm-only entries table ──────────────────────── */
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

/* ── Table category badges ──────────────────────── */
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

/* ── Table ──────────────────────────────────────── */
.sci-table td { vertical-align: top; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    /* ── Raw data from PHP ─────────────────────────────── */
    const tercileData    = <?= json_encode($tercileChart,    JSON_UNESCAPED_UNICODE) ?>;
    const rainfallActual = <?= json_encode($rainfallActuals, JSON_UNESCAPED_UNICODE) ?>;
    const spiData        = <?= json_encode($spiChart,        JSON_UNESCAPED_UNICODE) ?>;

    /* ── Colour palette ────────────────────────────────── */
    const C_RED   = '#dc2626';
    const C_AMBER = '#d97706';
    const C_BLUE  = '#2563eb';
    const C_GREEN = '#16a34a';
    const C_GREY  = '#64748b';
    const C_ALERT = '#d97706';   // amber  — Alert phase
    const C_ALARM = '#dc2626';   // red    — Alarm phase
    const C_ALERT_LIGHT = '#fef3c7';
    const C_ALARM_LIGHT = '#fee2e2';

    /* ── Wrap source_text to ~55 chars per line ────────── */
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

    /* ── Shared base options ───────────────────────────── */
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

    /* ══════════════════════════════════════════════════════
       CHART 1 — Tercile Probabilities (stacked bar)
       ══════════════════════════════════════════════════════ */
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
                            return d.season + ' — ' + d.region;
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

    /* ══════════════════════════════════════════════════════
       CHART 2 — Rainfall Actuals (% of LTM)
       ══════════════════════════════════════════════════════ */
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

    /* ══════════════════════════════════════════════════════
       CHART 3 — Drought SPI Probabilities
       ══════════════════════════════════════════════════════ */
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

})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
