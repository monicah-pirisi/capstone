/**
 * Samburu EWS — currentAlert.js
 *
 * Fetches /api/current-alert-data.php and renders:
 *   • Alert banner with level + score
 *   • Sub-score bars
 *   • Reasons list
 *   • NDMA + KMD source cards
 *   • Indigenous indicator grid
 *   • Stakeholder routing table
 *   • Tabbed channel message previews (WhatsApp, Facebook, Radio, USSD)
 */

(function () {
    'use strict';

    const API_URL = 'api/current-alert-data.php';
    const loading = document.getElementById('loadingOverlay');

    /* ═══════════════════════════════════════════
       LEVEL CONFIG
       ═══════════════════════════════════════════ */

    const LEVEL_CFG = {
        Normal: { icon: '🟢', css: 'level-normal', label: 'NORMAL — LOW RISK' },
        Watch: { icon: '🔵', css: 'level-watch', label: 'WATCH — MODERATE RISK' },
        Alert: { icon: '🟠', css: 'level-alert', label: 'ALERT — HIGH RISK' },
        Alarm: { icon: '🔴', css: 'level-alarm', label: 'ALARM — VERY HIGH RISK' },
        Emergency: { icon: '⛔', css: 'level-emergency', label: 'EMERGENCY — CRITICAL' },
    };

    const BAR_COLORS = {
        ndvi: '#198754',
        rainfall: '#2980b9',
        livestock: '#e07b00',
        water: '#6f42c1',
        food_security: '#c0392b',
        indigenous: '#0f5132',
    };

    const BAR_LABELS = {
        ndvi: 'Vegetation',
        rainfall: 'Rainfall',
        livestock: 'Livestock',
        water: 'Water',
        food_security: 'Food Sec.',
        indigenous: 'Indigenous',
    };

    /* ═══════════════════════════════════════════
       FETCH
       ═══════════════════════════════════════════ */

    fetch(API_URL)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!data.ok) throw new Error(data.error || 'API error');
            if (loading) loading.style.display = 'none';

            const a = data.assessment;
            const s = data.sources;

            renderAlertBanner(a);
            renderSubScores(a.sub_scores);
            renderReasons(a.reasons);
            renderNDMA(s.ndma);
            renderKMD(s.kmd);
            renderIndigenous(s.indigenous);
            renderRouting(a.recommended_actions);
            renderMessages(a.channel_messages);
            initTabs();
            initCopyButtons();

            if (window.EWS?.toast) {
                const lvl = a.risk_level || 'Unknown';
                const tType = ['Normal', 'Watch'].includes(lvl) ? 'success' : (lvl === 'Emergency' ? 'error' : 'warning');
                EWS.toast(`Risk level: ${lvl} — Score: ${a.score}`, tType, 4000);
            }
        })
        .catch(err => {
            console.error('Alert fetch error:', err);
            if (loading) {
                loading.innerHTML = '<p style="color:var(--clr-danger);">⚠️ Failed to load alert data. Please try again later.</p>';
            }
        });

    /* ═══════════════════════════════════════════
       1. ALERT BANNER
       ═══════════════════════════════════════════ */

    function renderAlertBanner(a) {
        const el = document.getElementById('alertBanner');
        if (!el) return;

        const cfg = LEVEL_CFG[a.risk_level] || LEVEL_CFG.Alert;
        el.className = 'alert-banner-large ' + cfg.css;
        el.style.display = 'flex';

        setText('alertIcon', cfg.icon);
        setText('alertLevel', cfg.label);
        setText('alertScore', a.score);
        setText('alertTime', 'Assessed: ' + formatDate(a.assessed_at));
    }

    /* ═══════════════════════════════════════════
       2. SUB-SCORE BARS
       ═══════════════════════════════════════════ */

    function renderSubScores(sub) {
        const container = document.getElementById('subScoreBars');
        const card = document.getElementById('subScoresCard');
        if (!container || !sub) return;

        container.innerHTML = Object.entries(sub).map(([key, val]) => {
            const pct = Math.min(Math.round(val), 100);
            const color = BAR_COLORS[key] || '#6c757d';
            const label = BAR_LABELS[key] || key;
            return `
                <div class="subscore-row">
                    <span class="subscore-label">${label}</span>
                    <div class="subscore-track">
                        <div class="subscore-fill" style="width:${pct}%;background:${color};">${pct}</div>
                    </div>
                </div>`;
        }).join('');

        if (card) card.style.display = '';
    }

    /* ═══════════════════════════════════════════
       3. REASONS
       ═══════════════════════════════════════════ */

    function renderReasons(reasons) {
        const list = document.getElementById('reasonsList');
        const card = document.getElementById('reasonsCard');
        if (!list || !Array.isArray(reasons)) return;

        list.innerHTML = reasons.map(r => `<li>${esc(r)}</li>`).join('');
        if (card) card.style.display = '';
    }

    /* ═══════════════════════════════════════════
       4. NDMA SOURCE CARD
       ═══════════════════════════════════════════ */

    function renderNDMA(ndma) {
        if (!ndma) return;
        const card = document.getElementById('ndmaCard');
        if (!card) return;

        setText('ndmaBulletin', ndma.bulletin || '');
        setText('ndmaSummary', ndma.summary || '');
        setText('ndmaPhase', ndma.phase_stated || '—');
        setText('ndmaUpdated', formatDate(ndma.updated_at));

        const link = document.getElementById('ndmaLink');
        if (link && ndma.url) link.href = ndma.url;

        card.style.display = '';
    }

    /* ═══════════════════════════════════════════
       5. KMD SOURCE CARD
       ═══════════════════════════════════════════ */

    function renderKMD(kmd) {
        if (!kmd) return;
        const card = document.getElementById('kmdCard');
        if (!card) return;

        setText('kmdPeriod', kmd.valid_period || '');
        setText('kmdAdvisory', kmd.advisory || '');
        setText('kmdUpdated', formatDate(kmd.updated_at));

        const outlook = kmd.outlook || {};
        setText('kmdOutlook', outlook.most_likely || '—');
        setText('kmdBelowPct', (outlook.probability_below_normal_pct ?? '—') + '%');

        // Onset info — if nested object, extract expected
        const onset = typeof kmd.onset === 'object' ? (kmd.onset?.expected || '—') : (kmd.onset || '—');
        setText('kmdOnset', onset);

        const link = document.getElementById('kmdLink');
        if (link && kmd.url) link.href = kmd.url;

        card.style.display = '';
    }

    /* ═══════════════════════════════════════════
       6. INDIGENOUS INDICATORS
       ═══════════════════════════════════════════ */

    function renderIndigenous(src) {
        const grid = document.getElementById('indigenousGrid');
        if (!grid || !src) return;

        const indicators = src.indicators || [];
        if (!indicators.length) return;

        const reliabilityBadge = r => {
            const low = (r || '').toLowerCase();
            if (low.includes('high')) return 'badge-green';
            if (low.includes('medium')) return 'badge-amber';
            return 'badge-neutral';
        };

        grid.innerHTML = indicators.map(ind => `
            <div class="card indigenous-card">
                <div class="card-header">
                    <div class="card-icon">${categoryIcon(ind.category)}</div>
                    <div>
                        <h3 class="card-title">${esc(ind.name)}</h3>
                        <span class="badge ${reliabilityBadge(ind.reliability)}">${esc(ind.reliability)} reliability</span>
                    </div>
                </div>
                <div class="card-body">
                    <p style="font-size:var(--fs-sm);color:var(--clr-text-muted);">${esc(ind.description)}</p>
                    <dl class="indigenous-signals">
                        <dt>🔴 Drought signal</dt>
                        <dd>${esc(ind.signal_drought)}</dd>
                        <dt>🟢 Good season signal</dt>
                        <dd>${esc(ind.signal_good_season)}</dd>
                    </dl>
                    <p class="reliability"><strong>Seasons:</strong> ${(ind.season_relevance || []).join(', ')} · <em>Source: ${esc(ind.source)}</em></p>
                </div>
            </div>
        `).join('');
    }

    function categoryIcon(cat) {
        const map = {
            'Astronomical': '⭐',
            'Animal': '🐾',
            'Botanical': '🌿',
            'Entomological': '🐜',
            'Meteorological': '💨',
            'Traditional ritual': '🔮',
        };
        return map[cat] || '🔍';
    }

    /* ═══════════════════════════════════════════
       7. ROUTING TABLE
       ═══════════════════════════════════════════ */

    function renderRouting(actions) {
        const tbody = document.getElementById('routingBody');
        if (!tbody || !Array.isArray(actions)) return;

        tbody.innerHTML = actions.map(a => `
            <tr>
                <td><span style="margin-right:6px;">${a.icon || '📌'}</span><strong>${esc(a.stakeholder)}</strong></td>
                <td>${esc(a.action)}</td>
            </tr>
        `).join('');
    }

    /* ═══════════════════════════════════════════
       8. CHANNEL MESSAGES
       ═══════════════════════════════════════════ */

    function renderMessages(msgs) {
        if (!msgs) return;

        setMsg('msgWhatsApp', msgs.whatsapp);
        setMsg('msgFacebook', msgs.facebook);
        setMsg('msgRadio30', msgs.radio_30s);
        setMsg('msgRadio60', msgs.radio_60s);

        // Combine USSD screens
        let ussd = msgs.ussd_status || '';
        if (msgs.ussd_actions) ussd += '\n\n─────────\n\n' + msgs.ussd_actions;
        setMsg('msgUSSD', ussd);
    }

    function setMsg(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text || '(No template available for this level)';
    }

    /* ═══════════════════════════════════════════
       TABS
       ═══════════════════════════════════════════ */

    function initTabs() {
        const btns = document.querySelectorAll('.tab-btn[data-tab]');
        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Deactivate all
                btns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                // Activate clicked
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
                const panel = document.getElementById(btn.dataset.tab);
                if (panel) panel.classList.add('active');
            });
        });
    }

    /* ═══════════════════════════════════════════
       COPY BUTTONS
       ═══════════════════════════════════════════ */

    function initCopyButtons() {
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.target);
                if (!target) return;
                const text = target.textContent;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        if (window.EWS?.toast) EWS.toast('Copied to clipboard!', 'success', 2000);
                    }).catch(() => fallbackCopy(text));
                } else {
                    fallbackCopy(text);
                }
            });
        });
    }

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            if (window.EWS?.toast) EWS.toast('Copied to clipboard!', 'success', 2000);
        } catch {
            if (window.EWS?.toast) EWS.toast('Copy failed — please select and copy manually', 'warning');
        }
        ta.remove();
    }

    /* ═══════════════════════════════════════════
       UTILITY
       ═══════════════════════════════════════════ */

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '';
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
