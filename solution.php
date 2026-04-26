<?php
/**
 * Samburu EWS: Solution
 *
 * Presents the platform's approach: recommendation engine,
 * multi-channel dissemination, and indigenous knowledge integration.
 * Loads recommendations.json.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'Our Solution';
$recs = DataRepository::load('recommendations.json') ?? [];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Our Solution</h1>
        <p>A recommender and educative platform that turns drought data into life-saving action through the right channel, in the right language, at the right time.</p>
    </div>
</section>

<!-- Approach -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Platform Approach</h2>
            <p>Three pillars that make <?= SITE_NAME ?> effective where previous systems have fallen short.</p>
        </div>
        <div class="grid grid-3 grid-auto">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-primary-pale);color:var(--clr-primary);"></div>
                    <h3 class="card-title">Risk Engine</h3>
                </div>
                <div class="card-body">
                    <p>A weighted scoring algorithm that merges NDMA scientific data, KMD seasonal forecasts, and indigenous community indicators into a single composite risk score (0 to 100) mapped to five alert levels.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-warning-light);color:var(--clr-warning);"></div>
                    <h3 class="card-title">Multi-Channel Delivery</h3>
                </div>
                <div class="card-body">
                    <p>Warnings are automatically formatted for WhatsApp, Facebook, vernacular radio scripts (30s and 60s), and USSD menus, ensuring every stakeholder group receives alerts via their preferred channel.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-info-light);color:var(--clr-info);"></div>
                    <h3 class="card-title">Indigenous Integration</h3>
                </div>
                <div class="card-body">
                    <p>Traditional indicators (star patterns, livestock behaviour, wild-fruit timing, wind direction) are validated and presented alongside scientific data to build trust and cultural relevance.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it differs -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>What Makes This Different</h2>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Traditional EWS</th>
                        <th><?= SITE_NAME ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Language</td><td>English / formal Swahili</td><td>Samburu vernacular + Swahili + English</td></tr>
                    <tr><td>Channels</td><td>Official bulletins, email</td><td>Radio, USSD, WhatsApp, Facebook, SMS</td></tr>
                    <tr><td>Actionability</td><td>Describes the threat only</td><td>Includes specific recommended actions per stakeholder</td></tr>
                    <tr><td>Indigenous knowledge</td><td>Ignored</td><td>Integrated and validated alongside scientific data</td></tr>
                    <tr><td>Audience</td><td>Government officials</td><td>Pastoralists, chiefs, radio stations, NGOs, government</td></tr>
                    <tr><td>Feedback</td><td>One-way broadcast</td><td>Two-way via USSD feedback menu</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Recommendations -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Evidence-Based Recommendations</h2>
            <p>Seven evidence-based recommendations derived from community interviews and barrier analysis.</p>
        </div>
        <div class="grid grid-auto">
            <?php foreach ($recs as $i => $r):
                $priorityClass = match($r['priority']) {
                    'Critical' => 'badge-red',
                    'High'     => 'badge-amber',
                    default    => 'badge-blue',
                };
            ?>
            <div class="card" style="border-left:4px solid var(--clr-primary);">
                <div class="card-header">
                    <div class="card-icon"><?= $i + 1 ?></div>
                    <div>
                        <h3 class="card-title"><?= htmlspecialchars($r['title']) ?></h3>
                        <span class="badge <?= $priorityClass ?>"><?= htmlspecialchars($r['priority']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p><?= htmlspecialchars($r['description']) ?></p>
                    <?php if (!empty($r['stakeholder_actions'])): ?>
                    <ul style="list-style:disc;padding-left:1.2rem;margin-top:var(--sp-sm);font-size:var(--fs-sm);color:var(--clr-text-muted);line-height:1.8;">
                        <?php foreach ($r['stakeholder_actions'] as $who => $what): ?>
                        <li><strong><?= htmlspecialchars($who) ?>:</strong> <?= htmlspecialchars($what) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if (!empty($r['addresses_barriers'])): ?>
                    <div style="margin-top:var(--sp-sm);display:flex;flex-wrap:wrap;gap:var(--sp-xs);">
                        <span style="font-size:var(--fs-xs);color:var(--clr-text-muted);">Addresses:</span>
                        <?php foreach ($r['addresses_barriers'] as $bid): ?>
                        <span class="badge badge-neutral"><?= htmlspecialchars($bid) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Architecture Diagram (HTML/CSS, no external images) -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>System Architecture</h2>
            <p>How data flows from raw inputs to actionable alerts for each stakeholder group.</p>
        </div>

        <div class="arch-diagram" aria-label="System architecture diagram">

            <!-- Row 1: Data Inputs -->
            <div class="arch-row">
                <div class="arch-label">DATA INPUTS</div>
                <div class="arch-nodes">
                    <div class="arch-node arch-node-blue">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">NDMA Bulletin</div>
                        <div class="arch-node-sub">NDVI · Rainfall · Livestock · Water · Food Security</div>
                    </div>
                    <div class="arch-node arch-node-blue">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">KMD Forecast</div>
                        <div class="arch-node-sub">Seasonal outlook · Rainfall probability · Onset</div>
                    </div>
                    <div class="arch-node arch-node-amber">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">Indigenous Indicators</div>
                        <div class="arch-node-sub">Star patterns · Livestock behaviour · Botanical signals</div>
                    </div>
                </div>
            </div>

            <div class="arch-arrow-down">▼</div>

            <!-- Row 2: Processing -->
            <div class="arch-row">
                <div class="arch-label">PROCESSING</div>
                <div class="arch-nodes arch-nodes-center">
                    <div class="arch-node arch-node-green arch-node-wide">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">Risk Engine (PHP)</div>
                        <div class="arch-node-sub">
                            Weighted scoring: NDVI 20% · Rainfall 20% · Livestock 20% ·
                            Water 15% · Food 10% · Indigenous 15%
                            Composite Score 0 to 100, Risk Level
                        </div>
                    </div>
                </div>
            </div>

            <div class="arch-arrow-down">▼</div>

            <!-- Row 3: Alert Level -->
            <div class="arch-row">
                <div class="arch-label">ALERT LEVEL</div>
                <div class="arch-nodes">
                    <div class="arch-node arch-node-level" style="background:var(--clr-primary-pale);border-color:var(--clr-primary);">Normal</div>
                    <div class="arch-node arch-node-level" style="background:var(--clr-info-light);border-color:var(--clr-info);">Watch</div>
                    <div class="arch-node arch-node-level" style="background:var(--clr-warning-light);border-color:var(--clr-warning);">Alert</div>
                    <div class="arch-node arch-node-level" style="background:var(--clr-danger-light);border-color:var(--clr-danger);">Alarm</div>
                    <div class="arch-node arch-node-level" style="background:#5a0000;border-color:#ff2020;color:#fff;">Emergency</div>
                </div>
            </div>

            <div class="arch-arrow-down">▼</div>

            <!-- Row 4: Dissemination -->
            <div class="arch-row">
                <div class="arch-label">CHANNELS</div>
                <div class="arch-nodes">
                    <div class="arch-node arch-node-channel">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">WhatsApp</div>
                        <div class="arch-node-sub">Community groups · Intermediaries</div>
                    </div>
                    <div class="arch-node arch-node-channel">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">Community Radio</div>
                        <div class="arch-node-sub">30s & 60s vernacular scripts</div>
                    </div>
                    <div class="arch-node arch-node-channel">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">USSD *384#</div>
                        <div class="arch-node-sub">Feature phones · No data needed</div>
                    </div>
                    <div class="arch-node arch-node-channel">
                        <div class="arch-node-icon"></div>
                        <div class="arch-node-title">Social Media</div>
                        <div class="arch-node-sub">Facebook · X (Twitter)</div>
                    </div>
                </div>
            </div>

            <div class="arch-arrow-down">▼</div>

            <!-- Row 5: Stakeholders -->
            <div class="arch-row">
                <div class="arch-label">STAKEHOLDERS</div>
                <div class="arch-nodes">
                    <div class="arch-node arch-node-stake"><strong>Government</strong></div>
                    <div class="arch-node arch-node-stake"><strong>NGOs</strong></div>
                    <div class="arch-node arch-node-stake"><strong>Pastoralists</strong></div>
                    <div class="arch-node arch-node-stake"><strong>Intermediaries</strong></div>
                    <div class="arch-node arch-node-stake"><strong>Radio</strong></div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
.arch-diagram {
    display: flex;
    flex-direction: column;
    gap: var(--sp-sm);
    max-width: 960px;
    margin-inline: auto;
}
.arch-row {
    display: flex;
    align-items: flex-start;
    gap: var(--sp-md);
}
.arch-label {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    transform: rotate(180deg);
    font-size: var(--fs-xs);
    font-weight: var(--fw-bold);
    color: var(--clr-text-muted);
    text-transform: uppercase;
    letter-spacing: .08em;
    min-width: 60px;
    text-align: center;
    padding: var(--sp-sm) 0;
}
.arch-nodes {
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-sm);
    flex: 1;
}
.arch-nodes-center { justify-content: center; }
.arch-node {
    flex: 1;
    min-width: 140px;
    padding: var(--sp-md);
    border-radius: var(--radius-lg);
    border: 2px solid;
    text-align: center;
    font-size: var(--fs-sm);
}
.arch-node-wide { min-width: 80%; max-width: 100%; }
.arch-node-icon { font-size: var(--fs-2xl); margin-bottom: var(--sp-xs); }
.arch-node-title { font-weight: var(--fw-semi); margin-bottom: var(--sp-xs); }
.arch-node-sub { font-size: var(--fs-xs); color: var(--clr-text-muted); line-height: 1.5; }

.arch-node-blue   { background: var(--clr-info-light);     border-color: var(--clr-info); }
.arch-node-amber  { background: var(--clr-warning-light);  border-color: var(--clr-warning); }
.arch-node-green  { background: var(--clr-primary-pale);   border-color: var(--clr-primary); }
.arch-node-level  { flex: 1; min-width: 80px; padding: var(--sp-sm); font-weight: var(--fw-semi); font-size: var(--fs-sm); }
.arch-node-channel { background: var(--clr-surface);  border-color: var(--clr-border); }
.arch-node-stake  { background: var(--clr-bg); border-color: var(--clr-border-light); color: var(--clr-text); font-size: var(--fs-sm); }

.arch-arrow-down {
    text-align: center;
    font-size: var(--fs-xl);
    color: var(--clr-primary-light);
    margin-left: 76px;
}

@media (max-width: 600px) {
    .arch-label { display: none; }
    .arch-row { flex-direction: column; }
    .arch-arrow-down { margin-left: 0; }
}
</style>

<section class="page-section text-center">
    <div class="container">
        <a href="findings.php" class="btn btn-primary btn-lg">View Research Findings</a>
        <a href="current-alert.php" class="btn btn-accent btn-lg" style="margin-left:var(--sp-sm);">Current Alert</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
