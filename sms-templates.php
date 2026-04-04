<?php
/**
 * Samburu EWS — SMS Templates
 *
 * Pre-formatted SMS messages for different drought alert levels.
 * Optimized for 160 character limit.
 */
require __DIR__ . '/config.php';

$pageTitle = 'SMS Templates';

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>SMS Templates</h1>
        <p>Short message service templates for broadcasting drought alerts via SMS.</p>
    </div>
</section>

<!-- SMS Templates -->
<section class="page-section">
    <div class="container">
        <!-- Character Limit Info -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">SMS Character Limits</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-3 grid-auto">
                    <div class="text-center">
                        <div class="stat-value">160</div>
                        <div class="stat-label">Single SMS</div>
                    </div>
                    <div class="text-center">
                        <div class="stat-value">306</div>
                        <div class="stat-label">2 SMS (Concatenated)</div>
                    </div>
                    <div class="text-center">
                        <div class="stat-value">459</div>
                        <div class="stat-label">3 SMS (Long message)</div>
                    </div>
                </div>
                <p class="mt-md text-muted">Keep messages under 160 characters for single-SMS delivery.</p>
            </div>
        </div>

        <!-- Normal -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-success);">
            <div class="card-header">
                <span class="badge badge-green">NORMAL</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Normal Conditions</h3>
            </div>
            <div class="card-body">
                <p id="sms-normal">Status: Normal drought conditions. Pasture and water adequate. Continue normal activities. Next update: [DATE]. - Samburu EWS</p>
                <div class="mt-sm">
                    <span class="badge">148 characters</span>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('sms-normal')">Copy SMS</button>
            </div>
        </div>

        <!-- Watch -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-info);">
            <div class="card-header">
                <span class="badge badge-blue">WATCH</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Watch Phase</h3>
            </div>
            <div class="card-body">
                <p id="sms-watch">WATCH: Early drought signs detected in Samburu. Rainfall below normal. Monitor livestock. Prepare for possible movement. - Samburu EWS</p>
                <div class="mt-sm">
                    <span class="badge">145 characters</span>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('sms-watch')">Copy SMS</button>
            </div>
        </div>

        <!-- Alert -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-warning);">
            <div class="card-header">
                <span class="badge badge-amber">ALERT</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Alert Phase</h3>
            </div>
            <div class="card-body">
                <p id="sms-alert">ALERT: Drought Alert issued for Samburu. Pasture limited. Water declining. Prepare to move livestock. Contact chief for water points. NDMA: [PHONE] - Samburu EWS</p>
                <div class="mt-sm">
                    <span class="badge">160 characters</span>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('sms-alert')">Copy SMS</button>
            </div>
        </div>

        <!-- Alarm -->
        <div class="card mb-lg" style="border-left:4px solid var(--clr-danger);">
            <div class="card-header">
                <span class="badge badge-red">ALARM</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Alarm Phase</h3>
            </div>
            <div class="card-body">
                <p id="sms-alarm">ALARM: Severe drought. Move livestock NOW to water points. Contact chief or NDMA [PHONE] for emergency assistance. - Samburu EWS</p>
                <div class="mt-sm">
                    <span class="badge">140 characters</span>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('sms-alarm')">Copy SMS</button>
            </div>
        </div>

        <!-- Emergency -->
        <div class="card mb-lg" style="border-left:4px solid #333;">
            <div class="card-header">
                <span class="badge" style="background:#333;color:#fff;">EMERGENCY</span>
                <h3 class="card-title" style="display:inline;margin-left:var(--sp-sm);">Emergency Phase</h3>
            </div>
            <div class="card-body">
                <p id="sms-emergency">EMERGENCY: Critical drought. Emergency response activated. Seek humanitarian aid immediately. Emergency: 999. NDMA: [PHONE] - Samburu EWS</p>
                <div class="mt-sm">
                    <span class="badge">148 characters</span>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('sms-emergency')">Copy SMS</button>
            </div>
        </div>

        <!-- Multi-part SMS for detailed info -->
        <div class="card mt-lg">
            <div class="card-header">
                <h3 class="card-title">Detailed Multi-Part SMS</h3>
            </div>
            <div class="card-body">
                <p>For 2-3 SMS concatenations when more detail is needed:</p>
                
                <div class="mt-md">
                    <h4>Part 1 (Header)</h4>
                    <div class="code-block">
                        <pre>🚨 ALERT: Samburu County Drought Alert issued. Current conditions severe. See parts 2&3 for actions.</pre>
                    </div>
                </div>

                <div class="mt-md">
                    <h4>Part 2 (Actions)</h4>
                    <div class="code-block">
                        <pre>ACTIONS: 1) Move livestock to designated water points 2) Contact chief for locations 3) Consider destocking</pre>
                    </div>
                </div>

                <div class="mt-md">
                    <h4>Part 3 (Contacts)</h4>
                    <div class="code-block">
                        <pre>CONTACTS: NDMA [PHONE] | County Gov [PHONE] | Red Cross [PHONE] | Emergency 999</pre>
                    </div>
                </div>

                <button class="btn btn-outline mt-md" onclick="copyToClipboard('sms-multi')">Copy All Parts</button>
            </div>
        </div>

        <!-- Bulk SMS Services -->
        <div class="card mt-lg">
            <div class="card-header">
                <h3 class="card-title">Bulk SMS Services</h3>
            </div>
            <div class="card-body">
                <p>Recommended services for broadcasting to large groups:</p>
                <div class="table-wrap mt-md">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Kenya Coverage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Africa's Talking</td>
                                <td>Bulk SMS and USSD API with Kenyan network prefixes</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Safaricom Business SMS</td>
                                <td>Direct operator bulk SMS for Safaricom subscribers</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td>Twilio</td>
                                <td>International SMS API with local number support</td>
                                <td>Yes</td>
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

