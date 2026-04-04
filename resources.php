<?php
/**
 * Samburu EWS — Education Hub
 *
 * Complete education hub: what EWS is, drought phases, risk scoring,
 * data sources, indigenous indicators, pastoralist action guide,
 * how to use the system, and external links.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle  = 'Education Hub';
$indigenous = DataRepository::load('indigenous_indicators.json');
$kmd        = DataRepository::load('kmd_summary.json');

require __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────────── -->
<section class="hero" style="padding:var(--sp-3xl) 0;">
    <div class="container">
        <h1>Education Hub</h1>
        <p>Everything you need to understand the Samburu Early Warning System — how it works, what the data means, and how to act on it.</p>
    </div>
</section>

<!-- ── 1. What is an EWS ────────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>What is an Early Warning System?</h2>
            <p>An Early Warning System turns environmental data into timely, actionable information for communities and decision-makers.</p>
        </div>

        <div class="grid grid-auto" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--sp-lg);">
            <div class="card text-center">
                <div class="card-icon" style="margin-inline:auto;background:var(--clr-success-light);color:var(--clr-success);"></div>
                <h3 class="card-title mt-md">Monitor</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Track rainfall, vegetation health, water availability, and livestock condition continuously.</p>
            </div>
            <div class="card text-center">
                <div class="card-icon" style="margin-inline:auto;background:var(--clr-info-light);color:var(--clr-info);"></div>
                <h3 class="card-title mt-md">Analyse</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Combine satellite science with indigenous knowledge to assess drought risk with local context.</p>
            </div>
            <div class="card text-center">
                <div class="card-icon" style="margin-inline:auto;background:var(--clr-warning-light);color:var(--clr-warning);"></div>
                <h3 class="card-title mt-md">Warn</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Send clear, phase-based alerts to communities via SMS, radio, WhatsApp, and USSD.</p>
            </div>
            <div class="card text-center">
                <div class="card-icon" style="margin-inline:auto;background:var(--clr-danger-light);color:var(--clr-danger);"></div>
                <h3 class="card-title mt-md">Respond</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Enable pastoralists, NGOs and government to act before crisis hits — not after.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── 2. Why Early Warning Matters ─────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Why Early Warning Matters</h2>
            <p>Acting early saves lives, livestock, and money. The evidence is clear.</p>
        </div>

        <div class="grid grid-3 grid-auto" style="max-width:800px;margin-inline:auto;text-align:center;">
            <div class="stat-card card">
                <div class="stat-value">3–6</div>
                <div class="stat-label">Months earlier warning</div>
            </div>
            <div class="stat-card card">
                <div class="stat-value">40%</div>
                <div class="stat-label">Less livestock loss</div>
            </div>
            <div class="stat-card card">
                <div class="stat-value">70%</div>
                <div class="stat-label">Cost savings</div>
            </div>
        </div>

        <p class="text-center text-muted mt-lg" style="max-width:600px;margin-inline:auto;">
            Studies show that for every <strong>$1</strong> invested in early warning, <strong>$7–23</strong> can be saved in disaster response costs.
        </p>
    </div>
</section>

<!-- ── 3. Drought Phases ─────────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Understanding Drought Phases</h2>
            <p>The NDMA uses a five-phase classification system. Each phase triggers specific community and government responses.</p>
        </div>

        <div class="grid grid-auto" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
            <?php
            $phases = [
                ['Normal',    'Conditions within seasonal norms. Continue routine monitoring.',    'var(--clr-primary-pale)',  'var(--clr-primary)'],
                ['Watch',     'Early signs of stress. Increase monitoring; start conservation.',   'var(--clr-info-light)',    'var(--clr-info)'],
                ['Alert',     'Clear deterioration. Activate response plans; begin destocking.',   'var(--clr-warning-light)', 'var(--clr-warning)'],
                ['Alarm',     'Severe conditions. Emergency destocking; mobilise relief.',         'var(--clr-danger-light)',  'var(--clr-danger)'],
                ['Emergency', 'Crisis. Full humanitarian response; coordinate evacuations.',       '#5a0000',                  '#fff'],
            ];
            foreach ($phases as $p): ?>
            <div class="card text-center" style="background:<?= $p[2] ?>;color:<?= $p[3] ?>;">
                <h3 style="margin:var(--sp-sm) 0;font-size:var(--fs-lg);"><?= $p[0] ?></h3>
                <p style="font-size:var(--fs-sm);opacity:.85;"><?= $p[1] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── 4. Risk Score ─────────────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>How to Read the Risk Score</h2>
        </div>
        <div class="card" style="max-width:700px;margin-inline:auto;">
            <p style="line-height:1.8;">The Samburu EWS risk score is a <strong>composite value from 0 to 100</strong>,
            calculated by weighting six indicators:</p>
            <div class="table-wrap mt-md">
                <table class="data-table">
                    <thead><tr><th>Indicator</th><th>Weight</th><th>Source</th></tr></thead>
                    <tbody>
                        <tr><td>Vegetation (NDVI)</td><td>20%</td><td>NDMA / satellite</td></tr>
                        <tr><td>Rainfall</td><td>20%</td><td>KMD</td></tr>
                        <tr><td>Livestock condition</td><td>15%</td><td>NDMA field reports</td></tr>
                        <tr><td>Water access</td><td>15%</td><td>NDMA / community scouts</td></tr>
                        <tr><td>Food security (FCS)</td><td>15%</td><td>NDMA / WFP</td></tr>
                        <tr><td>Indigenous indicators</td><td>15%</td><td>Community scouts / elders</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-md text-muted" style="font-size:var(--fs-sm);">
                A score of <strong>0</strong> means no drought stress; <strong>100</strong> means maximum crisis.
                The score maps directly to the phase chart above.
            </p>
        </div>
    </div>
</section>

<!-- ── 5. The Data We Use ────────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>The Data We Use</h2>
            <p>Samburu EWS is unique in combining two complementary knowledge systems.</p>
        </div>

        <div class="grid grid-2 grid-auto" style="max-width:900px;margin-inline:auto;">
            <div class="card" style="border-left:4px solid var(--clr-info);background:var(--clr-info-light);">
                <h3 class="card-title">Scientific Data</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">From satellites and weather stations:</p>
                <ul style="list-style:disc;padding-left:var(--sp-lg);font-size:var(--fs-sm);line-height:2;color:var(--clr-text);">
                    <li>Rainfall measurements (KMD gauges)</li>
                    <li>Vegetation health — NDVI index</li>
                    <li>Temperature and evapotranspiration</li>
                    <li>Water source levels and river flows</li>
                </ul>
            </div>
            <div class="card" style="border-left:4px solid var(--clr-primary);background:var(--clr-primary-pale);">
                <h3 class="card-title">Indigenous Knowledge</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">From community observers and elders:</p>
                <ul style="list-style:disc;padding-left:var(--sp-lg);font-size:var(--fs-sm);line-height:2;color:var(--clr-text);">
                    <li>Livestock behaviour and body condition</li>
                    <li>Plant flowering and fruiting patterns</li>
                    <li>Bird and insect migrations</li>
                    <li>Traditional weather signs and oral records</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ── 6. Indigenous Indicator Guide ────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Indigenous Indicator Guide</h2>
            <p>Traditional knowledge used by Samburu elders and scouts to forecast seasonal conditions — validated alongside satellite data.</p>
        </div>
        <div class="grid grid-2 grid-auto">
            <?php
            foreach ($indigenous as $ind): ?>
            <div class="card" style="border-left:4px solid var(--clr-accent);">
                <div class="card-header">
                    <div class="card-icon"></div>
                    <div>
                        <h3 class="card-title"><?= htmlspecialchars($ind['indicator']) ?></h3>
                        <span class="badge badge-amber"><?= htmlspecialchars($ind['status']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p style="font-size:var(--fs-sm);color:var(--clr-text-muted);"><?= htmlspecialchars($ind['community_note']) ?></p>
                    <p class="text-muted" style="font-size:var(--fs-xs);margin-top:var(--sp-xs);">
                        Reliability: <?= htmlspecialchars($ind['reliability']) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── 7. Pastoralist Action Guide ──────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Pastoralist Action Guide</h2>
            <p>Plain-language guidance on what pastoralists should do at each risk level.</p>
        </div>

        <div class="grid grid-3 grid-auto">

            <div class="card" style="border-top:4px solid var(--clr-primary);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-primary-pale);color:var(--clr-primary);"></div>
                    <div>
                        <h3 class="card-title" style="color:var(--clr-primary);">Low Risk — Normal</h3>
                        <span class="badge badge-green">Score 80–100</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:var(--fs-sm);margin-bottom:var(--sp-md);">Conditions within seasonal norms. No immediate threat detected.</p>
                    <ul style="list-style:none;font-size:var(--fs-sm);line-height:2.2;color:var(--clr-text);">
                        <li>Continue normal grazing rotation</li>
                        <li>Monitor water source levels weekly</li>
                        <li>Maintain herd at normal numbers</li>
                        <li>Check this platform monthly for updates</li>
                        <li>Save some livestock as reserve</li>
                        <li>Attend community drought briefings</li>
                    </ul>
                </div>
            </div>

            <div class="card" style="border-top:4px solid var(--clr-warning);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-warning-light);color:var(--clr-warning);"></div>
                    <div>
                        <h3 class="card-title" style="color:var(--clr-warning);">Moderate Risk — Alert</h3>
                        <span class="badge badge-amber">Score 50–79</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:var(--fs-sm);margin-bottom:var(--sp-md);">Clear signs of deterioration. Early action prevents larger losses.</p>
                    <ul style="list-style:none;font-size:var(--fs-sm);line-height:2.2;color:var(--clr-text);">
                        <li>Begin <strong>early destocking</strong> — sell weak animals first</li>
                        <li>Move herds toward known dry-season pastures</li>
                        <li>Reduce herd size by 20–30% if pasture is poor</li>
                        <li>Locate and note nearest water point distances</li>
                        <li>Contact your chief or ward rep to register</li>
                        <li>Listen to community radio for daily updates</li>
                    </ul>
                </div>
            </div>

            <div class="card" style="border-top:4px solid var(--clr-danger);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-danger-light);color:var(--clr-danger);"></div>
                    <div>
                        <h3 class="card-title" style="color:var(--clr-danger);">High Risk — Alarm / Emergency</h3>
                        <span class="badge badge-red">Score 0–49</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:var(--fs-sm);margin-bottom:var(--sp-md);">Severe or crisis conditions. Act immediately — every day matters.</p>
                    <ul style="list-style:none;font-size:var(--fs-sm);line-height:2.2;color:var(--clr-text);">
                        <li><strong>Sell or slaughter</strong> all weak/unproductive animals now</li>
                        <li>Move to emergency grazing areas with permission</li>
                        <li>Register with NDMA / County for relief support</li>
                        <li>Contact Kenya Red Cross: <strong>0800 723 253</strong> (free)</li>
                        <li>Keep children and elders near water points</li>
                        <li>Dial <strong>*384#</strong> on any phone for daily alert updates</li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="card mt-lg" style="background:var(--clr-primary-pale);border:1px solid var(--clr-primary-light);">
            <p style="font-size:var(--fs-sm);color:var(--clr-primary);line-height:1.8;margin:0;">
                <strong>Understanding uncertainty:</strong> No forecast is 100% certain. This platform combines
                multiple data sources to reduce uncertainty — but always cross-check with what you observe locally
                (grass condition, livestock behaviour, water levels). If your indigenous observations strongly
                contradict the forecast, report it to your community scout so the system can be improved.
            </p>
        </div>
    </div>
</section>

<!-- ── 8. How to Use This System ────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>How to Use This System</h2>
            <p>Different audiences access alerts through different channels — find the right one for you.</p>
        </div>

        <div class="grid grid-2 grid-auto" style="max-width:900px;margin-inline:auto;">
            <div class="card" style="border-top:4px solid var(--clr-primary);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-primary-pale);color:var(--clr-primary);"></div>
                    <div><h3 class="card-title">For Community Members</h3></div>
                </div>
                <div class="card-body">
                    <ul style="list-style:disc;padding-left:var(--sp-lg);font-size:var(--fs-sm);line-height:2.2;color:var(--clr-text);">
                        <li>Check this website regularly for current alerts</li>
                        <li>Listen to local community radio broadcasts</li>
                        <li>Contact your chief for the latest information</li>
                        <li>Dial <strong>*384#</strong> on any phone to check via USSD</li>
                        <li>Join WhatsApp alert groups through your ward rep</li>
                    </ul>
                </div>
            </div>

            <div class="card" style="border-top:4px solid var(--clr-accent);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-warning-light);color:var(--clr-accent);"></div>
                    <div><h3 class="card-title">For Stakeholders</h3></div>
                </div>
                <div class="card-body">
                    <ul style="list-style:disc;padding-left:var(--sp-lg);font-size:var(--fs-sm);line-height:2.2;color:var(--clr-text);">
                        <li>Monitor the <a href="current-alert.php">Live Predictions</a> page daily</li>
                        <li>Use message templates to disseminate alerts</li>
                        <li>Coordinate response plans with other stakeholders</li>
                        <li>Follow phase-specific response protocols</li>
                        <li>Report ground-level observations to NDMA</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── 9. External Resources ─────────────────────────── -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>External Resources</h2>
            <p>Authoritative sources for deeper research and official bulletins.</p>
        </div>
        <div class="grid grid-3 grid-auto">
            <div class="card">
                <h3 class="card-title">NDMA Bulletins</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Monthly drought early warning bulletins for all ASAL counties including Samburu.</p>
                <a href="https://www.ndma.go.ke" target="_blank" rel="noopener" class="btn btn-outline btn-sm mt-md">Visit NDMA →</a>
            </div>
            <div class="card">
                <h3 class="card-title">KMD Forecasts</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Seasonal weather outlooks and rainfall probability maps from Kenya Meteorological Department.</p>
                <a href="<?= htmlspecialchars($kmd['source_url'] ?? '#') ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm mt-md">Visit KMD →</a>
            </div>
            <div class="card">
                <h3 class="card-title">FEWS NET</h3>
                <p class="text-muted" style="font-size:var(--fs-sm);">Famine Early Warning Systems Network — food security analysis and livelihood zone maps.</p>
                <a href="https://fews.net" target="_blank" rel="noopener" class="btn btn-outline btn-sm mt-md">Visit FEWS NET →</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
