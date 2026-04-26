<?php
/**
 * Samburu EWS: Home Page
 */
require __DIR__ . '/config.php';

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1><?= SITE_NAME ?></h1>
        <p>
            A drought early warning and action recommendation platform combining
            scientific forecasts with indigenous knowledge to protect pastoralist
            communities from climate hazards across Samburu County, Kenya.
        </p>
        <div class="hero-ctas">
            <a href="findings.php"      class="btn btn-accent btn-lg"> View Findings</a>
            <a href="current-alert.php" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">Current Alert</a>
            <a href="resources.php"     class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.5);color:rgba(255,255,255,.85);">Resources</a>
        </div>
    </div>
</section>

<!-- Quick Stats -->
<section class="page-section">
    <div class="container">
        <div class="grid grid-4 grid-auto">
            <div class="card stat-card">
                <div class="stat-value">5</div>
                <div class="stat-label">Stakeholder Groups</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value">4</div>
                <div class="stat-label">Dissemination Channels</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value">24/7</div>
                <div class="stat-label">USSD Availability</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value">Live</div>
                <div class="stat-label">Risk Engine</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>A four-stage pipeline that turns raw data into actionable early warnings for every stakeholder level.</p>
        </div>

        <div class="grid grid-4 grid-auto">
            <!-- Stage 1 -->
            <div class="card hiw-card">
                <div class="card-header">
                    <div class="card-icon hiw-step">1</div>
                    <h3 class="card-title">Scientific Forecasts</h3>
                </div>
                <div class="card-body">
                    <p>
                        Satellite NDVI, rainfall anomalies, and meteorological
                        models provide objective drought indicators updated
                        monthly.
                    </p>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="card hiw-card">
                <div class="card-header">
                    <div class="card-icon hiw-step" style="background:var(--clr-warning-light);color:var(--clr-warning);">2</div>
                    <h3 class="card-title">Indigenous Indicators</h3>
                </div>
                <div class="card-body">
                    <p>
                        Community scouts report on livestock body condition,
                        pasture quality, wild-fruit availability, and migration
                        patterns.
                    </p>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="card hiw-card">
                <div class="card-header">
                    <div class="card-icon hiw-step" style="background:var(--clr-danger-light);color:var(--clr-danger);">3</div>
                    <h3 class="card-title">Risk Engine</h3>
                </div>
                <div class="card-body">
                    <p>
                        Weighted algorithms merge both data streams, computing
                        a composite risk score that maps to Normal, Alert,
                        Alarm, or Emergency levels.
                    </p>
                </div>
            </div>

            <!-- Stage 4 -->
            <div class="card hiw-card">
                <div class="card-header">
                    <div class="card-icon hiw-step" style="background:var(--clr-info-light);color:var(--clr-info);">4</div>
                    <h3 class="card-title">Dissemination Channels</h3>
                </div>
                <div class="card-body">
                    <p>
                        Tailored alerts reach stakeholders via social-media
                        dashboards, vernacular radio scripts, SMS blasts, and
                        USSD menus.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stakeholders -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Key Stakeholders</h2>
            <p>The early-warning ecosystem depends on coordinated action across five stakeholder groups.</p>
        </div>

        <div class="grid grid-3 grid-auto">
            <!-- Government -->
            <div class="card stakeholder-card">
                <div class="card-header">
                    <div class="card-icon"></div>
                    <div>
                        <h3 class="card-title">Government Agencies</h3>
                        <span class="badge badge-green">Policy & Coordination</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        The National Drought Management Authority (NDMA) and
                        Samburu County Government coordinate response plans,
                        allocate relief resources, and set policy based on EWS
                        risk levels.
                    </p>
                </div>
            </div>

            <!-- NGOs -->
            <div class="card stakeholder-card">
                <div class="card-header">
                    <div class="card-icon"></div>
                    <div>
                        <h3 class="card-title">NGOs &amp; Development Partners</h3>
                        <span class="badge badge-blue">Funding & Technical Support</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Organizations like the Kenya Red Cross, WFP, and
                        FAO provide technical capacity, funding, and
                        humanitarian logistics when alert thresholds are
                        exceeded.
                    </p>
                </div>
            </div>

            <!-- Radio Stations -->
            <div class="card stakeholder-card">
                <div class="card-header">
                    <div class="card-icon"></div>
                    <div>
                        <h3 class="card-title">Community Radio Stations</h3>
                        <span class="badge badge-amber">Information Broadcast</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Local FM stations broadcast vernacular-language
                        early-warning bulletins, reaching remote pastoralist
                        households without internet access.
                    </p>
                </div>
            </div>

            <!-- Pastoralists -->
            <div class="card stakeholder-card">
                <div class="card-header">
                    <div class="card-icon"></div>
                    <div>
                        <h3 class="card-title">Pastoralist Communities</h3>
                        <span class="badge badge-amber">Direct Beneficiaries</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Semi-nomadic herders in Samburu County are the primary
                        audience. The system helps them make timely migration
                        and destocking decisions using both modern and
                        traditional cues.
                    </p>
                </div>
            </div>

            <!-- Intermediaries -->
            <div class="card stakeholder-card">
                <div class="card-header">
                    <div class="card-icon"></div>
                    <div>
                        <h3 class="card-title">Intermediaries &amp; Chiefs</h3>
                        <span class="badge badge-neutral">Community Liaison</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Village chiefs, ward administrators, and community
                        health volunteers bridge the gap between formal
                        agencies and grassroots pastoralist groups.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-lg">
            <a href="stakeholders.php" class="btn btn-primary">View All Stakeholders →</a>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="page-section cta-section">
    <div class="container text-center">
        <h2 style="color:var(--clr-primary);margin-bottom:var(--sp-sm);">Stay Informed, Stay Prepared</h2>
        <p class="text-muted mb-lg" style="max-width:560px;margin-inline:auto;">
            Check the latest drought risk assessment or explore historical
            findings to understand trends in Samburu County.
        </p>
        <div class="hero-ctas">
            <a href="current-alert.php" class="btn btn-accent btn-lg"> Check Current Alert</a>
            <a href="findings.php" class="btn btn-outline btn-lg"> Explore Findings</a>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/includes/footer.php';
?>
