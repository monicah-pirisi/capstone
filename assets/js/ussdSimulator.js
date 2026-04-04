/**
 * Samburu EWS — ussdSimulator.js
 *
 * Simulates a *384# USSD session.
 * Fetches live risk data from /api/current-alert-data.php
 * and drives a state-machine menu with:
 *   1. Current Alert Level
 *   2. Advice for Pastoralists
 *   3. Where to Get Help
 *   4. Change Language (English / Samburu)
 */

(function () {
    'use strict';

    const API_URL = 'api/current-alert-data.php';

    /* ── DOM refs ───────────────────────────── */
    const screen = document.getElementById('ussdScreen');
    const input = document.getElementById('ussdInput');
    const sendBtn = document.getElementById('ussdSend');
    const keypad = document.getElementById('ussdKeypad');
    const langInd = document.getElementById('ussdLangIndicator');

    /* ── State ──────────────────────────────── */
    let riskData = null;    // API response
    let currentMenu = 'home';
    let lang = 'en';         // 'en' or 'sm' (Samburu)

    /* ── Bilingual content ──────────────────── */
    const T = {
        en: {
            title: 'SAMBURU EWS *384#',
            welcome: 'Welcome to Samburu\nEarly Warning System',
            menu: [
                '1. Current Alert Level',
                '2. Advice for Pastoralists',
                '3. Where to Get Help',
                '4. Change Language',
            ],
            nav: '\n0. Back  00. Home',
            alertTitle: 'CURRENT ALERT',
            phase: 'Phase',
            score: 'Risk Score',
            rainfall: 'Rainfall',
            pasture: 'Pasture',
            water: 'Water trek.',
            livestock: 'Livestock',
            adviceTitle: 'PASTORALIST ADVICE',
            helpTitle: 'EMERGENCY CONTACTS',
            langTitle: 'SELECT LANGUAGE',
            langMenu: '1. English\n2. Samburu (Maa)',
            langSet: 'Language set to: ',
            invalid: 'Invalid option.\nPlease try again.',
            footer: '\nPowered by Samburu EWS',
        },
        sm: {
            title: 'SAMBURU EWS *384#',
            welcome: 'Supaa! Karibu kwa\nMfumo wa Onyo la Mapema',
            menu: [
                '1. Kiwango cha Tahadhari',
                '2. Ushauri kwa Wafugaji',
                '3. Msaada wa Dharura',
                '4. Badilisha Lugha',
            ],
            nav: '\n0. Rudi  00. Nyumbani',
            alertTitle: 'TAHADHARI YA SASA',
            phase: 'Awamu',
            score: 'Alama ya Hatari',
            rainfall: 'Mvua',
            pasture: 'Malisho',
            water: 'Umbali wa maji',
            livestock: 'Hali ya mifugo',
            adviceTitle: 'USHAURI KWA WAFUGAJI',
            helpTitle: 'NAMBARI ZA DHARURA',
            langTitle: 'CHAGUA LUGHA',
            langMenu: '1. Kiingereza\n2. Kisamburu (Maa)',
            langSet: 'Lugha imebadilishwa: ',
            invalid: 'Chaguo batili.\nTafadhali jaribu tena.',
            footer: '\nInatumia Samburu EWS',
        }
    };

    function t(key) { return (T[lang] || T.en)[key] || T.en[key] || ''; }

    /* ═══════════════════════════════════════════
       INITIALIZATION
       ═══════════════════════════════════════════ */

    fetch(API_URL)
        .then(res => res.json())
        .then(data => {
            if (!data.ok) throw new Error(data.error || 'API error');
            riskData = data;
            showHome();
        })
        .catch(err => {
            console.error('USSD API error:', err);
            display(t('title') + '\n\n❌ Service unavailable.\nPlease try again later.');
        });

    /* ── Event listeners ────────────────────── */
    sendBtn.addEventListener('click', handleSend);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') handleSend(); });

    keypad.addEventListener('click', e => {
        const key = e.target.dataset?.key;
        if (key) {
            input.value += key;
            input.focus();
        }
    });

    /* ═══════════════════════════════════════════
       MENU ENGINE
       ═══════════════════════════════════════════ */

    function handleSend() {
        const val = input.value.trim();
        input.value = '';
        input.focus();

        if (!val) return;

        // Global navigation
        if (val === '00') { currentMenu = 'home'; showHome(); return; }
        if (val === '0') { navigateBack(); return; }

        // Route to handler
        switch (currentMenu) {
            case 'home': handleHome(val); break;
            case 'alert': showHome(); break;         // Any input → back
            case 'advice': showHome(); break;
            case 'help': showHome(); break;
            case 'lang': handleLang(val); break;
            default: showHome(); break;
        }
    }

    function navigateBack() {
        showHome();
    }

    /* ── Home ───────────────────────────────── */

    function showHome() {
        currentMenu = 'home';
        const lines = [
            t('title'),
            '',
            t('welcome'),
            '─'.repeat(24),
            ...t('menu'),
            '',
            t('footer'),
        ];
        display(lines.join('\n'));
    }

    function handleHome(val) {
        switch (val) {
            case '1': showAlert(); break;
            case '2': showAdvice(); break;
            case '3': showHelp(); break;
            case '4': showLang(); break;
            default: display(t('invalid') + t('nav')); break;
        }
    }

    /* ── 1. Current Alert ───────────────────── */

    function showAlert() {
        currentMenu = 'alert';
        if (!riskData) { display('Loading data…'); return; }

        const a = riskData.assessment;
        const inp = riskData.inputs || {};
        const levelEmoji = { Normal: '🟢', Watch: '🔵', Alert: '🟠', Alarm: '🔴', Emergency: '⛔' };

        const lines = [
            t('title'),
            '',
            '── ' + t('alertTitle') + ' ──',
            '',
            (levelEmoji[a.risk_level] || '⚠️') + ' ' + t('phase') + ': ' + (a.risk_level || '—'),
            t('score') + ': ' + a.score + ' / 100',
            '',
            t('rainfall') + ': ' + (inp.rainfall_mm ?? '—') + ' mm',
            t('pasture') + ': NDVI ' + (inp.ndvi ?? '—'),
            t('water') + ': ' + (inp.water_distance_km ?? '—') + ' km',
            t('livestock') + ': ' + (inp.livestock_condition ?? '—'),
            t('nav'),
        ];
        display(lines.join('\n'));
    }

    /* ── 2. Advice ──────────────────────────── */

    function showAdvice() {
        currentMenu = 'advice';
        if (!riskData) { display('Loading…'); return; }

        const a = riskData.assessment;
        const level = a.risk_level || 'Alert';
        const actions = a.recommended_actions || [];

        // Find pastoralist-specific action
        let pastAction = '';
        for (const act of actions) {
            const name = (act.stakeholder || '').toLowerCase();
            if (name.includes('pastoralist') || name.includes('communit')) {
                pastAction = act.action;
                break;
            }
        }
        if (!pastAction && actions.length > 0) {
            pastAction = actions[0].action;
        }

        const adviceMap_en = {
            Normal: '✅ Conditions are normal.\n• Continue usual migration\n• Store water when possible\n• Watch for star & wind signs',
            Watch: '🔵 Watch conditions closely.\n• Conserve water reserves\n• Scout alternative pastures\n• Reduce herd splitting',
            Alert: '🟠 Take action now!\n• Sell/trade weak animals\n• Move herds to water points\n• Contact your chief\n• Store food supplies',
            Alarm: '🔴 URGENT!\n• Emergency destocking\n• Seek NGO feed support\n• Register for assistance\n• Do not travel far alone',
            Emergency: '⛔ EMERGENCY!\n• Go to nearest relief point\n• Register your household\n• Call hotline: 0800-723-253\n• Stay near your community',
        };
        const adviceMap_sm = {
            Normal: '✅ Hali ni ya kawaida.\n• Endelea na mifugo kama kawaida\n• Hifadhi maji\n• Angalia ishara za nyota',
            Watch: '🔵 Fuatilia hali kwa makini.\n• Hifadhi akiba ya maji\n• Tafuta maeneo mbadala\n• Punguza kutenganisha mifugo',
            Alert: '🟠 Chukua hatua sasa!\n• Uza wanyama dhaifu\n• Hamisha mifugo kwa maji\n• Wasiliana na chifu wako',
            Alarm: '🔴 HARAKA!\n• Uza mifugo kupita kiasi\n• Omba msaada wa chakula\n• Jisajilishe kwa msaada',
            Emergency: '⛔ DHARURA!\n• Nenda kituo cha msaada\n• Sajili kaya yako\n• Piga simu: 0800-723-253',
        };

        const map = lang === 'sm' ? adviceMap_sm : adviceMap_en;
        const advice = map[level] || map.Alert;

        const lines = [
            t('title'),
            '',
            '── ' + t('adviceTitle') + ' ──',
            '(' + level + ')',
            '',
            advice,
            '',
            pastAction ? '📢 ' + pastAction : '',
            t('nav'),
        ];
        display(lines.join('\n'));
    }

    /* ── 3. Help ────────────────────────────── */

    function showHelp() {
        currentMenu = 'help';

        const contacts_en = [
            '── ' + t('helpTitle') + ' ──',
            '',
            '📞 NDMA Hotline:',
            '   0800-723-253 (toll-free)',
            '',
            '📞 County Emergency:',
            '   020-123-4567',
            '',
            '📞 Kenya Red Cross:',
            '   1199',
            '',
            '📞 Police / Emergency:',
            '   999 or 112',
            '',
            '🏥 Nearest Relief Points:',
            '   Maralal, Wamba,',
            '   Baragoi, Archer\'s Post',
        ];

        const contacts_sm = [
            '── ' + t('helpTitle') + ' ──',
            '',
            '📞 NDMA Simu:',
            '   0800-723-253 (bure)',
            '',
            '📞 Dharura ya Kaunti:',
            '   020-123-4567',
            '',
            '📞 Msalaba Mwekundu:',
            '   1199',
            '',
            '📞 Polisi:',
            '   999 au 112',
            '',
            '🏥 Vituo vya Msaada:',
            '   Maralal, Wamba,',
            '   Baragoi, Archer\'s Post',
        ];

        const contacts = lang === 'sm' ? contacts_sm : contacts_en;

        const lines = [
            t('title'),
            '',
            ...contacts,
            t('nav'),
        ];
        display(lines.join('\n'));
    }

    /* ── 4. Language ────────────────────────── */

    function showLang() {
        currentMenu = 'lang';
        const lines = [
            t('title'),
            '',
            '── ' + t('langTitle') + ' ──',
            '',
            t('langMenu'),
            t('nav'),
        ];
        display(lines.join('\n'));
    }

    function handleLang(val) {
        if (val === '1') {
            lang = 'en';
            if (langInd) langInd.textContent = 'EN';
            display(t('langSet') + 'English' + '\n' + t('nav'));
            setTimeout(showHome, 1500);
        } else if (val === '2') {
            lang = 'sm';
            if (langInd) langInd.textContent = 'SM';
            display(t('langSet') + 'Samburu' + '\n' + t('nav'));
            setTimeout(showHome, 1500);
        } else {
            display(t('invalid') + t('nav'));
        }
    }

    /* ═══════════════════════════════════════════
       DISPLAY HELPER
       ═══════════════════════════════════════════ */

    function display(text) {
        if (!screen) return;
        screen.textContent = text;
        screen.scrollTop = 0;
    }

})();
