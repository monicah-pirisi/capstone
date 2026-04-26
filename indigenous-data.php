<?php
/**
 * Samburu EWS: Indigenous Knowledge Systems
 * Data drawn from Chapter 4.2 of the research study:
 * "Social-Technical Barriers Limiting EWS Effectiveness Among
 *  Pastoralist Communities in Samburu County, Kenya" (2026)
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'Indigenous Knowledge';
$indicators = DataRepository::load('indigenous_indicators.json') ?? [];

// Group by tier, then by category
$general    = array_filter($indicators, fn($i) => ($i['tier'] ?? 'general') === 'general');
$specialist = array_filter($indicators, fn($i) => ($i['tier'] ?? 'general') === 'specialist');

// Sub-group general by category
$byCategory = [];
foreach ($general as $ind) {
    $byCategory[$ind['category']][] = $ind;
}

$categoryMeta = [
    'animals' => ['label' => 'Animal Behaviour',   'icon' => '🐄', 'color' => 'var(--clr-primary)'],
    'weather' => ['label' => 'Weather & Sky',       'icon' => '🌤', 'color' => 'var(--clr-info)'],
    'land'    => ['label' => 'Land & Temperature',  'icon' => '🌍', 'color' => 'var(--clr-accent)'],
    'plants'  => ['label' => 'Vegetation & Plants', 'icon' => '🌿', 'color' => 'var(--clr-primary-light)'],
];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Indigenous Knowledge Systems</h1>
        <p>Samburu elders use a systematic, multi-source indigenous forecasting system developed and validated over generations, a structured knowledge practice, not informal guesswork.</p>
    </div>
</section>

<!-- Research Context -->
<section class="page-section" style="padding-top:var(--sp-2xl);">
    <div class="container">

        <div class="ik-context-banner">
            <div class="ik-context-text">
                <h2>What the Research Found</h2>
                <p>
                    Findings from Chapter 4.2 of the study show that indigenous climate forecasting in Samburu is not
                    a supplementary or informal practice. It is a <strong>primary system</strong>, one that communities
                    rely on, especially when formal early warning systems prove unreliable or inaccessible.
                </p>
                <blockquote class="ik-quote">
                    "For them, it works because that is what they have been using. They become experts. Even what they
                    say, and mostly what the government says, they give in small differences. So it also works, the
                    transmission of knowledge from one generation to another."
                </blockquote>
                <p class="ik-source">Research participant, Samburu County (qualitative interview, 2026)</p>
            </div>
            <div class="ik-context-stats">
                <div class="ik-stat">
                    <span class="ik-stat-num">12</span>
                    <span class="ik-stat-label">Documented indicators</span>
                </div>
                <div class="ik-stat">
                    <span class="ik-stat-num">2</span>
                    <span class="ik-stat-label">Knowledge tiers</span>
                </div>
                <div class="ik-stat">
                    <span class="ik-stat-num">4</span>
                    <span class="ik-stat-label">Indicator categories</span>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Tiered System Explainer -->
<section class="page-section ik-alt-bg">
    <div class="container">
        <div class="section-header">
            <h2>A Tiered Knowledge System</h2>
            <p>
                The research identified two distinct tiers of indigenous forecasting in Samburu (§4.2.1).
                Ordinary elders observe animal, weather, land, and plant indicators. When greater certainty
                is needed, communities consult specialist elders who read celestial and spiritual signs.
            </p>
        </div>

        <div class="tier-grid">
            <div class="tier-card tier-general">
                <div class="tier-badge">Tier 1: General Elders</div>
                <h3>Environmental Observation</h3>
                <p>
                    Ordinary community elders observe and interpret everyday environmental signals,
                    including animal behaviour, cloud and sky patterns, land conditions, and vegetation changes.
                    These observations are widely shared within the community and inform day-to-day
                    pastoral decisions such as when to move livestock or begin destocking.
                </p>
                <div class="tier-categories">
                    <span>Animal Behaviour</span>
                    <span>Weather &amp; Sky</span>
                    <span>Land &amp; Temperature</span>
                    <span>Vegetation &amp; Plants</span>
                </div>
            </div>

            <div class="tier-card tier-specialist">
                <div class="tier-badge tier-badge-specialist">Tier 2: Specialist Elders</div>
                <h3>Celestial &amp; Spiritual Forecasting</h3>
                <p>
                    A distinct class of specialist elders, and in the most remote communities, divine
                    seers, provides advanced forecasting through the reading of star positions, celestial
                    movements, and spiritual signs. This tier is consulted when general indicators are
                    ambiguous, and serves as the <strong>primary warning source</strong> for communities
                    completely beyond the reach of formal EWS.
                </p>
                <div class="tier-categories">
                    <span>Celestial Signs</span>
                    <span>Spiritual Readings</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tier 1: Indicators by Category -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Tier 1: Environmental Indicators</h2>
            <p>Ten indicators observed by community elders and pastoralists, documented in §4.2.1 and Table 4.2 of the research.</p>
        </div>

        <?php foreach ($categoryMeta as $catKey => $cat): ?>
        <?php if (empty($byCategory[$catKey])) continue; ?>

        <div class="ik-category-block">
            <div class="ik-category-header" style="border-color:<?= $cat['color'] ?>;">
                <span class="ik-category-icon"><?= $cat['icon'] ?></span>
                <h3 style="color:<?= $cat['color'] ?>;"><?= $cat['label'] ?></h3>
                <span class="ik-category-count"><?= count($byCategory[$catKey]) ?> indicator<?= count($byCategory[$catKey]) > 1 ? 's' : '' ?></span>
            </div>

            <div class="ik-indicators-grid">
                <?php foreach ($byCategory[$catKey] as $ind): ?>
                <div class="ik-indicator-card" style="--accent:<?= $cat['color'] ?>;">
                    <div class="ik-indicator-header">
                        <h4><?= htmlspecialchars($ind['indicator']) ?></h4>
                        <span class="ik-status ik-status-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $ind['status'] ?? 'unknown'))) ?>">
                            <?= htmlspecialchars($ind['status'] ?? 'Monitored') ?>
                        </span>
                    </div>
                    <p class="ik-indicator-note"><?= htmlspecialchars($ind['community_note']) ?></p>
                    <div class="ik-indicator-footer">
                        <span class="ik-reliability ik-reliability-<?= strtolower($ind['reliability'] ?? 'medium') ?>">
                            <?= htmlspecialchars($ind['reliability'] ?? 'Medium') ?> reliability
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
</section>

<!-- Tier 2: Specialist Indicators -->
<?php if (!empty($specialist)): ?>
<section class="page-section ik-specialist-bg">
    <div class="container">
        <div class="section-header">
            <h2>Tier 2: Specialist Indicators</h2>
            <p>
                Advanced forecasting by specialist elders and divine seers. Consulted when general indicators
                are ambiguous or insufficient. The primary warning source for the most remote Samburu communities
                beyond the reach of any formal EWS channel (§4.2.1).
            </p>
        </div>

        <div class="ik-specialist-grid">
            <?php foreach ($specialist as $ind): ?>
            <div class="ik-specialist-card">
                <div class="ik-specialist-badge">
                    <?= $ind['category'] === 'celestial' ? 'Celestial' : 'Spiritual' ?>
                </div>
                <h3><?= htmlspecialchars($ind['indicator']) ?></h3>
                <p><?= htmlspecialchars($ind['community_note']) ?></p>
                <div class="ik-indicator-footer" style="margin-top:var(--sp-md);">
                    <span class="ik-reliability ik-reliability-high">
                        <?= htmlspecialchars($ind['reliability'] ?? 'High') ?> reliability
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="ik-specialist-note">
            <strong>Research note (§4.2.1):</strong> Specialist elders who read celestial signs are identified
            as a distinct class from ordinary elders. Divine seers in the deepest and most remote communities
            "foresee the future climatic conditions of local areas" and represent the main source of warning
            where no formal infrastructure exists.
        </div>
    </div>
</section>
<?php endif; ?>

<!-- STS Framework Insight -->
<section class="page-section">
    <div class="container">
        <div class="ik-sts-box">
            <h3>Socio-Technical Systems Perspective (§4.2.2)</h3>
            <p>
                From an STS framework perspective, the indigenous knowledge system is not a competitor to
                formal EWS, it is a <strong>functioning social subsystem</strong> that operates in parallel.
                The failure of formal EWS to engage with, validate, or integrate this system represents a
                missed opportunity for alignment.
            </p>
            <p>
                Rather than competing with indigenous knowledge, a well-aligned EWS would complement it.
                The study recommends combining scientific forecasts with elder observations in the same
                warning message, verified by a joint local committee of elders, chiefs, and government
                representatives, so that communities hear a single, trusted, multi-source message
                (Recommendation 1 &amp; 5, §5.3).
            </p>
            <a href="findings.php#sectionRecs" class="btn btn-primary btn-sm" style="margin-top:var(--sp-md);">
                View All Recommendations →
            </a>
        </div>
    </div>
</section>

<style>
/* Context banner */
.ik-context-banner {
    display: grid;
    grid-template-columns: 1fr 220px;
    gap: var(--sp-2xl);
    background: var(--clr-primary);
    color: #fff;
    border-radius: var(--radius-xl);
    padding: var(--sp-2xl);
    align-items: start;
}
.ik-context-text h2 {
    font-size: var(--fs-xl);
    font-weight: var(--fw-bold);
    margin-bottom: var(--sp-md);
}
.ik-context-text p {
    font-size: var(--fs-sm);
    line-height: 1.8;
    opacity: .9;
    margin-bottom: var(--sp-md);
}
.ik-quote {
    border-left: 3px solid var(--clr-accent);
    margin: 0 0 var(--sp-sm);
    padding: var(--sp-sm) var(--sp-md);
    font-style: italic;
    font-size: var(--fs-sm);
    line-height: 1.7;
    opacity: .95;
}
.ik-source {
    font-size: var(--fs-xs);
    opacity: .7;
    margin: 0;
}
.ik-context-stats {
    display: flex;
    flex-direction: column;
    gap: var(--sp-md);
    background: rgba(255,255,255,.1);
    border-radius: var(--radius-lg);
    padding: var(--sp-lg);
}
.ik-stat {
    text-align: center;
}
.ik-stat-num {
    display: block;
    font-size: var(--fs-3xl);
    font-weight: var(--fw-black);
    line-height: 1;
}
.ik-stat-label {
    font-size: var(--fs-xs);
    opacity: .75;
    text-transform: uppercase;
    letter-spacing: .07em;
}

/* Tier cards */
.ik-alt-bg { background: var(--clr-bg); }
.tier-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-lg);
}
.tier-card {
    border-radius: var(--radius-lg);
    padding: var(--sp-xl);
    box-shadow: var(--shadow-sm);
}
.tier-general {
    background: var(--clr-surface);
    border-top: 4px solid var(--clr-primary);
}
.tier-specialist {
    background: #1a0a2e;
    color: #e8d5ff;
    border-top: 4px solid #9b59b6;
}
.tier-badge {
    display: inline-block;
    font-size: var(--fs-xs);
    font-weight: var(--fw-semi);
    padding: 3px 12px;
    border-radius: var(--radius-pill);
    background: var(--clr-primary-pale);
    color: var(--clr-primary);
    margin-bottom: var(--sp-md);
    text-transform: uppercase;
    letter-spacing: .06em;
}
.tier-badge-specialist {
    background: rgba(155,89,182,.25);
    color: #c39bd3;
}
.tier-card h3 {
    font-size: var(--fs-lg);
    font-weight: var(--fw-bold);
    margin-bottom: var(--sp-md);
}
.tier-specialist h3 { color: #fff; }
.tier-card p {
    font-size: var(--fs-sm);
    line-height: 1.8;
}
.tier-specialist p { color: #c8a8e9; }
.tier-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: var(--sp-lg);
}
.tier-categories span {
    font-size: var(--fs-xs);
    padding: 3px 10px;
    border-radius: var(--radius-pill);
    background: var(--clr-primary-pale);
    color: var(--clr-primary);
    font-weight: var(--fw-medium);
}
.tier-specialist .tier-categories span {
    background: rgba(155,89,182,.2);
    color: #c39bd3;
}

/* Category blocks */
.ik-category-block {
    margin-bottom: var(--sp-2xl);
}
.ik-category-header {
    display: flex;
    align-items: center;
    gap: var(--sp-md);
    border-left: 5px solid;
    padding-left: var(--sp-md);
    margin-bottom: var(--sp-lg);
}
.ik-category-icon { font-size: var(--fs-2xl); }
.ik-category-header h3 {
    font-size: var(--fs-lg);
    font-weight: var(--fw-bold);
    flex: 1;
    margin: 0;
}
.ik-category-count {
    font-size: var(--fs-xs);
    color: var(--clr-text-muted);
    background: var(--clr-bg-alt);
    padding: 3px 10px;
    border-radius: var(--radius-pill);
}
.ik-indicators-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--sp-md);
}

/* Indicator cards */
.ik-indicator-card {
    background: var(--clr-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border-top: 3px solid var(--accent, var(--clr-primary));
    padding: var(--sp-lg);
    transition: transform var(--tr-base), box-shadow var(--tr-base);
}
.ik-indicator-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.ik-indicator-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--sp-sm);
    margin-bottom: var(--sp-sm);
}
.ik-indicator-header h4 {
    font-size: var(--fs-base);
    font-weight: var(--fw-semi);
    color: var(--clr-text);
    margin: 0;
    line-height: 1.4;
    flex: 1;
}
.ik-status {
    font-size: var(--fs-xs);
    padding: 3px 8px;
    border-radius: var(--radius-pill);
    white-space: nowrap;
    font-weight: var(--fw-medium);
    flex-shrink: 0;
}
.ik-status-deteriorating,
.ik-status-early-movement-observed,
.ik-status-drying-rapidly,
.ik-status-above-normal,
.ik-status-sparse----browning-early,
.ik-status-observed,
.ik-status-drought-signals-present { background: var(--clr-danger-light); color: var(--clr-danger); }
.ik-status-monitored,
.ik-status-unusual-changes-noted,
.ik-status-specialist-assessment,
.ik-status-consulted-in-remote-areas { background: var(--clr-warning-light); color: var(--clr-warning); }
.ik-status-dry-season-clouds-dominant { background: var(--clr-info-light); color: var(--clr-info); }
.ik-indicator-note {
    font-size: var(--fs-sm);
    color: var(--clr-text-muted);
    line-height: 1.7;
    margin: 0 0 var(--sp-md);
}
.ik-indicator-footer {
    display: flex;
    align-items: center;
    gap: var(--sp-sm);
}
.ik-reliability {
    font-size: var(--fs-xs);
    padding: 2px 10px;
    border-radius: var(--radius-pill);
    font-weight: var(--fw-medium);
}
.ik-reliability-high   { background: var(--clr-success-light); color: var(--clr-success); }
.ik-reliability-medium { background: var(--clr-warning-light); color: var(--clr-warning); }
.ik-reliability-low    { background: var(--clr-danger-light);  color: var(--clr-danger); }
.ik-reliability-high-\(within-specialist-community\),
.ik-reliability-high-\(within-deepest-communities\) {
    background: var(--clr-success-light); color: var(--clr-success);
}

/* Specialist section */
.ik-specialist-bg {
    background: #0d0720;
}
.ik-specialist-bg .section-header h2 { color: #fff; }
.ik-specialist-bg .section-header p  { color: #a68cc8; }
.ik-specialist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: var(--sp-lg);
}
.ik-specialist-card {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(155,89,182,.3);
    border-radius: var(--radius-lg);
    padding: var(--sp-xl);
    color: #e0c8ff;
}
.ik-specialist-badge {
    display: inline-block;
    font-size: var(--fs-xs);
    padding: 3px 12px;
    border-radius: var(--radius-pill);
    background: rgba(155,89,182,.25);
    color: #c39bd3;
    font-weight: var(--fw-semi);
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-bottom: var(--sp-md);
}
.ik-specialist-card h3 {
    font-size: var(--fs-lg);
    font-weight: var(--fw-bold);
    color: #fff;
    margin-bottom: var(--sp-md);
}
.ik-specialist-card p {
    font-size: var(--fs-sm);
    line-height: 1.8;
    color: #b89fd4;
}
.ik-specialist-note {
    margin-top: var(--sp-xl);
    padding: var(--sp-lg);
    background: rgba(155,89,182,.1);
    border-left: 4px solid #9b59b6;
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    font-size: var(--fs-sm);
    color: #b89fd4;
    line-height: 1.7;
}
.ik-specialist-note strong { color: #e0c8ff; }

/* STS insight box */
.ik-sts-box {
    background: var(--clr-primary-pale);
    border-left: 5px solid var(--clr-primary);
    border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
    padding: var(--sp-xl) var(--sp-2xl);
    max-width: 860px;
    margin: 0 auto;
}
.ik-sts-box h3 {
    font-size: var(--fs-lg);
    font-weight: var(--fw-bold);
    color: var(--clr-primary);
    margin-bottom: var(--sp-md);
}
.ik-sts-box p {
    font-size: var(--fs-sm);
    line-height: 1.8;
    color: var(--clr-text);
    margin-bottom: var(--sp-md);
}

/* Responsive */
@media (max-width: 768px) {
    .ik-context-banner { grid-template-columns: 1fr; }
    .ik-context-stats  { flex-direction: row; justify-content: space-around; }
    .tier-grid         { grid-template-columns: 1fr; }
    .ik-indicators-grid, .ik-specialist-grid { grid-template-columns: 1fr; }
    .ik-sts-box        { padding: var(--sp-lg); }
}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
