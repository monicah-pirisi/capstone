<?php
/**
 * Samburu EWS — Channels
 *
 * Shows dissemination channels: social media templates,
 * radio scripts, and USSD simulator link.
 * Powered by channels_content.json.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'Channels';
$content   = DataRepository::load('channels_content.json');

$whatsapp  = $content['social_media']['whatsapp'] ?? [];
$facebook  = $content['social_media']['facebook'] ?? [];
$radio     = $content['radio'] ?? [];
$ussd      = $content['ussd'] ?? [];

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Dissemination Channels</h1>
        <p>How early warnings reach every stakeholder — from social media dashboards to vernacular radio and basic-phone USSD menus.</p>
    </div>
</section>

<!-- Channel overview cards -->
<section class="page-section">
    <div class="container">
        <div class="grid grid-4 grid-auto">
            <div class="card stat-card"><div class="stat-value">WA</div><div class="stat-label">WhatsApp</div></div>
            <div class="card stat-card"><div class="stat-value">FB/X</div><div class="stat-label">Facebook / X</div></div>
            <div class="card stat-card"><div class="stat-value">FM</div><div class="stat-label">Community Radio</div></div>
            <div class="card stat-card"><div class="stat-value">USSD</div><div class="stat-label">*384#</div></div>
        </div>
    </div>
</section>

<!-- WhatsApp -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>WhatsApp Templates</h2>
            <p>Audience: <?= htmlspecialchars($whatsapp['audience'] ?? '') ?>. Pre-formatted messages for each alert level.</p>
        </div>
        <div class="grid grid-auto">
            <?php
            foreach (($whatsapp['templates'] ?? []) as $level => $tpl): ?>
            <div class="card" style="border-left:4px solid var(--clr-primary);">
                <h3 class="card-title"><?= htmlspecialchars($level) ?></h3>
                <pre class="channel-pre mt-sm"><?= htmlspecialchars($tpl['body'] ?? '') ?></pre>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Facebook -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Facebook / X (Twitter) Posts</h2>
            <p>Audience: <?= htmlspecialchars($facebook['audience'] ?? '') ?></p>
        </div>
        <div class="grid grid-3 grid-auto">
            <?php foreach (($facebook['templates'] ?? []) as $level => $tpl): ?>
            <div class="card" style="border-left:4px solid var(--clr-info);">
                <h3 class="card-title"><?= htmlspecialchars($level) ?></h3>
                <pre class="channel-pre mt-sm"><?= htmlspecialchars($tpl['post'] ?? '') ?></pre>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Radio -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>Community Radio Scripts</h2>
            <p><?= htmlspecialchars($radio['language_note'] ?? '') ?></p>
        </div>

        <h3 style="color:var(--clr-primary);margin-bottom:var(--sp-md);">30-Second Spots</h3>
        <div class="grid grid-auto mb-xl">
            <?php foreach (($radio['scripts']['30s'] ?? []) as $level => $s): ?>
            <div class="card" style="border-left:4px solid var(--clr-accent);">
                <div class="card-header">
                    <span class="badge badge-amber"><?= htmlspecialchars($s['duration'] ?? '30s') ?></span>
                    <h3 class="card-title"><?= htmlspecialchars($level) ?></h3>
                </div>
                <pre class="channel-pre mt-sm"><?= htmlspecialchars($s['script'] ?? '') ?></pre>
            </div>
            <?php endforeach; ?>
        </div>

        <h3 style="color:var(--clr-primary);margin-bottom:var(--sp-md);">60-Second Spots</h3>
        <div class="grid grid-auto">
            <?php foreach (($radio['scripts']['60s'] ?? []) as $level => $s): ?>
            <div class="card" style="border-left:4px solid var(--clr-warning);">
                <div class="card-header">
                    <span class="badge badge-amber"><?= htmlspecialchars($s['duration'] ?? '60s') ?></span>
                    <h3 class="card-title"><?= htmlspecialchars($level) ?></h3>
                </div>
                <pre class="channel-pre mt-sm"><?= htmlspecialchars($s['script'] ?? '') ?></pre>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- USSD -->
<section class="page-section">
    <div class="container">
        <div class="section-header">
            <h2>USSD Menu (<?= htmlspecialchars($ussd['code'] ?? '*384#') ?>)</h2>
            <p><?= htmlspecialchars($ussd['description'] ?? '') ?></p>
        </div>

        <div class="grid grid-2 grid-auto" style="align-items:start;">
            <div class="card">
                <h3 class="card-title mb-md">Menu Structure</h3>
                <?php foreach (($ussd['menu'] ?? []) as $num => $item): ?>
                <div style="padding:var(--sp-sm) 0;border-bottom:1px solid var(--clr-border-light);">
                    <strong><?= htmlspecialchars($num) ?>. <?= htmlspecialchars($item['label'] ?? '') ?></strong>
                    <pre class="channel-pre mt-xs" style="font-size:var(--fs-xs);"><?= htmlspecialchars($item['response'] ?? '') ?></pre>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="ussd-simulator.php" class="btn btn-accent btn-lg">Try the Live Simulator →</a>
                <p class="text-muted mt-md" style="font-size:var(--fs-sm);">
                    Experience the full USSD menu with bilingual support, live risk data, and emergency contacts.
                </p>
            </div>
        </div>
    </div>
</section>

<style>
.channel-pre {
    white-space: pre-wrap;
    word-break: break-word;
    font-family: 'Courier New', monospace;
    font-size: var(--fs-sm);
    line-height: 1.7;
    color: var(--clr-text-muted);
    background: var(--clr-bg);
    padding: var(--sp-md);
    border-radius: var(--radius-md);
    max-height: 300px;
    overflow-y: auto;
}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
