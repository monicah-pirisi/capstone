<?php
/**
 * Samburu EWS — Research Findings
 * Qualitative thematic analysis from semi-structured interviews.
 */
require __DIR__ . '/config.php';

$pageTitle = 'Research Findings';
require __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────── -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Research Findings</h1>
        <p>Thematic analysis of semi-structured interviews exploring the socio-technical barriers to early warning system effectiveness in Samburu County, Kenya.</p>
    </div>
</section>

<!-- ── Loading overlay ───────────────────────────── -->
<div id="loadingOverlay" class="findings-loading">
    <div class="spinner"></div>
    <p>Loading findings…</p>
</div>

<!-- ── Study Overview ────────────────────────────── -->
<section class="page-section" id="sectionMeta" style="display:none;padding-top:var(--sp-xl);">
    <div class="container">
        <div class="study-overview-card" id="studyOverview"></div>
    </div>
</section>

<!-- ── Seven Themes ──────────────────────────────── -->
<section class="page-section" id="sectionThemes" style="display:none;">
    <div class="container">
        <div class="section-header">
            <h2>Seven Emergent Themes</h2>
            <p>Seven themes emerged from the thematic analysis, each mapped to a research question and interpreted through the Socio-Technical Systems (STS) framework.</p>
        </div>
        <div id="themeCards" class="themes-grid"></div>
    </div>
</section>

<!-- ── Barriers ──────────────────────────────────── -->
<section class="page-section findings-alt-bg" id="sectionBarriers" style="display:none;">
    <div class="container">
        <div class="section-header">
            <h2>Barriers to EWS Utilization</h2>
            <p>Seven interconnected barriers prevent formal early warning systems from being received, understood, trusted, and acted upon by pastoralist communities.</p>
        </div>
        <div id="barrierCards" class="barriers-grid"></div>
    </div>
</section>

<!-- ── Recommendations ───────────────────────────── -->
<section class="page-section" id="sectionRecs" style="display:none;">
    <div class="container">
        <div class="section-header">
            <h2>Recommendations</h2>
            <p>Seven evidence-based recommendations drawn from Chapter 5 of the study, each mapped to responsible actors and the barriers they address.</p>
        </div>
        <div id="recCards" class="recs-grid"></div>
    </div>
</section>

<style>
/* ── Loading ──────────────────────────────────────── */
.findings-loading {
    text-align: center;
    padding: var(--sp-3xl) 0;
    color: var(--clr-text-muted);
}
.spinner {
    width: 40px; height: 40px;
    margin: 0 auto var(--sp-md);
    border: 4px solid var(--clr-border-light);
    border-top-color: var(--clr-primary);
    border-radius: 50%;
    animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Study overview ───────────────────────────────── */
.study-overview-card {
    background: var(--clr-primary);
    color: #fff;
    border-radius: var(--radius-xl);
    padding: var(--sp-xl) var(--sp-2xl);
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-lg);
    align-items: flex-start;
}
.study-overview-card .so-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-md) var(--sp-2xl);
    flex: 1 1 100%;
}
.so-meta-item strong {
    display: block;
    font-size: var(--fs-xs);
    text-transform: uppercase;
    letter-spacing: .07em;
    opacity: .75;
    margin-bottom: 2px;
}
.so-meta-item span {
    font-size: var(--fs-md);
    font-weight: var(--fw-semi);
}
.so-note {
    flex: 1 1 100%;
    font-size: var(--fs-sm);
    opacity: .85;
    border-top: 1px solid rgba(255,255,255,.2);
    padding-top: var(--sp-md);
    margin-top: var(--sp-xs);
}

/* ── Themes grid ─────────────────────────────────── */
.themes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: var(--sp-lg);
}
.theme-card {
    background: var(--clr-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border-top: 4px solid var(--clr-primary);
    overflow: hidden;
    transition: transform var(--tr-base), box-shadow var(--tr-base);
}
.theme-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.theme-card-header {
    padding: var(--sp-lg) var(--sp-lg) var(--sp-md);
    display: flex;
    align-items: flex-start;
    gap: var(--sp-md);
}
.theme-number {
    flex-shrink: 0;
    width: 36px; height: 36px;
    background: var(--clr-primary);
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: var(--fs-sm);
    font-weight: var(--fw-bold);
}
.theme-card-header h3 {
    font-size: var(--fs-base);
    font-weight: var(--fw-semi);
    color: var(--clr-text);
    margin: 0;
    line-height: 1.4;
}
.theme-rq-badge {
    margin-top: 4px;
    display: inline-block;
    font-size: var(--fs-xs);
    padding: 2px 8px;
    border-radius: var(--radius-pill);
    background: var(--clr-primary-pale);
    color: var(--clr-primary);
    font-weight: var(--fw-medium);
}
.theme-card-body {
    padding: 0 var(--sp-lg) var(--sp-md);
}
.theme-summary {
    font-size: var(--fs-sm);
    color: var(--clr-text-muted);
    line-height: 1.7;
    margin-bottom: var(--sp-md);
}
.theme-quote {
    background: var(--clr-bg);
    border-left: 3px solid var(--clr-accent);
    margin: 0 0 var(--sp-md);
    padding: var(--sp-sm) var(--sp-md);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    font-size: var(--fs-xs);
    color: var(--clr-text);
    font-style: italic;
    line-height: 1.6;
}
.theme-finding {
    font-size: var(--fs-xs);
    color: var(--clr-primary);
    font-weight: var(--fw-medium);
    background: var(--clr-primary-pale);
    border-radius: var(--radius-md);
    padding: var(--sp-xs) var(--sp-sm);
    line-height: 1.5;
}

/* ── Barriers ────────────────────────────────────── */
.findings-alt-bg { background: var(--clr-bg); }
.barriers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: var(--sp-lg);
}
.barrier-card {
    background: var(--clr-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border-left: 5px solid var(--clr-danger);
    overflow: hidden;
    transition: transform var(--tr-base), box-shadow var(--tr-base);
}
.barrier-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.barrier-card-header {
    padding: var(--sp-lg) var(--sp-lg) var(--sp-sm);
    display: flex;
    align-items: flex-start;
    gap: var(--sp-md);
}
.barrier-rank {
    flex-shrink: 0;
    width: 32px; height: 32px;
    background: var(--clr-danger);
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: var(--fs-xs);
    font-weight: var(--fw-bold);
}
.barrier-card-header h3 {
    font-size: var(--fs-base);
    font-weight: var(--fw-semi);
    color: var(--clr-text);
    margin: 0;
    line-height: 1.4;
}
.barrier-card-body {
    padding: 0 var(--sp-lg) var(--sp-lg);
}
.barrier-desc {
    font-size: var(--fs-sm);
    color: var(--clr-text-muted);
    line-height: 1.7;
    margin-bottom: var(--sp-md);
}
.barrier-evidence {
    background: #fffbf0;
    border-left: 3px solid var(--clr-accent);
    padding: var(--sp-sm) var(--sp-md);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    margin-bottom: var(--sp-md);
}
.barrier-evidence-label {
    font-size: var(--fs-xs);
    font-weight: var(--fw-semi);
    color: var(--clr-accent-dark);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: var(--sp-xs);
}
.barrier-evidence p {
    font-size: var(--fs-xs);
    color: var(--clr-text);
    font-style: italic;
    line-height: 1.6;
    margin: 0;
}
.barrier-groups {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: var(--sp-md);
}
.group-tag {
    font-size: var(--fs-xs);
    padding: 3px 10px;
    border-radius: var(--radius-pill);
    background: var(--clr-danger-light);
    color: var(--clr-danger-dark);
    font-weight: var(--fw-medium);
}
.barrier-implication {
    background: var(--clr-warning-light);
    border-radius: var(--radius-md);
    padding: var(--sp-sm) var(--sp-md);
    font-size: var(--fs-xs);
    color: var(--clr-text);
    line-height: 1.6;
}
.barrier-implication strong {
    color: var(--clr-warning);
    display: block;
    margin-bottom: 2px;
}

/* ── Recommendations ─────────────────────────────── */
.recs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: var(--sp-lg);
}
.rec-card {
    background: var(--clr-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border-top: 4px solid var(--clr-primary-light);
    overflow: hidden;
    transition: transform var(--tr-base), box-shadow var(--tr-base);
    display: flex;
    flex-direction: column;
}
.rec-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.rec-card-header {
    padding: var(--sp-lg) var(--sp-lg) var(--sp-sm);
    display: flex;
    align-items: flex-start;
    gap: var(--sp-md);
}
.rec-number {
    flex-shrink: 0;
    width: 36px; height: 36px;
    background: var(--clr-primary-light);
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: var(--fs-sm);
    font-weight: var(--fw-bold);
}
.rec-card-header-text h3 {
    font-size: var(--fs-base);
    font-weight: var(--fw-semi);
    color: var(--clr-text);
    margin: 0 0 4px;
    line-height: 1.4;
}
.rec-card-body {
    padding: 0 var(--sp-lg) var(--sp-lg);
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: var(--sp-md);
}
.rec-desc {
    font-size: var(--fs-sm);
    color: var(--clr-text-muted);
    line-height: 1.7;
}
.rec-responsible {
    font-size: var(--fs-xs);
    background: var(--clr-primary-pale);
    border-radius: var(--radius-md);
    padding: var(--sp-xs) var(--sp-sm);
    color: var(--clr-primary-dark);
    line-height: 1.5;
}
.rec-responsible strong {
    display: block;
    color: var(--clr-primary);
    margin-bottom: 2px;
}
.rec-barrier-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.rec-barrier-tag-label {
    font-size: var(--fs-xs);
    color: var(--clr-text-muted);
    margin-right: 2px;
}
.rec-barrier-tag {
    font-size: var(--fs-xs);
    padding: 3px 10px;
    border-radius: var(--radius-pill);
    background: var(--clr-bg-alt);
    color: var(--clr-text-muted);
    font-weight: var(--fw-medium);
}

/* ── Priority badges ─────────────────────────────── */
.priority-badge {
    display: inline-block;
    font-size: var(--fs-xs);
    padding: 2px 10px;
    border-radius: var(--radius-pill);
    font-weight: var(--fw-semi);
    letter-spacing: .04em;
}
.priority-critical { background: var(--clr-danger-light); color: var(--clr-danger); }
.priority-high     { background: var(--clr-warning-light); color: var(--clr-warning); }
.priority-medium   { background: var(--clr-info-light);    color: var(--clr-info); }

/* ── Section header ──────────────────────────────── */
.section-header {
    text-align: center;
    margin-bottom: var(--sp-2xl);
}
.section-header h2 {
    font-size: var(--fs-2xl);
    font-weight: var(--fw-bold);
    color: var(--clr-text);
    margin-bottom: var(--sp-sm);
}
.section-header p {
    color: var(--clr-text-muted);
    max-width: 680px;
    margin: 0 auto;
    font-size: var(--fs-md);
    line-height: 1.7;
}

@media (max-width: 640px) {
    .themes-grid, .barriers-grid, .recs-grid {
        grid-template-columns: 1fr;
    }
    .study-overview-card { padding: var(--sp-lg); }
}
</style>

<?php
$pageScripts = ['assets/js/findings.js'];
require __DIR__ . '/includes/footer.php';
?>
