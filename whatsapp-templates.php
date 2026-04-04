<?php
/**
 * Samburu EWS — WhatsApp Templates
 *
 * Displays pre-formatted WhatsApp messages for different
 * drought alert levels and stakeholder groups.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/DataRepository.php';

$pageTitle = 'WhatsApp Templates';

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>WhatsApp Templates</h1>
        <p>Pre-formatted WhatsApp messages for disseminating drought alerts to stakeholder groups.</p>
    </div>
</section>

<!-- WhatsApp Templates -->
<section class="page-section">
    <div class="container">
        <!-- Normal -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-success);">
            <div class="card-header">
                <span class="badge badge-green" style="font-size:var(--fs-md);">NORMAL</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Normal Conditions</h3>
            </div>
            <div class="card-body">
                <p>Use for: No drought conditions, normal pastoral activities</p>
                <div class="code-block mt-md">
                    <pre>⚠️ Samburu EWS - NORMAL

Samburu County is currently under NORMAL drought conditions.

✅ Pasture availability: Good
✅ Water sources: Adequate
✅ Livestock condition: Healthy

Continue normal activities. Next update: [DATE]

Powered by Samburu EWS</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('template-normal')">Copy Template</button>
            </div>
        </div>

        <!-- Watch -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-info);">
            <div class="card-header">
                <span class="badge badge-blue" style="font-size:var(--fs-md);">WATCH</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Watch Phase</h3>
            </div>
            <div class="card-body">
                <p>Use for: Early signs of drought, monitor conditions</p>
                <div class="code-block mt-md">
                    <pre>⚠️ Samburu EWS - WATCH

Samburu County is now under WATCH phase.

📊 Indicators to monitor:
• Rainfall: Below average
• Pasture: Starting to decrease
• Water: Still adequate

Actions: Monitor livestock, prepare for possible movement

Next update: [DATE]
Powered by Samburu EWS</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('template-watch')">Copy Template</button>
            </div>
        </div>

        <!-- Alert -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-warning);">
            <div class="card-header">
                <span class="badge badge-amber" style="font-size:var(--fs-md);">ALERT</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Alert Phase</h3>
            </div>
            <div class="card-body">
                <p>Use for: Drought alert issued, prepare response</p>
                <div class="code-block mt-md">
                    <pre>⚠️🚨 Samburu EWS - ALERT

⚠️ DROUGHT ALERT issued for Samburu County!

📉 Current conditions:
• Rainfall: Significantly below normal
• Pasture: Limited
• Water sources: Declining

🔴 ACTIONS REQUIRED:
• Prepare livestock for possible movement
• Contact local chief for water points
• Reduce herd size if possible

Emergency contacts:
- NDMA: [PHONE]
- County Gov: [PHONE]

Next update: [DATE]</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('template-alert')">Copy Template</button>
            </div>
        </div>

        <!-- Alarm -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-danger);">
            <div class="card-header">
                <span class="badge badge-red" style="font-size:var(--fs-md);">ALARM</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Alarm Phase</h3>
            </div>
            <div class="card-body">
                <p>Use for: Severe drought, immediate action required</p>
                <div class="code-block mt-md">
                    <pre>🚨🔴 Samburu EWS - ALARM

🚨 SEVERE DROUGHT ALARM for Samburu County!

❌ Current conditions:
• Pasture: Very limited
• Water: Scarce
• Livestock: Under stress

🆘 IMMEDIATE ACTIONS:
• Move livestock to designated water points
• Contact chiefs for evacuation assistance
• Seek humanitarian assistance

Emergency contacts:
- NDMA: [PHONE]
- Red Cross: [PHONE]
- County Gov: [PHONE]

DO NOT DELAY - Act now!</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('template-alarm')">Copy Template</button>
            </div>
        </div>

        <!-- Emergency -->
        <div class="card mb-lg" style="border-left:4px solid #333;">
            <div class="card-header">
                <span class="badge" style="font-size:var(--fs-md);background:#333;color:#fff;">EMERGENCY</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Emergency Phase</h3>
            </div>
            <div class="card-body">
                <p>Use for: Critical emergency, humanitarian response needed</p>
                <div class="code-block mt-md">
                    <pre>⛔🆘 Samburu EWS - EMERGENCY

⛔ DROUGHT EMERGENCY - IMMEDIATE HELP REQUIRED

📍 Location: [WARD/VILLAGE]

😰 Current situation:
• No pasture available
• Water sources exhausted
• Livestock dying
• Food security critical

🆘 RESPONSE REQUIRED:
• Emergency evacuation of livestock
• Food aid distribution
• Water trucking

Contact emergency services NOW!

Emergency: 999 | NDMA: [PHONE]</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('template-emergency')">Copy Template</button>
            </div>
        </div>

        <!-- Group-specific templates -->
        <div class="card mt-lg">
            <div class="card-header">
                <h3 class="card-title">Stakeholder-Specific Templates</h3>
            </div>
            <div class="card-body">
                <p>Tailored messages for different stakeholder groups:</p>
                <div class="table-wrap mt-md">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Stakeholder</th>
                                <th>Template</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Government Agencies</td>
                                <td class="text-muted" style="font-size:var(--fs-xs);">Stakeholder-specific templates to be added — use the phase templates above and address to relevant group.</td>
                            </tr>
                            <tr>
                                <td>NGOs</td>
                                <td class="text-muted" style="font-size:var(--fs-xs);">Stakeholder-specific templates to be added — use the phase templates above and address to relevant group.</td>
                            </tr>
                            <tr>
                                <td>Radio Stations</td>
                                <td class="text-muted" style="font-size:var(--fs-xs);">See Radio Scripts page for broadcast-ready content.</td>
                            </tr>
                            <tr>
                                <td>Community Chiefs</td>
                                <td class="text-muted" style="font-size:var(--fs-xs);">Stakeholder-specific templates to be added — use the phase templates above and address to relevant group.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-lg">
            <a href="channels.php" class="btn btn-primary">← Back to Channels</a>
        </div>
    </div>
</section>

<style>
.code-block {
    background: var(--clr-bg-alt);
    border: 1px solid var(--clr-border);
    border-radius: var(--radius-md);
    padding: var(--sp-md);
}
.code-block pre {
    margin: 0;
    font-family: monospace;
    white-space: pre-wrap;
    font-size: var(--fs-sm);
}
</style>

<script>
function copyToClipboard(id) {
    const el = document.getElementById(id);
    const text = el ? el.innerText : '';
    if (!text) return;
    navigator.clipboard.writeText(text).then(function () {
        const btn = event.target;
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        btn.disabled = true;
        setTimeout(function () { btn.textContent = original; btn.disabled = false; }, 1800);
    }).catch(function () {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>

