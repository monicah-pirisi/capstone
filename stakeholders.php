<?php
/**
 * Samburu EWS: Stakeholders
 *
 * Tabbed interface for all five stakeholder groups.
 * Each tab shows: What you receive · What you do ·
 * How you share · Feedback loop · Phase actions.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'Stakeholders';
$data      = DataRepository::load('stakeholders.json');
$groups    = $data['groups'] ?? [];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Stakeholders</h1>
        <p>The early-warning ecosystem depends on coordinated action across five stakeholder groups, each with distinct roles, channels, and response protocols.</p>
    </div>
</section>

<!-- Tab buttons -->
<section class="page-section" style="padding-bottom:0;">
    <div class="container">
        <div class="grid grid-auto" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
            <?php foreach ($groups as $i => $g): ?>
            <button
                class="stakeholder-tab-btn <?= $i === 0 ? 'active' : '' ?>"
                data-tab="tab-<?= htmlspecialchars($g['id']) ?>"
                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                role="tab"
            >
                <span><?= htmlspecialchars($g['name']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Tab panels -->
<section class="page-section" style="padding-top:var(--sp-lg);">
    <div class="container">
        <?php foreach ($groups as $i => $g): ?>
        <div
            class="stakeholder-panel <?= $i === 0 ? 'active' : '' ?>"
            id="tab-<?= htmlspecialchars($g['id']) ?>"
            role="tabpanel"
        >
            <!-- Group header -->
            <div class="card mb-lg" style="border-left:4px solid var(--clr-primary);background:var(--clr-primary-pale);">
                <div class="card-header">
                    <div class="card-icon" style="background:var(--clr-primary-pale);color:var(--clr-primary);"></div>
                    <div>
                        <h2 class="card-title" style="color:var(--clr-primary);"><?= htmlspecialchars($g['name']) ?></h2>
                        <span class="badge badge-green"><?= htmlspecialchars($g['badge']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p style="line-height:1.8;"><?= htmlspecialchars($g['role']) ?></p>
                    <div style="margin-top:var(--sp-md);">
                        <strong style="font-size:var(--fs-sm);color:var(--clr-text-muted);">Members / Entities:</strong>
                        <div style="display:flex;flex-wrap:wrap;gap:var(--sp-xs);margin-top:var(--sp-xs);">
                            <?php foreach ($g['members'] as $m): ?>
                            <span class="badge badge-neutral"><?= htmlspecialchars($m) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div style="margin-top:var(--sp-md);">
                        <strong style="font-size:var(--fs-sm);color:var(--clr-text-muted);">Preferred Channels:</strong>
                        <div style="display:flex;flex-wrap:wrap;gap:var(--sp-xs);margin-top:var(--sp-xs);">
                            <?php foreach ($g['preferred_channels'] as $ch): ?>
                            <span class="badge badge-blue"><?= htmlspecialchars($ch) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4-column info cards -->
            <div class="grid grid-2 grid-auto mb-lg">

                <div class="card" style="border-left:4px solid var(--clr-info);">
                    <h3 class="card-title" style="color:var(--clr-info);">What You Receive</h3>
                    <p class="text-muted" style="font-size:var(--fs-sm);line-height:1.8;margin-top:var(--sp-sm);"><?= htmlspecialchars($g['what_you_receive']) ?></p>
                </div>

                <div class="card" style="border-left:4px solid var(--clr-primary);">
                    <h3 class="card-title" style="color:var(--clr-primary);">What You Do</h3>
                    <p class="text-muted" style="font-size:var(--fs-sm);line-height:1.8;margin-top:var(--sp-sm);"><?= htmlspecialchars($g['what_you_do']) ?></p>
                </div>

                <div class="card" style="border-left:4px solid var(--clr-accent);">
                    <h3 class="card-title" style="color:var(--clr-accent);">How You Share</h3>
                    <p class="text-muted" style="font-size:var(--fs-sm);line-height:1.8;margin-top:var(--sp-sm);"><?= htmlspecialchars($g['how_you_share']) ?></p>
                </div>

                <div class="card" style="border-left:4px solid var(--clr-success);">
                    <h3 class="card-title" style="color:var(--clr-success);">Feedback Loop</h3>
                    <p class="text-muted" style="font-size:var(--fs-sm);line-height:1.8;margin-top:var(--sp-sm);"><?= htmlspecialchars($g['feedback_loop']) ?></p>
                </div>

            </div>

            <!-- Phase actions table -->
            <div class="card">
                <h3 class="card-title mb-md">Response Actions by Alert Phase</h3>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Phase</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($g['alert_actions'] as $phase => $action): ?>
                            <tr>
                                <td style="white-space:nowrap;"><?= htmlspecialchars($phase) ?></td>
                                <td><?= htmlspecialchars($action) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
.stakeholder-tab-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-xs);
    padding: var(--sp-md);
    background: var(--clr-surface);
    border: 2px solid var(--clr-border-light);
    border-radius: var(--radius-lg);
    cursor: pointer;
    font-family: var(--ff-base);
    font-size: var(--fs-xs);
    font-weight: var(--fw-medium);
    color: var(--clr-text-muted);
    text-align: center;
    transition: all var(--tr-fast);
    line-height: 1.4;
}
.stakeholder-tab-btn:hover {
    border-color: var(--clr-primary-light);
    color: var(--clr-primary);
    background: var(--clr-primary-pale);
}
.stakeholder-tab-btn.active {
    background: var(--clr-primary);
    border-color: var(--clr-primary);
    color: #fff;
}
.stakeholder-panel { display: none; }
.stakeholder-panel.active { display: block; animation: fadeIn var(--tr-base); }
</style>

<script>
(function () {
    'use strict';
    var btns   = document.querySelectorAll('.stakeholder-tab-btn');
    var panels = document.querySelectorAll('.stakeholder-panel');
    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            btns.forEach(function (b) { b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
            panels.forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
            var panel = document.getElementById(btn.dataset.tab);
            if (panel) panel.classList.add('active');
        });
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
