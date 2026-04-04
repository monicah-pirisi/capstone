<?php
/**
 * Samburu EWS — Current Alert
 *
 * Final action page: current warning level, assessment breakdown,
 * stakeholder actions, and dissemination channel messages.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'Current Alert';

/* PHP-side source data for provenance bar (static from JSON) */
$kmd      = DataRepository::load('kmd_summary.json');
$ndma     = DataRepository::load('ndma_latest.json');
$bulletins = DataRepository::load('kmd_bulletins.json') ?? [];

require __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ─────────────────────────────────────────────────────── -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Current Drought Alert</h1>
        <p>Current drought warning based on the latest scientific bulletin and community observations. Use this page to understand the alert level, verify the reasons, and take appropriate action.</p>
    </div>
</section>

<!-- ── Alert Risk Banner ─────────────────────────────────────────── -->
<section class="page-section" style="padding-top:var(--sp-lg);padding-bottom:0;">
    <div class="container">
        <!-- Loading state shown while JS computes the risk score -->
        <div id="loadingOverlay" class="ca-loading">
            <div class="spinner"></div>
            <p>Computing risk assessment…</p>
        </div>
        <!-- Banner revealed by JS after computation -->
        <div id="alertBanner" class="alert-banner-large" style="display:none;">
            <div class="alert-large-icon" id="alertIcon"></div>
            <div class="alert-large-body">
                <div class="alert-large-level" id="alertLevel">—</div>
                <div class="alert-large-score">
                    Composite Score: <strong id="alertScore">—</strong> / 100
                </div>
                <div class="alert-large-time" id="alertTime"></div>
            </div>
        </div>
    </div>
</section>

<!-- ── Provenance bar ────────────────────────────────────────────── -->
<section style="padding:var(--sp-md) 0 var(--sp-lg);">
    <div class="container">
        <div class="alert-provenance-bar">
            <div class="alert-provenance-text">
                <span>
                    Based on
                    <strong><?= htmlspecialchars($kmd['source'] ?? 'KMD') ?></strong>
                    bulletin (<?= htmlspecialchars($kmd['valid_period'] ?? '—') ?>),
                    <strong><?= htmlspecialchars($ndma['source'] ?? 'NDMA') ?></strong>
                    assessment (<?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?>),
                    and community indigenous observations.
                </span>
            </div>
            <div class="alert-provenance-actions">
                <a href="prototype.php" class="btn btn-sm btn-outline">View Integrated Comparison</a>
                <a href="scientific-data.php" class="btn btn-sm btn-outline">Scientific Data</a>
            </div>
        </div>
    </div>
</section>

<!-- ── Assessment Breakdown ──────────────────────────────────────── -->
<section class="page-section" style="padding-top:0;">
    <div class="container">
        <div class="section-header">
            <h2>Assessment Breakdown</h2>
            <p>Indicator sub-scores and the reasons driving the current alert level.</p>
        </div>
        <div class="grid grid-2 grid-auto">
            <div class="card" id="subScoresCard" style="display:none;">
                <h3 class="card-title mb-md">Indicator Sub-Scores</h3>
                <div id="subScoreBars"></div>
            </div>
            <div class="card" id="reasonsCard" style="display:none;">
                <h3 class="card-title mb-md">Assessment Reasons</h3>
                <ul id="reasonsList" class="reasons-list"></ul>
            </div>
        </div>
    </div>
</section>

<!-- ── KMD Bulletin History ──────────────────────────────────────── -->
<?php if (!empty($bulletins)): ?>
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>KMD Monthly Forecast History</h2>
            <p>Official Kenya Meteorological Department forecasts for Samburu County — January to April 2026, latest first.</p>
        </div>

        <div class="bulletin-timeline">
        <?php foreach ($bulletins as $i => $b):
            $catClass = match($b['outlook_category'] ?? '') {
                'below_average'        => 'bul-dry',
                'near_to_below_normal' => 'bul-mixed',
                'near_to_above_normal',
                'above_average'        => 'bul-wet',
                default                => 'bul-mixed',
            };
            $catIcon = match($b['outlook_category'] ?? '') {
                'below_average'        => '☀️',
                'near_to_below_normal' => '🌤',
                'near_to_above_normal',
                'above_average'        => '🌧',
                default                => '🌤',
            };
            $isLatest = $i === 0;
        ?>
        <div class="bulletin-card <?= $catClass ?>">
            <div class="bulletin-card-header">
                <div class="bulletin-month-icon"><?= $catIcon ?></div>
                <div class="bulletin-card-title">
                    <div class="bulletin-month">
                        <?= htmlspecialchars($b['valid_period']) ?>
                        <?php if ($isLatest): ?>
                        <span class="bulletin-latest-badge">Latest</span>
                        <?php endif; ?>
                    </div>
                    <div class="bulletin-outlook-label"><?= htmlspecialchars($b['outlook_label'] ?? '—') ?></div>
                </div>
                <div class="bulletin-updated">
                    Updated: <?= htmlspecialchars($b['updated_at']) ?>
                </div>
            </div>

            <div class="bulletin-card-body">
                <div class="bulletin-samburu-extract">
                    <div class="bulletin-extract-label">Samburu County — KMD Statement</div>
                    <p>"<?= htmlspecialchars($b['samburu_specific']) ?>"</p>
                </div>

                <div class="bulletin-meta-row">
                    <?php if (!empty($b['temperature_max_celsius']) || !empty($b['temperature_min_celsius'])): ?>
                    <div class="bulletin-meta-item">
                        <span class="bulletin-meta-key">Temperature</span>
                        <span class="bulletin-meta-val">
                            <?php
                            $parts = [];
                            if (!empty($b['temperature_min_celsius'])) $parts[] = 'Min ' . $b['temperature_min_celsius'];
                            if (!empty($b['temperature_max_celsius'])) $parts[] = 'Max ' . $b['temperature_max_celsius'];
                            echo htmlspecialchars(implode(' · ', $parts));
                            ?>
                        </span>
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
        </div><!-- .bulletin-timeline -->

        <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:var(--sp-md);text-align:center;">
            Source: Kenya Meteorological Department (KMD) monthly forecasts.
            <a href="https://meteo.go.ke/our-products/monthly-forecast/" target="_blank" rel="noopener" style="color:var(--clr-primary);">meteo.go.ke →</a>
        </p>
    </div>
</section>
<?php endif; ?>

<!-- ── Stakeholder Actions ───────────────────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Stakeholder Actions</h2>
            <p>Recommended immediate actions for each group at the current alert level.</p>
        </div>
        <div class="table-wrap">
            <table class="data-table" id="routingTable">
                <thead>
                    <tr>
                        <th>Stakeholder Group</th>
                        <th>Recommended Action</th>
                    </tr>
                </thead>
                <tbody id="routingBody">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ── Dissemination Messages ────────────────────────────────────── -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Dissemination Messages</h2>
            <p>Auto-filled templates ready for immediate dissemination at the current alert level. Select a channel, review, and copy.</p>
        </div>

        <div class="tab-list" role="tablist" aria-label="Channel message tabs">
            <button class="tab-btn active" role="tab" aria-selected="true"  aria-controls="tabWhatsApp" id="btnWhatsApp" data-tab="tabWhatsApp">WhatsApp</button>
            <button class="tab-btn"        role="tab" aria-selected="false" aria-controls="tabFacebook" id="btnFacebook" data-tab="tabFacebook">Facebook / X</button>
            <button class="tab-btn"        role="tab" aria-selected="false" aria-controls="tabRadio30"  id="btnRadio30"  data-tab="tabRadio30">Radio 30s</button>
            <button class="tab-btn"        role="tab" aria-selected="false" aria-controls="tabRadio60"  id="btnRadio60"  data-tab="tabRadio60">Radio 60s</button>
            <button class="tab-btn"        role="tab" aria-selected="false" aria-controls="tabUSSD"     id="btnUSSD"     data-tab="tabUSSD">USSD</button>
        </div>

        <div class="tab-panel active" role="tabpanel" id="tabWhatsApp" aria-labelledby="btnWhatsApp">
            <div class="card msg-preview"><pre id="msgWhatsApp" class="msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgWhatsApp">Copy message</button>
        </div>
        <div class="tab-panel" role="tabpanel" id="tabFacebook" aria-labelledby="btnFacebook">
            <div class="card msg-preview"><pre id="msgFacebook" class="msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgFacebook">Copy message</button>
        </div>
        <div class="tab-panel" role="tabpanel" id="tabRadio30" aria-labelledby="btnRadio30">
            <div class="card msg-preview"><pre id="msgRadio30" class="msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgRadio30">Copy message</button>
        </div>
        <div class="tab-panel" role="tabpanel" id="tabRadio60" aria-labelledby="btnRadio60">
            <div class="card msg-preview"><pre id="msgRadio60" class="msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgRadio60">Copy message</button>
        </div>
        <div class="tab-panel" role="tabpanel" id="tabUSSD" aria-labelledby="btnUSSD">
            <div class="card msg-preview ussd-phone"><pre id="msgUSSD" class="ussd-screen" style="min-height:auto;"></pre></div>
        </div>
    </div>
</section>

<!-- ── Data Sources ──────────────────────────────────────────────── -->
<section class="page-section" style="padding-top:var(--sp-lg);padding-bottom:var(--sp-lg);">
    <div class="container">
        <h3 style="font-size:var(--fs-md);margin-bottom:var(--sp-md);color:var(--clr-text-muted);">Data Sources</h3>
        <div class="grid grid-2 grid-auto">
            <div class="card ca-source-card" id="ndmaCard" style="display:none;">
                <div class="ca-source-header">
                    <span class="ca-source-label">NDMA Bulletin</span>
                    <span class="badge badge-blue" id="ndmaBulletin"></span>
                </div>
                <p id="ndmaSummary" class="ca-source-summary"></p>
                <div class="ca-source-meta">
                    <span>Phase stated: <strong id="ndmaPhase"></strong></span>
                    <span>Updated: <span id="ndmaUpdated"></span></span>
                </div>
                <a href="#" id="ndmaLink" target="_blank" rel="noopener" class="btn btn-outline btn-sm mt-sm">View NDMA Source →</a>
            </div>

            <div class="card ca-source-card" id="kmdCard" style="display:none;">
                <div class="ca-source-header">
                    <span class="ca-source-label">KMD Forecast</span>
                    <span class="badge badge-amber" id="kmdPeriod"></span>
                </div>
                <p id="kmdAdvisory" class="ca-source-summary"></p>
                <div class="ca-source-meta">
                    <span>Outlook: <strong id="kmdOutlook"></strong></span>
                    <span>Updated: <span id="kmdUpdated"></span></span>
                </div>
                <!-- Hidden fields kept in DOM for JS compatibility -->
                <span id="kmdBelowPct" style="display:none;"></span>
                <span id="kmdOnset"    style="display:none;"></span>
                <a href="#" id="kmdLink" target="_blank" rel="noopener" class="btn btn-outline btn-sm mt-sm">View KMD Bulletin →</a>
            </div>
        </div>

        <!-- Indigenous grid: kept in DOM, hidden — data is on indigenous-data.php -->
        <div id="indigenousGrid" style="display:none;"></div>
    </div>
</section>

<style>
/* ── Loading & spinner ───────────────────────────── */
.ca-loading {
    text-align: center;
    padding: var(--sp-2xl) 0;
    color: var(--clr-text-muted);
}
.spinner {
    width: 40px; height: 40px; margin: 0 auto var(--sp-md);
    border: 4px solid var(--clr-border-light); border-top-color: var(--clr-primary);
    border-radius: 50%; animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Big alert banner ────────────────────────────── */
.alert-banner-large {
    display: flex; align-items: center; gap: var(--sp-xl);
    padding: var(--sp-xl) var(--sp-2xl); border-radius: var(--radius-xl);
    border-left: 6px solid; box-shadow: var(--shadow-lg);
}
.alert-large-icon  { font-size: 3rem; }
.alert-large-level { font-size: var(--fs-2xl); font-weight: var(--fw-extra); }
.alert-large-score { font-size: var(--fs-md); margin-top: var(--sp-xs); }
.alert-large-time  { font-size: var(--fs-xs); color: var(--clr-text-muted); margin-top: var(--sp-xs); }

.alert-banner-large.level-normal    { background: var(--clr-primary-pale);   border-color: var(--clr-primary-light); color: var(--clr-primary); }
.alert-banner-large.level-watch     { background: #e8f4fd;                   border-color: var(--clr-info);          color: #084298; }
.alert-banner-large.level-alert     { background: var(--clr-warning-light);  border-color: var(--clr-warning);       color: #664d03; }
.alert-banner-large.level-alarm     { background: #fde2de;                   border-color: var(--clr-danger);        color: var(--clr-danger); }
.alert-banner-large.level-emergency { background: #5a0000;                   border-color: #ff2020;                  color: #fff; }

/* ── Provenance bar ──────────────────────────────── */
.alert-provenance-bar {
    display: flex; align-items: center; justify-content: space-between;
    gap: var(--sp-md); background: #f0faf4;
    border: 1px solid var(--clr-primary-pale);
    border-left: 4px solid var(--clr-primary-light);
    border-radius: var(--radius-lg);
    padding: var(--sp-md) var(--sp-lg);
    font-size: var(--fs-sm); flex-wrap: wrap;
}
.alert-provenance-text {
    display: flex; align-items: flex-start; gap: var(--sp-sm);
    color: var(--clr-text-muted); flex: 1;
}
.alert-provenance-actions {
    display: flex; gap: var(--sp-sm); flex-wrap: wrap; flex-shrink: 0;
}

/* ── Sub-score bars ──────────────────────────────── */
.subscore-row   { display: flex; align-items: center; gap: var(--sp-sm); margin-bottom: var(--sp-sm); }
.subscore-label { width: 120px; font-size: var(--fs-sm); font-weight: var(--fw-medium); flex-shrink: 0; }
.subscore-track {
    flex: 1; height: 20px; background: var(--clr-border-light);
    border-radius: var(--radius-pill); overflow: hidden;
}
.subscore-fill {
    height: 100%; border-radius: var(--radius-pill); transition: width .6s ease;
    display: flex; align-items: center; justify-content: flex-end; padding-right: 6px;
    font-size: var(--fs-xs); font-weight: var(--fw-semi); color: #fff;
}

/* ── Reasons list ────────────────────────────────── */
.reasons-list { list-style: none; }
.reasons-list li {
    padding: var(--sp-sm) 0; border-bottom: 1px solid var(--clr-border-light);
    font-size: var(--fs-sm); line-height: 1.6;
}
.reasons-list li::before { content: '› '; }
.reasons-list li:last-child { border-bottom: none; }

/* ── Message previews ────────────────────────────── */
.msg-preview { background: var(--clr-bg); }
.msg-pre {
    white-space: pre-wrap; word-break: break-word;
    font-family: 'Inter', sans-serif; font-size: var(--fs-sm);
    line-height: 1.7; color: var(--clr-text); margin: 0;
}

/* ── Compact source cards ────────────────────────── */
.ca-source-card { padding: var(--sp-md) var(--sp-lg); }
.ca-source-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: var(--sp-sm);
}
.ca-source-label { font-weight: var(--fw-semi); font-size: var(--fs-sm); }
.ca-source-summary {
    font-size: var(--fs-sm); color: var(--clr-text-muted);
    margin-bottom: var(--sp-sm); line-height: 1.5;
}
.ca-source-meta {
    display: flex; gap: var(--sp-lg); font-size: var(--fs-xs);
    color: var(--clr-text-muted); flex-wrap: wrap;
}

/* ── Bulletin timeline ───────────────────────────── */
.bulletin-timeline {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--sp-lg);
}
.bulletin-card {
    background: var(--clr-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    border-top: 4px solid;
    transition: transform var(--tr-base), box-shadow var(--tr-base);
}
.bulletin-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.bul-dry   { border-color: var(--clr-danger);        }
.bul-mixed { border-color: var(--clr-warning);       }
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
    background: var(--clr-bg);
    border-left: 3px solid var(--clr-primary-light);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    padding: var(--sp-sm) var(--sp-md);
    margin-bottom: var(--sp-md);
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

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 768px) {
    .alert-banner-large   { flex-direction: column; text-align: center; padding: var(--sp-lg); }
    .subscore-label       { width: 90px; font-size: var(--fs-xs); }
    .alert-provenance-bar { flex-direction: column; align-items: flex-start; }
}
</style>

<?php
$pageScripts = ['assets/js/currentAlert.js'];
require __DIR__ . '/includes/footer.php';
?>
