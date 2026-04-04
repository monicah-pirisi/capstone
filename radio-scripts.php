<?php
/**
 * Samburu EWS — Radio Scripts
 *
 * Pre-written radio broadcast scripts for different
 * drought alert levels in English and Samburu.
 */
require __DIR__ . '/config.php';

$pageTitle = 'Radio Scripts';

require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>Radio Scripts</h1>
        <p>Pre-written radio broadcast scripts for disseminating drought alerts via community FM stations.</p>
    </div>
</section>

<!-- Radio Scripts -->
<section class="page-section">
    <div class="container">
        <!-- Script Length Options -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">Script Length Options</h3>
            </div>
            <div class="card-body">
                <p>Choose the appropriate script length based on your broadcast slot:</p>
                <div class="grid grid-3 grid-auto mt-md">
                    <div class="card" style="background:var(--clr-success-light);">
                        <div class="card-body text-center">
                            <h4>30 Seconds</h4>
                            <p class="text-muted">Quick alert update</p>
                            <span class="badge badge-green">~75 words</span>
                        </div>
                    </div>
                    <div class="card" style="background:var(--clr-info-light);">
                        <div class="card-body text-center">
                            <h4>60 Seconds</h4>
                            <p class="text-muted">Standard bulletin</p>
                            <span class="badge badge-blue">~150 words</span>
                        </div>
                    </div>
                    <div class="card" style="background:var(--clr-warning-light);">
                        <div class="card-body text-center">
                            <h4>2 Minutes</h4>
                            <p class="text-muted">Detailed report</p>
                            <span class="badge badge-amber">~300 words</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30-Second Script - Normal -->
        <div class="card mb-lg">
            <div class="card-header">
                <span class="badge badge-green">30 SECONDS - NORMAL</span>
            </div>
            <div class="card-body">
                <div class="code-block">
                    <pre id="radio-30-normal">[MUSIC FADE IN]

ANCHOR: This is your Samburu Early Warning System update.

Samburu County remains under NORMAL drought conditions.
Pasture and water sources are adequate for our livestock.

Continue with your normal activities. The next update will be
broadcast on [DAY/DATE].

This is Samburu EWS, keeping our community informed.

[MUSIC FADE OUT]

[approx. 30 seconds]</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('radio-30-normal')">Copy Script</button>
            </div>
        </div>

        <!-- 60-Second Script - Watch -->
        <div class="card mb-lg">
            <div class="card-header">
                <span class="badge badge-blue">60 SECONDS - WATCH</span>
            </div>
            <div class="card-body">
                <div class="code-block">
                    <pre id="radio-60-watch">[MUSIC FADE IN]

ANCHOR: Attention all Samburu community members. This is an
important update from the Samburu Early Warning System.

Samburu County is now under WATCH phase. This means we are
seeing early signs of drought conditions.

What you need to know:
- Rainfall has been below normal
- Pasture is starting to decrease
- Water sources are still adequate

What you should do:
- Monitor your livestock condition
- Prepare for possible movement to other grazing areas
- Stay informed by listening to this station

The next update will be on [DAY/DATE]. Stay safe, Samburu.

[MUSIC FADE OUT]

[approx. 60 seconds]</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('radio-60-watch')">Copy Script</button>
            </div>
        </div>

        <!-- 60-Second Script - Alert -->
        <div class="card mb-lg">
            <div class="card-header">
                <span class="badge badge-amber">60 SECONDS - ALERT</span>
            </div>
            <div class="card-body">
                <div class="code-block">
                    <pre id="radio-60-alert">[MUSIC FADE IN — URGENT TONE]

ANCHOR: URGENT ALERT from Samburu Early Warning System.

Samburu County has been placed under ALERT phase due to
worsening drought conditions.

Key information:
- Rainfall is significantly below normal
- Pasture is now limited in many areas
- Water sources are declining rapidly

Immediate actions:
- Prepare to move your livestock to designated water points
- Contact your local chief for information on water locations
- Consider reducing your herd through destocking

Emergency contacts:
- NDMA: [PHONE NUMBER]
- County Government: [PHONE NUMBER]

This is a serious situation. Stay tuned for updates.

[MUSIC FADE OUT]

[approx. 60 seconds]</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('radio-60-alert')">Copy Script</button>
            </div>
        </div>

        <!-- 2-Minute Script - Alarm -->
        <div class="card mb-lg">
            <div class="card-header">
                <span class="badge badge-red">2 MINUTES - ALARM</span>
            </div>
            <div class="card-body">
                <div class="code-block">
                    <pre id="radio-2min-alarm">[MUSIC FADE IN — VERY URGENT]

ANCHOR: CRITICAL DROUGHT ALARM — Samburu County.

This is an emergency broadcast from the Samburu Early Warning
System. We are now under ALARM phase — the highest level
before emergency.

Severe drought conditions:
- Pasture is now very limited or exhausted
- Water sources are becoming scarce
- Livestock are under severe stress
- Some animal deaths may be occurring

Immediate actions required:

1. MOVE YOUR LIVESTOCK — Go to designated water points and
   grazing areas identified by your chief.

2. SEEK ASSISTANCE — Contact your ward administrator or
   village chief for emergency support.

3. PROTECT YOUR FAMILY — Ensure you have enough water
   and food for your household.

Emergency contacts:
- NDMA Hotline: [PHONE]
- Samburu County Government: [PHONE]
- Kenya Red Cross: [PHONE]
- Police Emergency: 999

Humanitarian aid is being organised. Do not delay — act now.

[MUSIC FADE OUT — HOLD FOR 5 SECONDS]

[approx. 2 minutes]</pre>
                </div>
                <button class="btn btn-sm btn-outline mt-md" onclick="copyToClipboard('radio-2min-alarm')">Copy Script</button>
            </div>
        </div>

        <!-- Samburu Language Scripts -->
        <div class="card mt-lg">
            <div class="card-header">
                <h3 class="card-title">Samburu Language Scripts</h3>
            </div>
            <div class="card-body">
                <p>Scripts translated into Samburu for local language broadcasts:</p>
                <div class="table-wrap mt-md">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Phase</th>
                                <th>30-Second</th>
                                <th>60-Second</th>
                                <th>2-Minute</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-green">Normal</span></td>
                                <td colspan="3" class="text-muted" style="font-size:var(--fs-xs);">Samburu-language translations to be added — contact the project team.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-blue">Watch</span></td>
                                <td colspan="3" class="text-muted" style="font-size:var(--fs-xs);">Samburu-language translations to be added — contact the project team.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-amber">Alert</span></td>
                                <td colspan="3" class="text-muted" style="font-size:var(--fs-xs);">Samburu-language translations to be added — contact the project team.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-red">Alarm</span></td>
                                <td colspan="3" class="text-muted" style="font-size:var(--fs-xs);">Samburu-language translations to be added — contact the project team.</td>
                            </tr>
                            <tr>
                                <td><span class="badge" style="background:#333;color:#fff;">Emergency</span></td>
                                <td colspan="3" class="text-muted" style="font-size:var(--fs-xs);">Samburu-language translations to be added — contact the project team.</td>
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

