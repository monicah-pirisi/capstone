<?php
/**
 * Samburu EWS: Problem Statement
 *
 * Outlines the drought challenge, communication barriers,
 * and why a better EWS is needed. Loads barriers.json.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'The Problem';
$barriers  = DataRepository::load('barriers.json');

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>The Problem</h1>
        <p>Why early-warning systems in Samburu County are failing the communities they are designed to protect.</p>
    </div>
</section>

<!-- Context -->
<section class="page-section">
    <div class="container">
        <div class="grid grid-2 grid-auto" style="align-items:start;">
            <div>
                <h2 style="color:var(--clr-primary);margin-bottom:var(--sp-md);">Drought in Samburu County</h2>
                <p class="text-muted" style="line-height:1.8;">
                    Pastoralist communities in Samburu County face critical climate hazards, with livestock
                    production contributing between <strong>60 to 85%</strong> of household cash income across
                    the pastoral and agro-pastoral livelihood zones. Recurrent droughts, such as the
                    2021-2022 event which resulted in over <strong>2.4 million livestock deaths</strong> nationwide,
                    with Samburu among the most affected counties, continue to threaten these livelihoods.
                </p>
                <p class="text-muted mt-md" style="line-height:1.8;">
                    Despite the critical role of Early Warning Systems (EWS) in climate adaptation, their
                    effectiveness remains limited. Top-down EWS designs often neglect local ecological knowledge
                    and socio-cultural dynamics, while infrastructural limitations hinder the timely issuance
                    of warnings, <strong>worsening community vulnerability</strong> rather than reducing it.
                </p>
            </div>
            <div class="card" style="background:var(--clr-danger-light);border-color:var(--clr-danger);">
                <h3 class="card-title" style="color:var(--clr-danger);">Key Evidence</h3>
                <ul style="list-style:none;margin-top:var(--sp-md);font-size:var(--fs-sm);line-height:2;">
                    <li>Livestock contributes <strong>60 to 85%</strong> of household cash income across pastoral and agro-pastoral livelihood zones <em>[1]</em></li>
                    <li>The <strong>2021-2022 drought</strong> caused over <strong>2.4 million livestock deaths</strong> nationwide, with Samburu among the most affected counties <em>[2, 3]</em></li>
                    <li>Systemic gaps identified: disconnect between formal EWS and indigenous knowledge <em>[4]</em></li>
                    <li>Mistrust in external information sources documented in pastoral communities <em>[5]</em></li>
                    <li>Institutional fragmentation undermines EWS coordination <em>[6]</em></li>
                </ul>
                <p style="font-size:var(--fs-xs);color:var(--clr-danger);margin-top:var(--sp-sm);font-style:italic;">Sources: Abstract &amp; literature review, Chapter 2</p>
            </div>
        </div>
    </div>
</section>

<!-- Communication Barriers -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Communication Barriers</h2>
            <p>Identified through 12 qualitative in-depth interviews with pastoralists, government officials, NGO workers, and community leaders in Samburu County (2026).</p>
        </div>
        <div class="grid grid-2 grid-auto">
            <?php foreach ($barriers as $b): ?>
            <div class="card" style="border-left:4px solid var(--clr-danger);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-danger-light);color:var(--clr-danger);font-weight:var(--fw-black);font-size:var(--fs-xl);">#<?= $b['rank'] ?></div>
                    <div>
                        <h3 class="card-title"><?= htmlspecialchars($b['barrier']) ?></h3>
                        <span class="badge badge-red">Barrier #<?= $b['rank'] ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p style="line-height:1.8;"><?= htmlspecialchars($b['description']) ?></p>

                    <div style="margin-top:var(--sp-md);padding:var(--sp-sm) var(--sp-md);background:var(--clr-bg);border-radius:var(--radius-md);font-size:var(--fs-sm);">
                        <strong style="color:var(--clr-text-muted);text-transform:uppercase;font-size:var(--fs-xs);letter-spacing:.05em;">Example Evidence</strong>
                        <p style="margin-top:var(--sp-xs);color:var(--clr-text-muted);font-style:italic;line-height:1.7;"><?= htmlspecialchars($b['evidence']) ?></p>
                    </div>

                    <div style="margin-top:var(--sp-md);padding:var(--sp-sm) var(--sp-md);background:var(--clr-primary-pale);border-radius:var(--radius-md);font-size:var(--fs-sm);border-left:3px solid var(--clr-primary-light);">
                        <strong style="color:var(--clr-primary);text-transform:uppercase;font-size:var(--fs-xs);letter-spacing:.05em;">Design Implication</strong>
                        <p style="margin-top:var(--sp-xs);color:var(--clr-primary);line-height:1.7;"><?= htmlspecialchars($b['design_implication']) ?></p>
                    </div>

                    <p class="mt-sm text-muted" style="font-size:var(--fs-xs);">
                        <strong>Affects:</strong> <?= implode(', ', array_map('htmlspecialchars', $b['affected_groups'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Gap -->
<section class="page-section">
    <div class="container text-center">
        <h2 style="color:var(--clr-primary);margin-bottom:var(--sp-md);">The Gap This Platform Addresses</h2>
        <p class="text-muted" style="max-width:700px;margin-inline:auto;line-height:1.8;">
            Existing systems produce technically accurate data, but it never reaches the people who need it most.
            <?= SITE_NAME ?> bridges this gap by translating scientific and indigenous indicators
            into actionable, multilingual, multi-channel warnings tailored to each stakeholder group.
        </p>
        <a href="solution.php" class="btn btn-primary btn-lg mt-lg">View Our Solution →</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
