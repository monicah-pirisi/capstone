/**
 * Samburu EWS - currentAlert.js
 *
 * Fetches /api/current-alert-data.php and renders the full
 * Current Alert dashboard: gauge, sub-score bars, reasons,
 * data inputs, stakeholder cards, and channel messages.
 */

(function () {
    'use strict';

    const API_URL = 'api/current-alert-data.php';

    // ── Level configuration ────────────────────────────────────────
    const LEVEL_CFG = {
        Normal:    { icon: '🟢', css: 'level-normal',    label: 'NORMAL: LOW RISK',        color: '#16a34a', sthClass: 'sth-normal'    },
        Watch:     { icon: '🔵', css: 'level-watch',     label: 'WATCH: MODERATE RISK',    color: '#2563eb', sthClass: 'sth-alert'     },
        Alert:     { icon: '🟠', css: 'level-alert',     label: 'ALERT: HIGH RISK',        color: '#d97706', sthClass: 'sth-alert'     },
        Alarm:     { icon: '🔴', css: 'level-alarm',     label: 'ALARM: VERY HIGH RISK',   color: '#dc2626', sthClass: 'sth-alarm'     },
        Emergency: { icon: '⛔', css: 'level-emergency', label: 'EMERGENCY: CRITICAL',     color: '#7f1d1d', sthClass: 'sth-emergency' },
    };

    // Only these three keys are scored — Balint et al. (2013) CDI weights
    const SCORED_KEYS = ['rainfall', 'ndvi', 'temperature'];

    const BAR_COLORS = {
        rainfall:    { good: '#2980b9', mod: '#2980b9', stress: '#dc2626' },
        ndvi:        { good: '#16a34a', mod: '#d97706', stress: '#dc2626' },
        temperature: { good: '#16a34a', mod: '#d97706', stress: '#dc2626' },
    };

    const BAR_LABELS = {
        rainfall:    'Rainfall',
        ndvi:        'Vegetation',
        temperature: 'Temperature',
    };

    const BAR_WEIGHTS = {
        rainfall: 50, ndvi: 25, temperature: 25,
    };

    const BAR_DESCS = {
        rainfall:    'Current vs long-term average',
        ndvi:        'VCI vegetation index vs normal (threshold 35)',
        temperature: 'KMD forecast max vs normal 30°C',
    };

    // ── Icons for data inputs ──────────────────────────────────────
    const INPUT_CARDS = [
        { key: 'ndvi',                   icon: '🌿', label: 'VCI (Vegetation)',  unit: '',    note: 'Current VCI vs normal 35' },
        { key: 'rainfall_mm',            icon: '🌧',  label: 'Rainfall',          unit: ' mm', note: 'vs long-term avg' },
        { key: 'rainfall_avg_mm',        icon: '📊',  label: 'LTM Rainfall',      unit: ' mm', note: 'Long-term monthly average' },
        { key: 'water_distance_km',      icon: '💧',  label: 'Water Distance',    unit: ' km', note: 'vs normal distance' },
        { key: 'livestock_condition',    icon: '🐄',  label: 'Livestock',         unit: '',    note: 'Body condition rating' },
        { key: 'food_consumption_score', icon: '🍽',  label: 'Food Score',        unit: '/42', note: 'Derived from bulletin %' },
        { key: 'indigenous_outlook',     icon: '🌍',  label: 'Indigenous',        unit: '',    note: 'Derived from 10 indicators' },
    ];

    // ── Fetch ──────────────────────────────────────────────────────
    fetch(API_URL)
        .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
        .then(data => {
            if (!data.ok) throw new Error(data.error || 'API error');

            const loading = document.getElementById('loadingOverlay');
            const command = document.getElementById('alertCommandSection');
            if (loading) loading.style.display = 'none';
            if (command) command.style.display = '';

            const a = data.assessment;
            const s = data.sources;
            const inputs = data.inputs || {};

            renderAlertBanner(a);
            renderGauge(a.score, a.risk_level);
            renderScaleMarker(a.score);
            renderSubScores(a.sub_scores);
            renderReasons(a.reasons);
            renderDataInputs(inputs);
            renderRouting(a.recommended_actions, a.risk_level);
            renderMessages(a.channel_messages);
            renderNDMA(s.ndma);
            renderKMD(s.kmd);
            initTabs();
            initCopyButtons();

            if (window.EWS && window.EWS.toast) {
                const lvl   = a.risk_level || 'Unknown';
                const tType = ['Normal','Watch'].includes(lvl) ? 'success' : lvl === 'Emergency' ? 'error' : 'warning';
                EWS.toast(`Risk level: ${lvl}, Score: ${a.score}`, tType, 4000);
            }
        })
        .catch(err => {
            console.error('Alert fetch error:', err);
            const loading = document.getElementById('loadingOverlay');
            if (loading) loading.innerHTML = `
                <div class="container" style="text-align:center;padding:var(--sp-2xl) 0;">
                    <p style="color:var(--clr-danger);font-size:var(--fs-md);">
                        ⚠️ Failed to load alert data. Please refresh or try again.
                    </p>
                </div>`;
        });

    // ── 1. Alert Banner ────────────────────────────────────────────
    function renderAlertBanner(a) {
        const panel = document.getElementById('alertBanner');
        if (!panel) return;
        const cfg = LEVEL_CFG[a.risk_level] || LEVEL_CFG.Alert;
        panel.className = 'ca-phase-panel ' + cfg.css;
        setText('alertIcon',  cfg.icon);
        setText('alertLevel', cfg.label);
        setText('alertTime',  'Assessed: ' + formatDate(a.assessed_at));
    }

    // ── 2. Circular Gauge ─────────────────────────────────────────
    function renderGauge(score, level) {
        const wrap = document.getElementById('scoreGauge');
        if (!wrap) return;

        const cfg    = LEVEL_CFG[level] || LEVEL_CFG.Alert;
        const color  = cfg.color;
        const r      = 70, cx = 90, cy = 90;
        const circ   = 2 * Math.PI * r;          // 439.8
        const offset = circ * (1 - score / 100); // how much to leave unfilled

        // Phase ring: thin outer ring coloured by phase
        const rOuter = 82;
        const circO  = 2 * Math.PI * rOuter;

        wrap.innerHTML = `
        <svg viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg">
          <!-- Background track -->
          <circle cx="${cx}" cy="${cy}" r="${r}"
                  fill="none" stroke="#e5e7eb" stroke-width="14"/>
          <!-- Coloured progress arc (starts at top: rotate -90) -->
          <circle cx="${cx}" cy="${cy}" r="${r}"
                  fill="none" stroke="${color}" stroke-width="14"
                  stroke-linecap="round"
                  stroke-dasharray="${circ}"
                  stroke-dashoffset="${circ}"
                  transform="rotate(-90 ${cx} ${cy})"
                  class="gauge-arc"
                  data-offset="${offset}"/>
          <!-- Score text -->
          <text x="${cx}" y="${cy - 8}" text-anchor="middle"
                font-size="34" font-weight="800" fill="${color}"
                font-family="Inter, sans-serif">${score}</text>
          <text x="${cx}" y="${cy + 14}" text-anchor="middle"
                font-size="11" fill="#9ca3af"
                font-family="Inter, sans-serif">out of 100</text>
          <!-- Level label -->
          <text x="${cx}" y="${cy + 32}" text-anchor="middle"
                font-size="9.5" font-weight="600" fill="${color}"
                font-family="Inter, sans-serif" letter-spacing="0.05em"
                text-transform="uppercase">${level.toUpperCase()}</text>
        </svg>`;

        // Animate after paint
        requestAnimationFrame(() => {
            setTimeout(() => {
                const arc = wrap.querySelector('.gauge-arc');
                if (arc) {
                    arc.style.transition = 'stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1)';
                    arc.style.strokeDashoffset = arc.dataset.offset;
                }
            }, 120);
        });
    }

    // ── 3. Scale Marker ───────────────────────────────────────────
    function renderScaleMarker(score) {
        const marker = document.getElementById('scaleMarker');
        if (!marker) return;
        // Scale: 100→0 maps to 0%→100% from left
        // Normal 80–100 = 0–20% from right = left 0–20%
        // Alert  60–79  = left 20–40%
        // Alarm  40–59  = left 40–60%
        // Emergency 0–39 = left 60–100%
        const leftPct = 100 - score;
        marker.style.left = Math.max(0, Math.min(100, leftPct)) + '%';
    }

    // ── 4. Sub-Score Bars ─────────────────────────────────────────
    function renderSubScores(sub) {
        const container = document.getElementById('subScoreBars');
        const card      = document.getElementById('subScoresCard');
        if (!container || !sub) return;

        container.innerHTML = SCORED_KEYS.filter(key => sub[key] !== undefined).map(key => { const val = sub[key];
            const pct    = Math.min(Math.round(val), 100);
            const weight = BAR_WEIGHTS[key] || 0;
            const contrib = ((val * weight) / 100).toFixed(1);
            const colors = BAR_COLORS[key] || { good: '#6c757d', mod: '#6c757d', stress: '#6c757d' };
            const color  = pct >= 75 ? colors.good : pct >= 50 ? colors.mod : colors.stress;
            const label  = BAR_LABELS[key]  || key;
            const desc   = BAR_DESCS[key]   || '';

            return `
            <div class="subscore-row">
                <div class="subscore-meta">
                    <span class="subscore-label">${label}
                        <span style="font-weight:400;color:var(--clr-text-muted);font-size:var(--fs-xs);"> — ${desc}</span>
                    </span>
                    <span class="subscore-weight">${weight}% weight</span>
                </div>
                <div class="subscore-track-wrap">
                    <div class="subscore-track">
                        <div class="subscore-fill"
                             style="width:0%;background:${color};"
                             data-width="${pct}">${pct}</div>
                        <div class="subscore-threshold thr-alert"  title="Alert threshold (60)"></div>
                        <div class="subscore-threshold thr-normal" title="Normal threshold (80)"></div>
                    </div>
                    <span class="subscore-contrib">+${contrib} pts</span>
                </div>
            </div>`;
        }).join('');

        // Animate bars
        requestAnimationFrame(() => {
            setTimeout(() => {
                container.querySelectorAll('.subscore-fill[data-width]').forEach(el => {
                    el.style.transition = 'width .9s cubic-bezier(.4,0,.2,1)';
                    el.style.width = el.dataset.width + '%';
                });
            }, 150);
        });

        if (card) card.style.display = '';
    }

    // ── 5. Reasons ────────────────────────────────────────────────
    function renderReasons(reasons) {
        const list = document.getElementById('reasonsList');
        const card = document.getElementById('reasonsCard');
        if (!list) return;

        if (!Array.isArray(reasons) || reasons.length === 0) {
            list.innerHTML = `
                <div class="ca-reason-ok">
                    <span style="font-size:1.2rem;">✅</span>
                    <span>All indicators are within acceptable range. Continue monitoring conditions.</span>
                </div>`;
        } else {
            list.innerHTML = reasons.map(r => {
                const isObj = typeof r === 'object' && r !== null;
                const sev   = isObj ? (r.severity || 'medium') : 'medium';
                const text  = isObj ? r.text : String(r);
                const icon  = sev === 'high' ? '🔴' : '🟠';
                return `
                <div class="ca-reason-item ca-reason-${sev}">
                    <span class="ca-reason-icon">${icon}</span>
                    <span>${esc(text)}</span>
                </div>`;
            }).join('');
        }

        if (card) card.style.display = '';
    }

    // ── 6. Data Inputs ────────────────────────────────────────────
    function renderDataInputs(inputs) {
        const grid = document.getElementById('dataInputsGrid');
        if (!grid) return;

        grid.innerHTML = INPUT_CARDS.map(c => {
            const raw = inputs[c.key];
            const val = raw !== undefined && raw !== null ? raw : '—';
            return `
            <div class="ca-input-card">
                <div class="ca-input-icon">${c.icon}</div>
                <div class="ca-input-label">${c.label}</div>
                <div class="ca-input-value">${esc(String(val))}${val !== '—' ? c.unit : ''}</div>
                <div class="ca-input-note">${c.note}</div>
            </div>`;
        }).join('');
    }

    // ── 7. Stakeholder Cards ──────────────────────────────────────
    function renderRouting(actions, level) {
        const grid = document.getElementById('stakeholderGrid');
        if (!grid || !Array.isArray(actions)) return;

        const cfg = LEVEL_CFG[level] || LEVEL_CFG.Alert;

        grid.innerHTML = actions.map(a => `
            <div class="ca-stakeholder-card ${cfg.sthClass}">
                <div class="ca-sth-icon">${a.icon || '📌'}</div>
                <div class="ca-sth-name">${esc(a.stakeholder)}</div>
                <div class="ca-sth-action">${esc(a.action)}</div>
            </div>`).join('');
    }

    // ── 8. Messages ───────────────────────────────────────────────
    function renderMessages(msgs) {
        if (!msgs) return;
        setMsg('msgWhatsApp', msgs.whatsapp);
        setMsg('msgFacebook', msgs.facebook);
        setMsg('msgRadio30',  msgs.radio_30s);
        setMsg('msgRadio60',  msgs.radio_60s);
        let ussd = msgs.ussd_status || '';
        if (msgs.ussd_actions) ussd += '\n\n─────────────\n\n' + msgs.ussd_actions;
        setMsg('msgUSSD', ussd);
    }

    // ── 9. NDMA Source Card ───────────────────────────────────────
    function renderNDMA(ndma) {
        if (!ndma) return;
        const card = document.getElementById('ndmaCard');
        if (!card) return;
        setText('ndmaBulletin', ndma.bulletin      || '');
        setText('ndmaSummary',  ndma.summary       || '');
        setText('ndmaPhase',    ndma.phase_stated  || '—');
        setText('ndmaUpdated',  formatDate(ndma.updated_at));
        const link = document.getElementById('ndmaLink');
        if (link && ndma.url) link.href = ndma.url;
        card.style.display = '';
    }

    // ── 10. KMD Source Card ───────────────────────────────────────
    function renderKMD(kmd) {
        if (!kmd) return;
        const card = document.getElementById('kmdCard');
        if (!card) return;
        setText('kmdPeriod',  kmd.valid_period || '');
        setText('kmdAdvisory', kmd.advisory    || '');
        setText('kmdUpdated',  formatDate(kmd.updated_at));
        const outlook = kmd.outlook || {};
        const outlookText = typeof outlook === 'object' && !Array.isArray(outlook)
            ? Object.values(outlook).join(' ')
            : (typeof outlook === 'string' ? outlook : '');
        setText('kmdOutlook', outlookText || '—');
        const link = document.getElementById('kmdLink');
        if (link && kmd.url) link.href = kmd.url;
        card.style.display = '';
    }

    // ── Tabs ──────────────────────────────────────────────────────
    function initTabs() {
        const btns = document.querySelectorAll('.ca-tab-btn[data-tab]');
        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                btns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
                document.querySelectorAll('.ca-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                btn.setAttribute('aria-selected','true');
                const panel = document.getElementById(btn.dataset.tab);
                if (panel) panel.classList.add('active');
            });
        });
    }

    // ── Copy Buttons ──────────────────────────────────────────────
    function initCopyButtons() {
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.target);
                if (!target) return;
                const text = target.textContent;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text)
                        .then(() => { if (window.EWS && window.EWS.toast) EWS.toast('Copied to clipboard!', 'success', 2000); })
                        .catch(() => fallbackCopy(text));
                } else {
                    fallbackCopy(text);
                }
            });
        });
    }

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try {
            document.execCommand('copy');
            if (window.EWS && window.EWS.toast) EWS.toast('Copied to clipboard!', 'success', 2000);
        } catch {
            if (window.EWS && window.EWS.toast) EWS.toast('Copy failed, please select and copy manually', 'warning');
        }
        ta.remove();
    }

    // ── Utilities ─────────────────────────────────────────────────
    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '';
    }

    function setMsg(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text || '(No template available for this level)';
    }

    function formatDate(iso) {
        if (!iso) return '—';
        try {
            return new Date(iso).toLocaleDateString('en-GB', {
                day: 'numeric', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        } catch { return iso; }
    }

    function esc(str) {
        if (typeof str !== 'string') return '';
        const el = document.createElement('span');
        el.textContent = str;
        return el.innerHTML;
    }

})();
