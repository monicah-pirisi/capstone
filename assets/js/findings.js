/**
 * Samburu EWS — findings.js
 *
 * Fetches /api/findings-data.php and renders:
 *   1. Study overview meta card
 *   2. Seven emergent theme cards (with key quote + STS finding)
 *   3. Barrier detail cards (with evidence quotes, affected groups, design implication)
 *   4. Recommendation cards (exactly as per Chapter 5 §5.3)
 */

(function () {
    'use strict';

    const API_URL = 'api/findings-data.php';
    const loading = document.getElementById('loadingOverlay');

    /* ═══════════════════════════════════════════
       FETCH
       ═══════════════════════════════════════════ */

    fetch(API_URL)
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (!data.ok) throw new Error(data.error || 'API error');
            if (loading) loading.style.display = 'none';
            renderMeta(data.meta);
            renderThemes(data.themes);
            renderBarriers(data.barriers_detail);
            renderRecommendations(data.recommendations);
        })
        .catch(err => {
            console.error('Findings fetch error:', err);
            if (loading) {
                loading.innerHTML = '<p style="color:var(--clr-danger);padding:var(--sp-xl);">Failed to load findings data. Please try again later.</p>';
            }
        });

    /* ═══════════════════════════════════════════
       STUDY OVERVIEW
       ═══════════════════════════════════════════ */

    function renderMeta(meta) {
        if (!meta) return;

        const card = document.getElementById('studyOverview');
        if (!card) return;

        card.innerHTML = `
            <div class="so-meta">
                <div class="so-meta-item">
                    <strong>Participants</strong>
                    <span>${esc(String(meta.n))}</span>
                </div>
                <div class="so-meta-item">
                    <strong>Method</strong>
                    <span>${esc(meta.method || '')}</span>
                </div>
                <div class="so-meta-item">
                    <strong>Location</strong>
                    <span>${esc(meta.location || '')}</span>
                </div>
                <div class="so-meta-item">
                    <strong>Period</strong>
                    <span>${esc(meta.period || '')}</span>
                </div>
            </div>
            <div class="so-note">${esc(meta.note || '')}</div>
        `;

        show('sectionMeta');
    }

    /* ═══════════════════════════════════════════
       SEVEN THEMES
       ═══════════════════════════════════════════ */

    function renderThemes(themes) {
        const container = document.getElementById('themeCards');
        if (!container || !Array.isArray(themes)) return;

        container.innerHTML = themes.map(t => `
            <div class="theme-card">
                <div class="theme-card-header">
                    <div class="theme-number">${t.number}</div>
                    <div>
                        <h3>${esc(t.title)}</h3>
                        <span class="theme-rq-badge">${esc(t.rq)}</span>
                    </div>
                </div>
                <div class="theme-card-body">
                    <p class="theme-summary">${esc(t.summary)}</p>
                    <blockquote class="theme-quote">"${esc(t.key_quote)}"</blockquote>
                    <div class="theme-finding">${esc(t.finding)}</div>
                </div>
            </div>
        `).join('');

        show('sectionThemes');
    }

    /* ═══════════════════════════════════════════
       BARRIER CARDS
       ═══════════════════════════════════════════ */

    function renderBarriers(barriers) {
        const container = document.getElementById('barrierCards');
        if (!container || !Array.isArray(barriers)) return;

        container.innerHTML = barriers.map(b => {
            const groups = (b.affected_groups || [])
                .map(g => `<span class="group-tag">${esc(g)}</span>`)
                .join('');

            return `
                <div class="barrier-card">
                    <div class="barrier-card-header">
                        <div class="barrier-rank">${b.rank}</div>
                        <div>
                            <h3>${esc(b.barrier)}</h3>
                        </div>
                    </div>
                    <div class="barrier-card-body">
                        <p class="barrier-desc">${esc(b.description)}</p>

                        <div class="barrier-evidence">
                            <div class="barrier-evidence-label">Evidence from interviews</div>
                            <p>${esc(b.evidence)}</p>
                        </div>

                        ${groups ? `<div class="barrier-groups">${groups}</div>` : ''}

                        <div class="barrier-implication">
                            <strong>Design implication</strong>
                            ${esc(b.design_implication)}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        show('sectionBarriers');
    }

    /* ═══════════════════════════════════════════
       RECOMMENDATION CARDS
       ═══════════════════════════════════════════ */

    function renderRecommendations(recs) {
        const container = document.getElementById('recCards');
        if (!container || !Array.isArray(recs)) return;

        container.innerHTML = recs.map(r => {
            const priorityClass =
                r.priority === 'Critical' ? 'priority-critical' :
                r.priority === 'High'     ? 'priority-high'     : 'priority-medium';

            const tags = (r.addresses_barriers || [])
                .map(b => `<span class="rec-barrier-tag">${esc(b)}</span>`)
                .join('');

            return `
                <div class="rec-card">
                    <div class="rec-card-header">
                        <div class="rec-number">${r.number}</div>
                        <div class="rec-card-header-text">
                            <h3>${esc(r.title)}</h3>
                            <span class="priority-badge ${priorityClass}">${esc(r.priority)}</span>
                        </div>
                    </div>
                    <div class="rec-card-body">
                        <p class="rec-desc">${esc(r.description)}</p>

                        <div class="rec-responsible">
                            <strong>Responsible</strong>
                            ${esc(r.responsible)}
                        </div>

                        ${tags ? `
                        <div class="rec-barrier-tags">
                            <span class="rec-barrier-tag-label">Addresses:</span>
                            ${tags}
                        </div>` : ''}
                    </div>
                </div>
            `;
        }).join('');

        show('sectionRecs');
    }

    /* ═══════════════════════════════════════════
       UTILITY
       ═══════════════════════════════════════════ */

    function show(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = '';
    }

    function esc(str) {
        if (typeof str !== 'string') return String(str ?? '');
        const el = document.createElement('span');
        el.textContent = str;
        return el.innerHTML;
    }

})();
