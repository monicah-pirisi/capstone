<?php
/**
 * Samburu EWS: Current Alert
 *
 * Final action page: current warning level, assessment breakdown,
 * stakeholder actions, and dissemination channel messages.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'Current Alert';

$kmd         = DataRepository::load('kmd_summary.json');
$ndma        = DataRepository::load('ndma_latest.json');
$ndmaHistory = DataRepository::load('ndma_history.json') ?? [];

// Compute trend: compare last two months' phases
$trendArrow = '';
$trendLabel = '';
$trendColor = '';
if (count($ndmaHistory) >= 2) {
    $phaseOrder = ['Normal' => 1, 'Watch' => 2, 'Alert' => 3, 'Alarm' => 4, 'Emergency' => 5];
    $prev    = $ndmaHistory[count($ndmaHistory) - 2]['phase'] ?? '';
    $current = $ndmaHistory[count($ndmaHistory) - 1]['phase'] ?? '';
    $prevVal = $phaseOrder[$prev]    ?? 0;
    $currVal = $phaseOrder[$current] ?? 0;
    if ($currVal > $prevVal)      { $trendArrow = '↑'; $trendLabel = 'Worsening'; $trendColor = '#dc2626'; }
    elseif ($currVal < $prevVal)  { $trendArrow = '↓'; $trendLabel = 'Improving'; $trendColor = '#16a34a'; }
    else                          { $trendArrow = '→'; $trendLabel = 'Stable';    $trendColor = '#d97706'; }
    $prevMonth = $ndmaHistory[count($ndmaHistory) - 2]['month'] ?? '';
}

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-xl) 0 var(--sp-lg);">
    <div class="container">
        <h1>Current Drought Alert</h1>
        <p>Live risk assessment from the latest NDMA bulletin, KMD forecast, and community indigenous observations for Samburu County.</p>
    </div>
</section>

<!-- Loading -->
<div id="loadingOverlay" class="ca-loading">
    <div class="container" style="text-align:center;padding:var(--sp-2xl) 0;">
        <div class="spinner"></div>
        <p style="color:var(--clr-text-muted);margin-top:var(--sp-md);">Computing risk assessment…</p>
    </div>
</div>

<!-- Command Section: Banner + Gauge -->
<section id="alertCommandSection" class="page-section ca-command-section" style="padding-top:0;display:none;">
    <div class="container">
        <div class="ca-command-grid">

            <!-- Left: Phase Panel -->
            <div class="ca-phase-panel" id="alertBanner">
                <div class="ca-phase-top">
                    <span class="ca-phase-icon" id="alertIcon"></span>
                    <div class="ca-phase-text">
                        <div class="ca-phase-label" id="alertLevel"></div>
                        <div class="ca-phase-time" id="alertTime"></div>
                    </div>
                </div>

                <!-- Trend badge -->
                <?php if ($trendArrow): ?>
                <div class="ca-trend-badge" style="border-color:<?= $trendColor ?>;color:<?= $trendColor ?>;">
                    <span class="ca-trend-arrow"><?= $trendArrow ?></span>
                    <span class="ca-trend-text">
                        <?= htmlspecialchars($trendLabel) ?> since <?= htmlspecialchars($prevMonth) ?>
                    </span>
                    <a href="scientific-data.php#ndmaTrend" class="ca-trend-link">View trend →</a>
                </div>
                <?php endif; ?>

                <!-- Risk Scale -->
                <div class="ca-scale-section">
                    <div class="ca-scale-title">Risk Scale</div>
                    <div class="ca-scale-track">
                        <div class="ca-scale-seg ca-seg-normal">Normal<span>80–100</span></div>
                        <div class="ca-scale-seg ca-seg-alert">Alert<span>60–79</span></div>
                        <div class="ca-scale-seg ca-seg-alarm">Alarm<span>40–59</span></div>
                        <div class="ca-scale-seg ca-seg-emergency">Emergency<span>0–39</span></div>
                    </div>
                    <div class="ca-scale-marker-row">
                        <div class="ca-scale-marker" id="scaleMarker" title="Current score"></div>
                    </div>
                </div>

                <!-- Provenance -->
                <div class="ca-provenance">
                    Sources: NDMA (<?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?>)
                    &nbsp;·&nbsp; KMD (<?= htmlspecialchars($kmd['valid_period'] ?? '—') ?>)
                    &nbsp;·&nbsp; Indigenous observations
                </div>
                <div class="ca-phase-actions">
                    <a href="prototype.php" class="btn btn-sm btn-outline">Integrated View</a>
                    <a href="scientific-data.php" class="btn btn-sm btn-outline">Scientific Data</a>
                </div>
            </div>

            <!-- Right: Gauge Panel -->
            <div class="ca-gauge-panel">
                <div id="scoreGauge" class="ca-gauge-wrap"></div>
                <div class="ca-gauge-caption">Composite Risk Score</div>
                <div class="ca-gauge-sub">Weighted across 6 indicators</div>
            </div>

        </div>
    </div>
</section>

<!-- Assessment Breakdown -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Assessment Breakdown</h2>
            <p>Sub-score per indicator with weight contribution. Threshold lines mark Alert (60) and Normal (80) boundaries.</p>
        </div>
        <div class="ca-breakdown-grid">

            <div class="card ca-scores-card" id="subScoresCard" style="display:none;">
                <div class="ca-scores-head">
                    <h3 class="card-title">Indicator Sub-Scores</h3>
                    <div class="ca-legend">
                        <span class="ca-legend-item"><span class="ca-legend-dot" style="background:#16a34a;"></span>Good (&ge;75)</span>
                        <span class="ca-legend-item"><span class="ca-legend-dot" style="background:#d97706;"></span>Moderate (50–74)</span>
                        <span class="ca-legend-item"><span class="ca-legend-dot" style="background:#dc2626;"></span>Stressed (&lt;50)</span>
                    </div>
                </div>
                <div id="subScoreBars"></div>
                <div class="ca-threshold-key">
                    <span class="ca-thr-item"><span class="ca-thr-line ca-thr-alert"></span>Alert threshold (60)</span>
                    <span class="ca-thr-item"><span class="ca-thr-line ca-thr-normal"></span>Normal threshold (80)</span>
                </div>
            </div>

            <div class="card ca-reasons-card" id="reasonsCard" style="display:none;">
                <h3 class="card-title mb-md">Why This Alert Level?</h3>
                <div id="reasonsList"></div>
            </div>

        </div>
    </div>
</section>

<!-- Data Inputs -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Data Inputs Used</h2>
            <p>Raw values extracted from the <?= htmlspecialchars($ndma['bulletin_month'] ?? 'latest NDMA') ?> bulletin and fed into the risk engine.</p>
        </div>
        <div class="ca-inputs-grid" id="dataInputsGrid">
            <!-- Populated by JS -->
        </div>
    </div>
</section>


<!-- Stakeholder Response -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Stakeholder Response</h2>
            <p>Recommended immediate actions for each group at the current alert level.</p>
        </div>
        <div class="ca-stakeholder-grid" id="stakeholderGrid">
            <!-- Populated by JS -->
        </div>
    </div>
</section>

<!-- Dissemination Messages -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Dissemination Messages</h2>
            <p>Auto-filled templates ready for immediate dissemination. Select a channel, review, and copy.</p>
        </div>

        <div class="ca-tab-list" role="tablist">
            <button class="ca-tab-btn active" data-tab="tabWhatsApp" aria-selected="true">
                <span class="ca-tab-icon">💬</span> WhatsApp
            </button>
            <button class="ca-tab-btn" data-tab="tabFacebook" aria-selected="false">
                <span class="ca-tab-icon">📘</span> Facebook / X
            </button>
            <button class="ca-tab-btn" data-tab="tabRadio30" aria-selected="false">
                <span class="ca-tab-icon">📻</span> Radio 30s
            </button>
            <button class="ca-tab-btn" data-tab="tabRadio60" aria-selected="false">
                <span class="ca-tab-icon">📻</span> Radio 60s
            </button>
            <button class="ca-tab-btn" data-tab="tabUSSD" aria-selected="false">
                <span class="ca-tab-icon">📱</span> USSD
            </button>
        </div>

        <div class="ca-tab-panel active" id="tabWhatsApp">
            <div class="card ca-msg-card"><pre id="msgWhatsApp" class="ca-msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgWhatsApp">Copy message</button>
        </div>
        <div class="ca-tab-panel" id="tabFacebook">
            <div class="card ca-msg-card"><pre id="msgFacebook" class="ca-msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgFacebook">Copy message</button>
        </div>
        <div class="ca-tab-panel" id="tabRadio30">
            <div class="card ca-msg-card"><pre id="msgRadio30" class="ca-msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgRadio30">Copy message</button>
        </div>
        <div class="ca-tab-panel" id="tabRadio60">
            <div class="card ca-msg-card"><pre id="msgRadio60" class="ca-msg-pre"></pre></div>
            <button class="btn btn-primary btn-sm mt-sm copy-btn" data-target="msgRadio60">Copy message</button>
        </div>
        <div class="ca-tab-panel" id="tabUSSD">
            <div class="card ca-msg-card ca-ussd-card"><pre id="msgUSSD" class="ca-msg-pre ca-ussd-pre"></pre></div>
        </div>
    </div>
</section>

<!-- Data Sources -->
<section class="page-section" style="padding:var(--sp-xl) 0;">
    <div class="container">
        <h3 style="font-size:var(--fs-md);font-weight:var(--fw-semi);margin-bottom:var(--sp-lg);color:var(--clr-text);">Data Sources</h3>
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
                <span id="kmdBelowPct" style="display:none;"></span>
                <span id="kmdOnset"    style="display:none;"></span>
                <a href="#" id="kmdLink" target="_blank" rel="noopener" class="btn btn-outline btn-sm mt-sm">View KMD Bulletin →</a>
            </div>
        </div>
        <div id="indigenousGrid" style="display:none;"></div>
    </div>
</section>

<style>
/* ── Loading ─────────────────────────────────────── */
.ca-loading { padding: var(--sp-2xl) 0; }
.spinner {
    width: 44px; height: 44px; margin: 0 auto var(--sp-md);
    border: 4px solid var(--clr-border); border-top-color: var(--clr-primary);
    border-radius: 50%; animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Command Section ─────────────────────────────── */
.ca-command-section { padding-bottom: var(--sp-xl); }
.ca-command-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: var(--sp-xl);
    align-items: start;
}

/* Phase Panel */
.ca-phase-panel {
    padding: var(--sp-xl);
    border-radius: var(--radius-xl);
    border-left: 6px solid;
    box-shadow: var(--shadow-lg);
}
.level-normal    .ca-phase-panel,
.ca-phase-panel.level-normal    { background: #f0fdf4; border-color: #16a34a; }
.ca-phase-panel.level-watch     { background: #eff6ff; border-color: #2563eb; }
.ca-phase-panel.level-alert     { background: #fffbeb; border-color: #d97706; }
.ca-phase-panel.level-alarm     { background: #fff1f2; border-color: #e11d48; }
.ca-phase-panel.level-emergency { background: #3b0000; border-color: #ef4444; color:#fff; }

.ca-phase-top {
    display: flex; align-items: center; gap: var(--sp-md);
    margin-bottom: var(--sp-lg);
}
.ca-phase-icon { font-size: 2.8rem; line-height: 1; }
.ca-phase-label {
    font-size: var(--fs-xl); font-weight: var(--fw-extra);
    letter-spacing: -.02em; line-height: 1.2;
}
.ca-phase-time { font-size: var(--fs-xs); color: var(--clr-text-muted); margin-top: 4px; }
.ca-phase-panel.level-emergency .ca-phase-time { color: rgba(255,255,255,.7); }

/* Risk Scale */
.ca-scale-section { margin: var(--sp-lg) 0 var(--sp-md); }
.ca-scale-title {
    font-size: var(--fs-xs); font-weight: var(--fw-semi); text-transform: uppercase;
    letter-spacing: .06em; color: var(--clr-text-muted); margin-bottom: var(--sp-xs);
}
.ca-scale-track {
    display: grid; grid-template-columns: repeat(4, 1fr);
    border-radius: var(--radius-md); overflow: hidden; height: 38px;
}
.ca-scale-seg {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; font-size: 0.6rem; font-weight: var(--fw-semi);
    text-transform: uppercase; letter-spacing: .04em; color: #fff;
    line-height: 1.2;
}
.ca-scale-seg span { font-size: 0.55rem; opacity: .8; font-weight: var(--fw-normal); }
.ca-seg-normal    { background: #16a34a; }
.ca-seg-alert     { background: #d97706; }
.ca-seg-alarm     { background: #dc2626; }
.ca-seg-emergency { background: #7f1d1d; }

.ca-scale-marker-row { position: relative; height: 16px; }
.ca-scale-marker {
    position: absolute; top: 2px;
    width: 0; height: 0;
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    border-top: 10px solid var(--clr-text);
    transform: translateX(-50%);
    transition: left .8s ease;
}

.ca-provenance {
    font-size: var(--fs-xs); color: var(--clr-text-muted);
    margin-bottom: var(--sp-md); line-height: 1.6;
}
.ca-phase-panel.level-emergency .ca-provenance { color: rgba(255,255,255,.6); }
.ca-phase-actions { display: flex; gap: var(--sp-sm); flex-wrap: wrap; }

/* Trend badge */
.ca-trend-badge {
    display: flex; align-items: center; gap: var(--sp-sm);
    padding: var(--sp-sm) var(--sp-md);
    border: 1.5px solid; border-radius: var(--radius-md);
    margin-bottom: var(--sp-md); flex-wrap: wrap;
    background: rgba(255,255,255,.5);
}
.ca-trend-arrow { font-size: var(--fs-lg); font-weight: var(--fw-black); line-height: 1; }
.ca-trend-text  { font-size: var(--fs-sm); font-weight: var(--fw-semi); flex: 1; }
.ca-trend-link  {
    font-size: var(--fs-xs); color: var(--clr-primary);
    text-decoration: none; white-space: nowrap;
}
.ca-trend-link:hover { text-decoration: underline; }

/* Gauge Panel */
.ca-gauge-panel {
    display: flex; flex-direction: column; align-items: center;
    gap: var(--sp-xs); padding: var(--sp-xl);
    background: var(--clr-surface); border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md); min-width: 200px;
}
.ca-gauge-wrap { width: 180px; height: 180px; }
.ca-gauge-wrap svg { width: 100%; height: 100%; }
.ca-gauge-caption {
    font-size: var(--fs-sm); font-weight: var(--fw-semi);
    color: var(--clr-text); text-align: center;
}
.ca-gauge-sub { font-size: var(--fs-xs); color: var(--clr-text-muted); text-align: center; }

/* ── Assessment Breakdown ────────────────────────── */
.ca-breakdown-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-lg);
    align-items: start;
}
.ca-scores-card { padding: var(--sp-xl); }
.ca-scores-head {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: var(--sp-md);
    margin-bottom: var(--sp-lg); flex-wrap: wrap;
}
.ca-legend { display: flex; gap: var(--sp-md); flex-wrap: wrap; align-items: center; }
.ca-legend-item {
    display: flex; align-items: center; gap: 5px;
    font-size: var(--fs-xs); color: var(--clr-text-muted);
}
.ca-legend-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}

/* Enhanced sub-score rows */
.subscore-row { margin-bottom: var(--sp-md); }
.subscore-meta {
    display: flex; justify-content: space-between; align-items: baseline;
    margin-bottom: 5px;
}
.subscore-label { font-size: var(--fs-sm); font-weight: var(--fw-semi); color: var(--clr-text); }
.subscore-weight { font-size: var(--fs-xs); color: var(--clr-text-muted); }
.subscore-track-wrap { display: flex; align-items: center; gap: var(--sp-sm); }
.subscore-track {
    flex: 1; height: 22px; background: var(--clr-border-light);
    border-radius: var(--radius-pill); overflow: visible; position: relative;
}
.subscore-fill {
    height: 100%; border-radius: var(--radius-pill);
    display: flex; align-items: center; justify-content: flex-end;
    padding-right: 8px; font-size: var(--fs-xs); font-weight: var(--fw-semi);
    color: #fff; min-width: 28px; transition: width .9s cubic-bezier(.4,0,.2,1);
    position: relative; z-index: 1;
}
/* Threshold markers */
.subscore-threshold {
    position: absolute; top: -4px; bottom: -4px; width: 2px;
    z-index: 2; border-radius: 1px;
}
.thr-alert  { background: #d97706; left: 60%; }
.thr-normal { background: #16a34a; left: 80%; }
.subscore-contrib {
    font-size: var(--fs-xs); color: var(--clr-text-muted);
    white-space: nowrap; width: 48px; text-align: right;
    flex-shrink: 0;
}

.ca-threshold-key {
    display: flex; gap: var(--sp-lg); margin-top: var(--sp-lg);
    padding-top: var(--sp-md); border-top: 1px solid var(--clr-border-light);
    flex-wrap: wrap;
}
.ca-thr-item {
    display: flex; align-items: center; gap: 6px;
    font-size: var(--fs-xs); color: var(--clr-text-muted);
}
.ca-thr-line {
    display: inline-block; width: 14px; height: 3px; border-radius: 2px;
}
.ca-thr-alert  { background: #d97706; }
.ca-thr-normal { background: #16a34a; }

/* Reasons card */
.ca-reasons-card { padding: var(--sp-xl); }
.ca-reason-item {
    display: flex; gap: var(--sp-sm); padding: var(--sp-md);
    border-radius: var(--radius-md); margin-bottom: var(--sp-sm);
    font-size: var(--fs-sm); line-height: 1.6; border-left: 3px solid;
}
.ca-reason-high   { background: var(--clr-danger-light);  border-color: var(--clr-danger);  color: #5f1313; }
.ca-reason-medium { background: var(--clr-warning-light); border-color: var(--clr-warning); color: #5a3700; }
.ca-reason-icon   { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }
.ca-reason-ok {
    display: flex; gap: var(--sp-sm); align-items: center;
    padding: var(--sp-md); background: var(--clr-success-light);
    border-radius: var(--radius-md); color: #0a4a1e;
    font-size: var(--fs-sm); border-left: 3px solid var(--clr-success);
}

/* ── Data Inputs ─────────────────────────────────── */
.ca-inputs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: var(--sp-md);
}
.ca-input-card {
    background: var(--clr-surface); border-radius: var(--radius-lg);
    padding: var(--sp-md) var(--sp-lg); border: 1px solid var(--clr-border-light);
    box-shadow: var(--shadow-xs);
}
.ca-input-icon { font-size: 1.4rem; margin-bottom: var(--sp-xs); }
.ca-input-label {
    font-size: var(--fs-xs); text-transform: uppercase; letter-spacing: .06em;
    color: var(--clr-text-muted); font-weight: var(--fw-semi); margin-bottom: 4px;
}
.ca-input-value {
    font-size: var(--fs-lg); font-weight: var(--fw-bold); color: var(--clr-text);
    line-height: 1.2; margin-bottom: 2px;
}
.ca-input-note { font-size: var(--fs-xs); color: var(--clr-text-muted); line-height: 1.4; }


/* ── Stakeholder Cards ───────────────────────────── */
.ca-stakeholder-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: var(--sp-md);
}
.ca-stakeholder-card {
    background: var(--clr-surface); border-radius: var(--radius-lg);
    padding: var(--sp-lg); border: 1px solid var(--clr-border-light);
    box-shadow: var(--shadow-sm); border-top: 3px solid;
    transition: transform .2s, box-shadow .2s;
}
.ca-stakeholder-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.ca-sth-icon { font-size: 1.8rem; margin-bottom: var(--sp-sm); }
.ca-sth-name {
    font-size: var(--fs-sm); font-weight: var(--fw-bold);
    color: var(--clr-text); margin-bottom: var(--sp-sm);
}
.ca-sth-action {
    font-size: var(--fs-sm); color: var(--clr-text-muted);
    line-height: 1.6;
}
.sth-normal    { border-top-color: #16a34a; }
.sth-alert     { border-top-color: #d97706; }
.sth-alarm     { border-top-color: #dc2626; }
.sth-emergency { border-top-color: #7f1d1d; }

/* ── Message Tabs ────────────────────────────────── */
.ca-tab-list {
    display: flex; gap: var(--sp-xs); flex-wrap: wrap;
    margin-bottom: var(--sp-md);
    border-bottom: 2px solid var(--clr-border-light);
    padding-bottom: 0;
}
.ca-tab-btn {
    display: flex; align-items: center; gap: 6px;
    padding: var(--sp-sm) var(--sp-md);
    background: none; border: none; border-bottom: 3px solid transparent;
    margin-bottom: -2px; cursor: pointer;
    font-size: var(--fs-sm); font-weight: var(--fw-medium); color: var(--clr-text-muted);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    transition: color .15s, border-color .15s, background .15s;
}
.ca-tab-btn:hover { color: var(--clr-text); background: var(--clr-border-light); }
.ca-tab-btn.active {
    color: var(--clr-primary); border-bottom-color: var(--clr-primary);
    font-weight: var(--fw-semi);
}
.ca-tab-icon { font-size: 1rem; }
.ca-tab-panel { display: none; }
.ca-tab-panel.active { display: block; }
.ca-msg-card { background: var(--clr-bg); padding: var(--sp-lg); }
.ca-msg-pre {
    white-space: pre-wrap; word-break: break-word;
    font-family: 'Inter', sans-serif; font-size: var(--fs-sm);
    line-height: 1.7; color: var(--clr-text); margin: 0;
}
.ca-ussd-card {
    background: #0d0d0d; border-radius: var(--radius-lg);
    max-width: 320px; margin: 0 auto;
}
.ca-ussd-pre {
    color: #00ff88; font-family: 'Courier New', monospace;
    font-size: var(--fs-sm); line-height: 1.8;
}

/* ── Source Cards ────────────────────────────────── */
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

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 900px) {
    .ca-command-grid   { grid-template-columns: 1fr; }
    .ca-gauge-panel    { flex-direction: row; align-items: center; justify-content: center; gap: var(--sp-lg); }
    .ca-breakdown-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .ca-phase-panel  { padding: var(--sp-lg); }
    .ca-scale-track  { height: 32px; }
    .ca-scale-seg    { font-size: 0.55rem; }
    .ca-gauge-wrap   { width: 140px; height: 140px; }
}
</style>

<?php
$pageScripts = ['assets/js/currentAlert.js'];
require __DIR__ . '/includes/footer.php';
?>
