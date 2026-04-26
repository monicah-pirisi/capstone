<?php
/**
 * Samburu EWS: Integrated Early Warning Prototype
 *
 * The integration / trust page: shows how scientific and indigenous
 * knowledge are combined into one socio-technical decision interface.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';
require __DIR__ . '/includes/Db.php';

$pageTitle = 'Prototype';

$kmd        = DataRepository::load('kmd_summary.json');
$ndma       = DataRepository::load('ndma_latest.json');
$indigenous = DataRepository::load('indigenous_indicators.json') ?? [];

// Use live DB link for the bulletin button if the sync has run, else fall back to JSON source_url
$liveKmdLink  = $kmd['source_url']  ?? 'https://meteo.go.ke/our-products/monthly-forecast/';
$liveNdmaLink = $ndma['source_url'] ?? 'https://ndma.go.ke/drought-information/';
try {
    $kmdRow  = Db::fetch('SELECT pdf_url, page_url FROM kmd_ndma_reports WHERE source_org = ? ORDER BY synced_at DESC LIMIT 1', ['KMD']);
    $ndmaRow = Db::fetch('SELECT pdf_url, page_url FROM kmd_ndma_reports WHERE source_org = ? ORDER BY synced_at DESC LIMIT 1', ['NDMA']);
    if ($kmdRow)  $liveKmdLink  = $kmdRow['pdf_url']  ?: $kmdRow['page_url'];
    if ($ndmaRow) $liveNdmaLink = $ndmaRow['pdf_url'] ?: $ndmaRow['page_url'];
} catch (Throwable) {
    // DB not ready, JSON fallback above is fine
}

// Agreement logic
// Official stress: KMD keyword match OR NDMA phase ≥ Alert
$kmdOutlookText = implode(' ', $kmd['outlook'] ?? []) . ' ' . ($kmd['advisory'] ?? '');
$kmdStress      = (bool) preg_match('/below|drought|dry|stress|deficit/i', $kmdOutlookText);
$ndmaPhaseUC    = strtoupper($ndma['phase'] ?? '');
$ndmaStress     = in_array($ndmaPhaseUC, ['ALERT', 'ALARM', 'EMERGENCY']);
$officialStress = $kmdStress || $ndmaStress;

// Indigenous stress: ≥50% of indicators show a stress keyword
$stressWords = ['deteriorating','low','sparse','drying','unusual','restless','below','poor','declining'];
$stressCount = 0;
foreach ($indigenous as $ind) {
    $s = strtolower($ind['status'] ?? '');
    foreach ($stressWords as $w) {
        if (strpos($s, $w) !== false) { $stressCount++; break; }
    }
}
$indigStress = count($indigenous) > 0 && ($stressCount / count($indigenous)) >= 0.5;

// Build a short summary of which official sources are signalling stress
$officialParts = [];
if ($kmdStress)  $officialParts[] = 'KMD (' . ($kmd['valid_period'] ?? 'forecast') . ')';
if ($ndmaStress) $officialParts[] = 'NDMA (' . ($ndma['phase'] ?? 'Alert') . ' phase)';
$officialSummary = implode(' and ', $officialParts);

if ($officialStress && $indigStress) {
    $agreement      = 'agreement';
    $agreementLabel = 'Agreement';
    $trustMsg       = ($officialSummary ? $officialSummary . ' and indigenous indicators all point to elevated drought concern' : 'All systems indicate elevated drought concern') . ', confidence is highest when both knowledge systems align (§4.7.1).';
    $trustColor     = 'var(--clr-danger)';
    $trustBg        = 'var(--clr-danger-light)';
    $confidence     = 90;
} elseif ($officialStress || $indigStress) {
    $agreement      = 'partial';
    $agreementLabel = 'Partial Agreement';
    $partialDetail  = $ndmaStress ? ' NDMA is at ' . ($ndma['phase'] ?? 'Alert') . ' phase.' : '';
    $trustMsg       = 'Mixed signals between official forecasts and community observations.' . $partialDetail . ' Closer monitoring is recommended.';
    $trustColor     = 'var(--clr-warning)';
    $trustBg        = 'var(--clr-warning-light)';
    $confidence     = 55;
} else {
    $agreement      = 'disagreement';
    $agreementLabel = 'Disagreement';
    $trustMsg       = 'Neither official forecasts nor indigenous indicators show strong drought signals. Consult the latest bulletin and community updates before acting.';
    $trustColor     = 'var(--clr-info)';
    $trustBg        = 'var(--clr-info-light)';
    $confidence     = 30;
}

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Integrated Early Warning Prototype</h1>
        <p>This prototype integrates official scientific forecast information and community indigenous indicators into one comparative decision-support interface for drought early warning in Samburu County.</p>
    </div>
</section>

<!-- Section 1: Integrated Comparison Overview -->
<section class="page-section proto-comparison-section">
    <div class="container">
        <div class="section-header">
            <h2>Integrated Knowledge Comparison</h2>
            <p>Scientific forecasts and community indigenous signals compared side-by-side. When both align, confidence in the warning is stronger, the core recommendation of §4.7.1 and Recommendation 1 of the study (§5.3).</p>
        </div>

        <div class="proto-comparison-grid">

            <!-- Card 1: Official Scientific Sources (KMD + NDMA) -->
            <div class="card proto-source-card">
                <div class="card-header" style="background:var(--clr-info-light);border-radius:var(--radius-lg) var(--radius-lg) 0 0;padding:var(--sp-md) var(--sp-lg);">
                    <div>
                        <span class="proto-badge proto-badge-blue">Official Sources</span>
                        <h3 class="card-title mt-xs">KMD &amp; NDMA</h3>
                    </div>
                </div>
                <div class="card-body">

                    <!-- KMD sub-section -->
                    <div class="proto-official-sub">
                        <div class="proto-official-sub-label">KMD Forecast: <?= htmlspecialchars($kmd['valid_period'] ?? '—') ?></div>
                        <ul class="proto-source-list">
                            <?php foreach ($kmd['outlook'] ?? [] as $month => $text): ?>
                            <li><strong><?= ucfirst(htmlspecialchars($month)) ?>:</strong> <?= htmlspecialchars($text) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (!empty($kmd['advisory'])): ?>
                        <p class="proto-advisory"><?= htmlspecialchars($kmd['advisory']) ?></p>
                        <?php endif; ?>
                        <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:var(--sp-xs);">
                            Updated: <?= htmlspecialchars($kmd['updated_at'] ?? '—') ?> &nbsp;·&nbsp;
                            <a href="<?= htmlspecialchars($liveKmdLink) ?>" target="_blank" rel="noopener" style="color:var(--clr-info);">View bulletin →</a>
                        </p>
                    </div>

                    <!-- NDMA sub-section -->
                    <div class="proto-official-sub proto-official-sub-ndma">
                        <?php
                        $ndmaPhase     = $ndma['phase'] ?? '—';
                        $ndmaPhaseUpper = strtoupper($ndmaPhase);
                        $ndmaPhaseClass = match($ndmaPhaseUpper) {
                            'NORMAL'    => 'proto-phase-normal',
                            'WATCH'     => 'proto-phase-watch',
                            'ALERT'     => 'proto-phase-alert',
                            'ALARM'     => 'proto-phase-alarm',
                            'EMERGENCY' => 'proto-phase-emergency',
                            default     => 'proto-phase-watch',
                        };
                        ?>
                        <div class="proto-official-sub-label">
                            NDMA Bulletin: <?= htmlspecialchars($ndma['bulletin_month'] ?? '—') ?>
                            <span class="proto-phase-badge <?= $ndmaPhaseClass ?>"><?= htmlspecialchars($ndmaPhase) ?></span>
                        </div>
                        <p style="font-size:var(--fs-xs);font-style:italic;color:var(--clr-text-muted);margin-bottom:var(--sp-xs);"><?= htmlspecialchars($ndma['phase_justification'] ?? '') ?></p>
                        <ul class="proto-source-list">
                            <?php $sc = $ndma['samburu_specific'] ?? []; ?>
                            <?php if (!empty($sc['vegetation_east'])): ?>
                            <li><strong>East:</strong> <?= htmlspecialchars($sc['vegetation_east']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($sc['vegetation_north'])): ?>
                            <li><strong>North:</strong> <?= htmlspecialchars($sc['vegetation_north']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($sc['vegetation_central'])): ?>
                            <li><strong>Central:</strong> <?= htmlspecialchars($sc['vegetation_central']) ?></li>
                            <?php endif; ?>
                            <?php $fs = $ndma['food_security'] ?? []; ?>
                            <?php if (!empty($fs)): ?>
                            <li><strong>Food security:</strong> Acceptable <?= $fs['acceptable_pct'] ?? '—' ?>% · Borderline <?= $fs['borderline_pct'] ?? '—' ?>% · Poor <?= $fs['poor_pct'] ?? '—' ?>%</li>
                            <?php endif; ?>
                            <?php $lv = $ndma['livestock'] ?? []; ?>
                            <?php if (!empty($lv['body_condition'])): ?>
                            <li><strong>Livestock:</strong> <?= htmlspecialchars($lv['body_condition']) ?> · <?= htmlspecialchars($lv['migration_status'] ?? '') ?></li>
                            <?php endif; ?>
                        </ul>
                        <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:var(--sp-xs);">
                            Updated: <?= htmlspecialchars($ndma['updated_at'] ?? '—') ?> &nbsp;·&nbsp;
                            <a href="<?= htmlspecialchars($liveNdmaLink) ?>" target="_blank" rel="noopener" style="color:var(--clr-info);">View bulletin →</a>
                        </p>
                    </div>

                </div>
            </div>

            <!-- Card 2: Indigenous Knowledge -->
            <div class="card proto-source-card">
                <div class="card-header" style="background:var(--clr-success-light);border-radius:var(--radius-lg) var(--radius-lg) 0 0;padding:var(--sp-md) var(--sp-lg);">
                    <div>
                        <span class="proto-badge proto-badge-green">Community Signals</span>
                        <h3 class="card-title mt-xs">Indigenous Indicators</h3>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-sm);">
                        Source: Samburu elders &amp; pastoralists, documented in §4.2.1, Chapter 4 (2026).
                        <?= count($indigenous) ?> indicators across 2 knowledge tiers.
                    </p>

                    <?php
                    // Group all indicators by tier then category
                    $indGeneral    = array_filter($indigenous, fn($i) => ($i['tier'] ?? 'general') === 'general');
                    $indSpecialist = array_filter($indigenous, fn($i) => ($i['tier'] ?? 'general') === 'specialist');

                    $catLabels = [
                        'animals' => 'Animal Behaviour',
                        'weather' => 'Weather & Sky',
                        'land'    => 'Land & Temperature',
                        'plants'  => 'Vegetation & Plants',
                    ];

                    // Group general by category
                    $indByCat = [];
                    foreach ($indGeneral as $ind) {
                        $indByCat[$ind['category']][] = $ind;
                    }
                    ?>

                    <!-- Tier 1: General indicators -->
                    <div class="proto-tier-label">Tier 1: General Elders</div>
                    <div class="proto-ind-scroll">
                        <?php foreach ($catLabels as $catKey => $catLabel): ?>
                        <?php if (empty($indByCat[$catKey])) continue; ?>
                        <div class="proto-ind-group">
                            <div class="proto-ind-cat"><?= $catLabel ?></div>
                            <?php foreach ($indByCat[$catKey] as $ind): ?>
                            <?php
                            $isStress = (bool) array_filter($stressWords,
                                fn($w) => strpos(strtolower($ind['status'] ?? ''), $w) !== false
                            );
                            ?>
                            <div class="proto-ind-row">
                                <span class="proto-ind-name"><?= htmlspecialchars($ind['indicator']) ?></span>
                                <span class="proto-ind-status <?= $isStress ? 'proto-ind-stress' : 'proto-ind-normal' ?>">
                                    <?= htmlspecialchars($ind['status']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tier 2: Specialist indicators -->
                    <?php if (!empty($indSpecialist)): ?>
                    <div class="proto-tier-label proto-tier-specialist-label">Tier 2: Specialist Elders</div>
                    <div class="proto-specialist-rows">
                        <?php foreach ($indSpecialist as $ind): ?>
                        <div class="proto-ind-row proto-specialist-row">
                            <span class="proto-ind-name"><?= htmlspecialchars($ind['indicator']) ?></span>
                            <span class="proto-ind-status proto-ind-specialist">
                                <?= htmlspecialchars($ind['status']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <p class="proto-advisory" style="margin-top:var(--sp-md);">
                        <?php
                        $stressRatio = count($indigenous) > 0 ? $stressCount / count($indigenous) : 0;
                        if ($stressRatio >= 0.7)
                            echo $stressCount . ' of ' . count($indigenous) . ' indicators show stress, community signals align with drought concern.';
                        elseif ($stressRatio >= 0.4)
                            echo $stressCount . ' of ' . count($indigenous) . ' indicators show stress, mixed conditions, some concern present.';
                        else
                            echo $stressCount . ' of ' . count($indigenous) . ' indicators show stress, relatively normal seasonal conditions.';
                        ?>
                    </p>

                    <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:var(--sp-sm);">
                        <a href="indigenous-data.php" style="color:var(--clr-primary);">View full indicator reference →</a>
                    </p>
                </div>
            </div>

            <!-- Card 3: Combined Trust Result -->
            <div class="card proto-source-card proto-trust-card" style="border:2px solid <?= $trustColor ?>;">
                <div class="card-header" style="background:<?= $trustBg ?>;border-radius:var(--radius-lg) var(--radius-lg) 0 0;padding:var(--sp-md) var(--sp-lg);">
                    <div>
                        <span class="proto-badge" style="background:<?= $trustColor ?>;color:#fff;">STS Assessment</span>
                        <h3 class="card-title mt-xs">Combined Interpretation</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="proto-agreement-badge" style="background:<?= $trustBg ?>;border:1.5px solid <?= $trustColor ?>;color:<?= $trustColor ?>;">
                        <?= $agreementLabel ?>
                    </div>
                    <p style="font-size:var(--fs-sm);margin:var(--sp-md) 0;"><?= $trustMsg ?></p>

                    <!-- Confidence meter -->
                    <div class="proto-meter-wrap">
                        <div style="display:flex;justify-content:space-between;font-size:var(--fs-xs);color:var(--clr-text-muted);margin-bottom:4px;">
                            <span>Alert confidence</span>
                            <strong style="color:<?= $trustColor ?>;"><?= $confidence ?>%</strong>
                        </div>
                        <div class="proto-meter-track">
                            <div class="proto-meter-fill" style="width:<?= $confidence ?>%;background:<?= $trustColor ?>;"></div>
                        </div>
                        <p style="font-size:var(--fs-xs);color:var(--clr-text-muted);margin-top:5px;line-height:1.5;">
                            Reflects agreement between KMD scientific forecast and indigenous indicators.
                            Full agreement = 90% · Partial = 55% · No agreement = 30%.
                            Per §4.7.1: confidence rises when both knowledge systems point to the same outcome.
                        </p>
                    </div>

                    <div class="mt-md">
                        <a href="current-alert.php" class="btn btn-accent btn-sm" style="width:100%;text-align:center;display:block;">
                            View Current Alert →
                        </a>
                    </div>
                </div>
            </div>

        </div><!-- .proto-comparison-grid -->
    </div>
</section>

<!-- Section 2: Why Integration Matters -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Why Integration Matters</h2>
            <p>Combining scientific and indigenous knowledge produces better drought early warning than either system alone.</p>
        </div>
        <div class="grid grid-3 grid-auto">
            <div class="card proto-why-card">
                <h3 class="card-title">Trust</h3>
                <p style="font-size:var(--fs-sm);color:var(--clr-text-muted);line-height:1.7;">
                    Elder authority functions as a trust gateway, information is more likely to be acted upon
                    when it passes through elder validation (§4.3.1). When a government warning and an elder
                    observation point to the same outcome, communities respond earlier and more confidently.
                </p>
                <p class="proto-why-quote">"If the government talks to the elders, and the elders comply with the information they have, then that will be more powerful than when they have information from only one source." Research participant (§4.3.1)</p>
            </div>
            <div class="card proto-why-card">
                <h3 class="card-title">Local Relevance</h3>
                <p style="font-size:var(--fs-sm);color:var(--clr-text-muted);line-height:1.7;">
                    KMD warnings are issued at county level rather than sub-county level, which reduces
                    their practical value for local decision-making (§4.1.2). Samburu North, East, Central,
                    and West have different rainfall patterns, indigenous indicators fill that gap with
                    location-specific, on-the-ground signals.
                </p>
                <p class="proto-why-quote">"Samburu County is also divided into North, East, Central, and West. Regions like the North receive less rainfall than the West, so the warnings are too general for them." Research participant (§4.1.2)</p>
            </div>
            <div class="card proto-why-card">
                <h3 class="card-title">Better Decisions</h3>
                <p style="font-size:var(--fs-sm);color:var(--clr-text-muted);line-height:1.7;">
                    When both scientific data and local observations point to the same outcome, the warning
                    becomes more convincing and communities are more likely to take early action (§4.7.1).
                    This is Recommendation 1 of the study: combine meteorological forecasts with elder
                    observations in the same warning message (§5.3).
                </p>
                <p class="proto-why-quote">"When both the government and elders communicate the same message, communities are more likely to believe the warning and prepare." Research participant (§4.7.1)</p>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: How the Integration Works -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>How the Integration Works</h2>
            <p>Five steps from raw data inputs to stakeholder action.</p>
        </div>
        <div class="proto-steps">
            <div class="proto-step">
                <div class="proto-step-num">1</div>
                <h4>Scientific Data</h4>
                <p>KMD forecasts &amp; NDMA bulletins loaded from field-updated JSON files</p>
            </div>
            <div class="proto-step-arrow">→</div>
            <div class="proto-step">
                <div class="proto-step-num">2</div>
                <h4>Indigenous Observations</h4>
                <p>Community signals from pastoralists, elders, and ecological observers</p>
            </div>
            <div class="proto-step-arrow">→</div>
            <div class="proto-step">
                <div class="proto-step-num">3</div>
                <h4>Comparison Logic</h4>
                <p>KMD/NDMA stress signals compared against indigenous indicator stress ratio to determine agreement &amp; confidence</p>
            </div>
            <div class="proto-step-arrow">→</div>
            <div class="proto-step">
                <div class="proto-step-num">4</div>
                <h4>Alert Generation</h4>
                <p>Composite risk level (Normal → Emergency) and trust confidence level produced</p>
            </div>
            <div class="proto-step-arrow">→</div>
            <div class="proto-step">
                <div class="proto-step-num">5</div>
                <h4>Dissemination</h4>
                <p>Alerts routed to stakeholders via WhatsApp, radio, USSD, and web dashboard</p>
            </div>
        </div>
    </div>
</section>

<!-- Section 4: Interactive Modules -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Supporting Modules</h2>
            <p>These interactive modules make up the full integrated system. Each supports a different layer of the early warning pipeline.</p>
        </div>
        <div class="grid grid-2 grid-auto">

            <a href="findings.php" class="card prototype-card" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-primary-pale);color:var(--clr-primary);"></div>
                    <div>
                        <h3 class="card-title">Research Findings Dashboard</h3>
                        <span class="badge badge-green">Qualitative Analysis</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Thematic analysis of 12 qualitative interviews: seven emergent themes, seven socio-technical barriers with participant evidence, and seven recommendations from Chapter 5.</p>
                    <ul class="proto-features">
                        <li>Seven emergent themes (RQ1–RQ4)</li>
                        <li>Barrier cards with interview evidence</li>
                        <li>Recommendations mapped to responsible actors</li>
                        <li>STS framework interpretation</li>
                    </ul>
                </div>
            </a>

            <a href="current-alert.php" class="card prototype-card" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-danger-light);color:var(--clr-danger);"></div>
                    <div>
                        <h3 class="card-title">Current Alert Engine</h3>
                        <span class="badge badge-red">Live Risk Score</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>NDMA phase, KMD forecast, and indigenous indicator stress ratio combined into a composite risk score (0–100). Sourced from the field-updated JSON data layer.</p>
                    <ul class="proto-features">
                        <li>Composite score + sub-score bars</li>
                        <li>NDMA &amp; KMD source cards</li>
                        <li>Indigenous indicators panel</li>
                        <li>Stakeholder routing table</li>
                    </ul>
                </div>
            </a>

            <a href="channels.php" class="card prototype-card" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-warning-light);color:var(--clr-warning);"></div>
                    <div>
                        <h3 class="card-title">Dissemination Channels</h3>
                        <span class="badge badge-amber">Multi-Channel</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Pre-formatted alert templates for WhatsApp, Facebook/X, vernacular radio scripts, and USSD menu content, auto-filled from risk level.</p>
                    <ul class="proto-features">
                        <li>WhatsApp templates (5 alert levels)</li>
                        <li>Radio scripts with SFX cues</li>
                        <li>Facebook/X posts</li>
                        <li>USSD menu structure</li>
                    </ul>
                </div>
            </a>

            <a href="ussd-simulator.php" class="card prototype-card" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="card-icon" style="background:#1a1a2e;color:#00ff88;"></div>
                    <div>
                        <h3 class="card-title">USSD Simulator (*384#)</h3>
                        <span class="badge badge-blue">Interactive Demo</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Phone-like interface simulating the USSD experience for pastoralists with basic phones. Supports English and Samburu languages.</p>
                    <ul class="proto-features">
                        <li>Realistic phone UI with keypad</li>
                        <li>Live risk data from API</li>
                        <li>Bilingual menu (EN / Samburu)</li>
                        <li>Emergency contacts</li>
                    </ul>
                </div>
            </a>

            <a href="stakeholders.php" class="card prototype-card" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-info-light);color:var(--clr-info);"></div>
                    <div>
                        <h3 class="card-title">Stakeholder Profiles</h3>
                        <span class="badge badge-blue">5 Groups</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Detailed profiles for Government, NGOs, Radio Stations, Pastoralists, and Intermediaries, with per-phase response actions.</p>
                    <ul class="proto-features">
                        <li>Members &amp; entities list</li>
                        <li>Preferred channels per group</li>
                        <li>Per-phase response actions</li>
                    </ul>
                </div>
            </a>

            <a href="resources.php" class="card prototype-card" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-primary-pale);color:var(--clr-primary);"></div>
                    <div>
                        <h3 class="card-title">Education Hub</h3>
                        <span class="badge badge-green">Knowledge Base</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Drought phase guide, risk-score methodology, indigenous indicator reference, and external links to NDMA, KMD, and FEWS NET.</p>
                    <ul class="proto-features">
                        <li>5-phase colour-coded guide</li>
                        <li>Score formula documentation</li>
                        <li>Indigenous indicator reference</li>
                        <li>External resource links</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Section 5: Now vs Future -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Now vs Future Work</h2>
            <p>What is implemented in this capstone prototype and what would be needed for a production deployment.</p>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Feature / Capability</th>
                        <th>Now (This Prototype)</th>
                        <th>Future (Production)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Knowledge Integration</strong></td>
                        <td>Side-by-side comparison of KMD forecast and indigenous indicators; agreement logic on page load</td>
                        <td>Automated agreement scoring with configurable weights per season and region</td>
                    </tr>
                    <tr>
                        <td><strong>Scientific Data</strong></td>
                        <td>KMD and NDMA summaries stored in manually updated JSON files</td>
                        <td>Automated syncing of latest KMD report links and summaries when published</td>
                    </tr>
                    <tr>
                        <td><strong>Indigenous Data</strong></td>
                        <td>Static JSON from field research; displayed and integrated in scoring</td>
                        <td>Community scout mobile app for periodic indicator reporting; IKMS database</td>
                    </tr>
                    <tr>
                        <td><strong>Risk Computation</strong></td>
                        <td>Rule-based agreement scoring between KMD forecast and indigenous indicators; confidence level computed on page load</td>
                        <td>Joint warning validation committee (elders, chiefs, government) reviews and confirms the score before dissemination, Recommendation 5, §5.3</td>
                    </tr>
                    <tr>
                        <td><strong>USSD</strong></td>
                        <td>Web-based USSD simulator (demo experience)</td>
                        <td>Real USSD service via telco gateway (Safaricom / Airtel *384# shortcode)</td>
                    </tr>
                    <tr>
                        <td><strong>SMS / WhatsApp</strong></td>
                        <td>Message templates in channels toolkit; auto-filled from risk level</td>
                        <td>Automated broadcast via Africa's Talking or WhatsApp Business API on alert threshold</td>
                    </tr>
                    <tr>
                        <td><strong>Feedback Loop</strong></td>
                        <td>Contact form → MySQL; admin dashboard for submissions</td>
                        <td>USSD feedback menu; community scout reporting portal; signal tracking over time</td>
                    </tr>
                    <tr>
                        <td><strong>Multilingual</strong></td>
                        <td>English + Samburu demo in USSD; radio scripts ready for translation</td>
                        <td>Full Samburu and Swahili interface; professional vernacular translation of all content</td>
                    </tr>
                    <tr>
                        <td><strong>Authentication</strong></td>
                        <td>Simple admin password (capstone-grade)</td>
                        <td>Role-based access (NDMA staff, County, NGO, Radio); audit logs</td>
                    </tr>
                    <tr>
                        <td><strong>Hosting</strong></td>
                        <td>Local PHP dev server; cPanel shared hosting</td>
                        <td>Cloud VM with CDN, SSL, daily backups, and 99.9% uptime SLA</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="grid grid-2 grid-auto mt-lg">
            <a href="current-alert.php" class="btn btn-accent btn-lg text-center">View Current Alert →</a>
            <a href="ussd-simulator.php" class="btn btn-primary btn-lg text-center">Try USSD Simulator →</a>
        </div>
    </div>
</section>

<!-- Section 6: Technology Stack -->
<section class="page-section" style="background:var(--clr-bg-alt);">
    <div class="container">
        <div class="section-header">
            <h2>Technology Stack</h2>
        </div>
        <div class="grid grid-3 grid-auto">
            <div class="card text-center">
                <h3 class="card-title">PHP 8+</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Backend services, risk engine, data integration, API endpoints</p>
            </div>
            <div class="card text-center">
                <h3 class="card-title">MySQL</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Contact messages, feedback submissions (PDO prepared statements)</p>
            </div>
            <div class="card text-center">
                <h3 class="card-title">HTML / CSS / Vanilla JS</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">No frameworks, responsive, accessible, performant across all devices</p>
            </div>
        </div>
    </div>
</section>

<style>
/* Comparison section */
.proto-comparison-section { background: linear-gradient(135deg, #f0faf4 0%, #e8f4fb 100%); }

.proto-comparison-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-lg);
    align-items: start;
}
@media (max-width: 900px) {
    .proto-comparison-grid { grid-template-columns: 1fr; }
}

.proto-source-card { padding: 0; overflow: hidden; }
.proto-source-card .card-body { padding: var(--sp-lg); }
.proto-source-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.proto-source-list {
    list-style: none;
    font-size: var(--fs-sm);
    margin-bottom: var(--sp-md);
}
.proto-source-list li {
    padding: 4px 0;
    border-bottom: 1px solid var(--clr-border-light);
}
.proto-source-list li:last-child { border-bottom: none; }

.proto-advisory {
    font-size: var(--fs-sm);
    font-style: italic;
    color: var(--clr-text-muted);
    background: rgba(0,0,0,.04);
    padding: var(--sp-sm) var(--sp-md);
    border-radius: var(--radius-md);
    margin-top: var(--sp-sm);
}

/* trust card */
.proto-trust-card { box-shadow: 0 6px 24px rgba(0,0,0,.12); }

.proto-agreement-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px var(--sp-md);
    border-radius: var(--radius-pill);
    font-weight: var(--fw-semi);
    font-size: var(--fs-sm);
    margin-bottom: var(--sp-sm);
}

.proto-meter-wrap { margin-top: var(--sp-md); }
.proto-meter-track {
    height: 10px;
    background: var(--clr-border-light);
    border-radius: var(--radius-pill);
    overflow: hidden;
}
.proto-meter-fill {
    height: 100%;
    border-radius: var(--radius-pill);
    transition: width 0.8s ease;
}

/* source badges */
.proto-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: var(--fw-semi);
    padding: 2px 8px;
    border-radius: var(--radius-pill);
    letter-spacing: 0.03em;
    text-transform: uppercase;
}
.proto-badge-blue  { background: var(--clr-info-light);    color: var(--clr-info); }
.proto-badge-green { background: var(--clr-success-light);  color: var(--clr-success); }

/* Official sub-sections (KMD + NDMA in one card) */
.proto-official-sub {
    padding-bottom: var(--sp-md);
    margin-bottom: var(--sp-md);
    border-bottom: 1px solid var(--clr-border-light);
}
.proto-official-sub:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.proto-official-sub-label {
    font-size: var(--fs-xs);
    font-weight: var(--fw-semi);
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--clr-info);
    margin-bottom: var(--sp-xs);
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
    flex-wrap: wrap;
}
.proto-official-sub-ndma .proto-official-sub-label { color: var(--clr-text-muted); }
.proto-phase-badge {
    font-size: var(--fs-xs);
    padding: 2px 8px;
    border-radius: var(--radius-pill);
    font-weight: var(--fw-bold);
    text-transform: uppercase;
}
.proto-phase-normal    { background: var(--clr-primary-pale);  color: var(--clr-primary); }
.proto-phase-watch     { background: var(--clr-info-light);    color: var(--clr-info); }
.proto-phase-alert     { background: var(--clr-warning-light); color: var(--clr-warning); }
.proto-phase-alarm     { background: var(--clr-danger-light);  color: var(--clr-danger); }
.proto-phase-emergency { background: #5a0000; color: #fff; }

/* Why integration matters cards */
.proto-why-card {
    padding: var(--sp-xl);
    display: flex;
    flex-direction: column;
    gap: var(--sp-sm);
}
.proto-why-quote {
    font-size: var(--fs-xs);
    font-style: italic;
    color: var(--clr-text-muted);
    border-left: 3px solid var(--clr-accent);
    padding-left: var(--sp-sm);
    line-height: 1.6;
    margin-top: var(--sp-xs);
}

/* Process steps */
.proto-steps {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 0;
    flex-wrap: wrap;
}
.proto-step {
    flex: 1;
    min-width: 130px;
    max-width: 180px;
    text-align: center;
    padding: var(--sp-md) var(--sp-sm);
    background: #fff;
    border: 1px solid var(--clr-border-light);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}
.proto-step-num {
    width: 28px; height: 28px;
    background: var(--clr-primary);
    color: #fff;
    border-radius: 50%;
    font-size: var(--fs-xs);
    font-weight: var(--fw-bold);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto var(--sp-xs);
}
.proto-step h4   { font-size: var(--fs-sm); margin-bottom: var(--sp-xs); }
.proto-step p    { font-size: var(--fs-xs); color: var(--clr-text-muted); line-height: 1.5; }
.proto-step-arrow {
    align-self: center;
    font-size: 1.4rem;
    color: var(--clr-primary-light);
    padding: 0 var(--sp-xs);
    flex-shrink: 0;
}
@media (max-width: 768px) {
    .proto-steps { flex-direction: column; align-items: center; }
    .proto-step-arrow { transform: rotate(90deg); }
    .proto-step { max-width: 100%; width: 100%; }
}

/* Indigenous indicator display */
.proto-tier-label {
    font-size: var(--fs-xs);
    font-weight: var(--fw-semi);
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--clr-primary);
    background: var(--clr-primary-pale);
    padding: 3px var(--sp-sm);
    border-radius: var(--radius-sm);
    margin: var(--sp-sm) 0 var(--sp-xs);
    display: inline-block;
}
.proto-tier-specialist-label {
    color: #7b3fa0;
    background: #f3e8ff;
}
.proto-ind-scroll {
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid var(--clr-border-light);
    border-radius: var(--radius-md);
    padding: var(--sp-xs) 0;
}
.proto-ind-group { padding: var(--sp-xs) 0; }
.proto-ind-cat {
    font-size: var(--fs-xs);
    font-weight: var(--fw-semi);
    color: var(--clr-text-muted);
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 4px var(--sp-sm) 2px;
    border-top: 1px solid var(--clr-border-light);
}
.proto-ind-group:first-child .proto-ind-cat { border-top: none; }
.proto-ind-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px var(--sp-sm);
    gap: var(--sp-sm);
    font-size: var(--fs-xs);
}
.proto-ind-name {
    color: var(--clr-text);
    flex: 1;
    line-height: 1.4;
}
.proto-ind-status {
    font-size: 0.68rem;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    white-space: nowrap;
    font-weight: var(--fw-medium);
    flex-shrink: 0;
}
.proto-ind-stress  { background: var(--clr-danger-light); color: var(--clr-danger); }
.proto-ind-normal  { background: var(--clr-success-light); color: var(--clr-success); }
.proto-ind-specialist { background: #f3e8ff; color: #7b3fa0; }
.proto-specialist-rows { margin-top: var(--sp-xs); }
.proto-specialist-row  { background: #faf5ff; border-radius: var(--radius-sm); margin-bottom: 2px; }

/* Module cards */
.prototype-card { transition: transform var(--tr-base), box-shadow var(--tr-base); }
.prototype-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.proto-features {
    list-style: none; margin-top: var(--sp-md);
    font-size: var(--fs-sm); color: var(--clr-text-muted);
}
.proto-features li { padding: 2px 0; }
.proto-features li::before { content: '✓ '; color: var(--clr-primary-light); font-weight: 700; }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
