<?php
/**
 * Samburu EWS — USSD Simulator
 *
 * Phone-like UI simulating a *384# USSD menu.
 * Fetches risk data from /api/current-alert-data.php
 * and presents a navigable text menu.
 */
require __DIR__ . '/config.php';

$pageTitle = 'USSD Simulator';
require __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ────────────────────────────────────── -->
<section class="hero" style="padding:var(--sp-2xl) 0;">
    <div class="container">
        <h1>USSD Simulator</h1>
        <p>Experience the *384# early-warning menu as a Samburu pastoralist would on a basic phone. This simulator fetches live risk data.</p>
    </div>
</section>

<section class="page-section">
    <div class="container">
        <div class="grid grid-2 grid-auto" style="align-items:start;">

            <!-- Phone Mockup  -->
            <div class="ussd-phone-wrapper">
                <div class="ussd-device">
                    <!-- Status bar -->
                    <div class="ussd-statusbar">
                        <span>Samburu EWS</span>
                        <span id="ussdLangIndicator">EN</span>
                    </div>

                    <!-- Screen -->
                    <div class="ussd-screen" id="ussdScreen" aria-live="polite" aria-atomic="true">
                        <span class="ussd-loading">Connecting to *384#…</span>
                    </div>

                    <!-- Input area -->
                    <div class="ussd-input-area">
                        <input
                            type="text"
                            class="ussd-input"
                            id="ussdInput"
                            placeholder="Enter option…"
                            aria-label="USSD input"
                            autocomplete="off"
                            maxlength="20"
                        >
                        <button class="ussd-send-btn" id="ussdSend" aria-label="Send">Send</button>
                    </div>

                    <!-- Keypad -->
                    <div class="ussd-keypad" id="ussdKeypad">
                        <button data-key="1">1</button>
                        <button data-key="2">2</button>
                        <button data-key="3">3</button>
                        <button data-key="4">4</button>
                        <button data-key="5">5</button>
                        <button data-key="6">6</button>
                        <button data-key="7">7</button>
                        <button data-key="8">8</button>
                        <button data-key="9">9</button>
                        <button data-key="*">*</button>
                        <button data-key="0">0</button>
                        <button data-key="#">#</button>
                    </div>
                </div>
            </div>

            <!-- ── Instructions panel ──────────── -->
            <div>
                <div class="card mb-lg">
                    <div class="card-header">
                        <div class="card-icon"></div>
                        <h3 class="card-title">How to Use</h3>
                    </div>
                    <div class="card-body">
                        <ol class="ussd-instructions">
                            <li>The simulator auto-dials <strong>*384#</strong> and shows the main menu.</li>
                            <li>Type a number (or tap the keypad) and press <strong>Send</strong>.</li>
                            <li>Enter <strong>0</strong> to go back, or <strong>00</strong> to return to the home menu.</li>
                            <li>Try switching to <strong>Samburu</strong> language via option 4.</li>
                        </ol>
                    </div>
                </div>

                <div class="card mb-lg">
                    <div class="card-header">
                        <div class="card-icon"></div>
                        <h3 class="card-title">Menu Options</h3>
                    </div>
                    <div class="card-body">
                        <div class="ussd-menu-map">
                            <div class="menu-item"><span class="badge badge-green">1</span> Current Alert Level</div>
                            <div class="menu-item"><span class="badge badge-amber">2</span> Advice for Pastoralists</div>
                            <div class="menu-item"><span class="badge badge-blue">3</span> Where to Get Help</div>
                            <div class="menu-item"><span class="badge badge-neutral">4</span> Change Language</div>
                        </div>
                    </div>
                </div>

                <div class="alert-banner alert-amber">
                    <div>
                        <strong>Note:</strong> This is a web-based simulator for demonstration purposes.
                        A real USSD service requires telco provisioning (e.g. Safaricom, Airtel) with a
                        registered USSD shortcode and a gateway integration. The menu logic shown here
                        mirrors what users would experience on a basic feature phone.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ── USSD Phone Device ─────────────────────── */
.ussd-phone-wrapper {
    display: flex;
    justify-content: center;
}

.ussd-device {
    width: 320px;
    background: #111827;
    border-radius: 2rem;
    padding: 1.2rem;
    box-shadow:
        0 20px 60px rgba(0,0,0,.35),
        inset 0 1px 0 rgba(255,255,255,.08);
    border: 3px solid #2d3748;
}

.ussd-statusbar {
    display: flex;
    justify-content: space-between;
    padding: 0.3rem 0.5rem 0.5rem;
    font-size: 0.7rem;
    color: rgba(255,255,255,.5);
    font-family: 'Inter', sans-serif;
}

.ussd-screen {
    background: #0a0a1a;
    border-radius: var(--radius-md);
    padding: 1rem;
    min-height: 220px;
    max-height: 280px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    line-height: 1.7;
    color: #00ff88;
    white-space: pre-wrap;
    word-break: break-word;
    margin-bottom: 0.8rem;
    scrollbar-width: thin;
    scrollbar-color: #00ff88 transparent;
}

.ussd-loading {
    animation: ussd-blink 1s steps(2) infinite;
}
@keyframes ussd-blink {
    50% { opacity: 0.3; }
}

.ussd-input-area {
    display: flex;
    gap: 0.4rem;
    margin-bottom: 0.8rem;
}

.ussd-input {
    flex: 1;
    background: #0a0a1a;
    border: 1px solid #00ff88;
    border-radius: var(--radius-sm);
    padding: 0.5rem 0.7rem;
    color: #00ff88;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}
.ussd-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,255,136,.3);
}

.ussd-send-btn {
    background: #00ff88;
    color: #0a0a1a;
    border: none;
    border-radius: var(--radius-sm);
    padding: 0.5rem 1rem;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background 150ms ease;
}
.ussd-send-btn:hover { background: #00cc6a; }

/* Keypad */
.ussd-keypad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.4rem;
}
.ussd-keypad button {
    background: #1f2937;
    color: #e5e7eb;
    border: 1px solid #374151;
    border-radius: var(--radius-md);
    padding: 0.6rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: background 120ms ease, transform 80ms ease;
}
.ussd-keypad button:hover { background: #374151; }
.ussd-keypad button:active { transform: scale(.95); background: #4b5563; }

/* Instructions */
.ussd-instructions {
    list-style: decimal;
    padding-left: 1.2rem;
    font-size: var(--fs-sm);
    color: var(--clr-text-muted);
    line-height: 1.8;
}

/* Menu map */
.ussd-menu-map { display: flex; flex-direction: column; gap: var(--sp-sm); }
.menu-item {
    display: flex; align-items: center; gap: var(--sp-sm);
    font-size: var(--fs-sm); font-weight: var(--fw-medium);
}

@media (max-width: 768px) {
    .ussd-device { width: 100%; max-width: 320px; }
}
</style>

<?php
$pageScripts = ['assets/js/ussdSimulator.js'];
require __DIR__ . '/includes/footer.php';
?>
