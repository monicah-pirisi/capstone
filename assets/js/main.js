/**
 * Samburu EWS — main.js
 * Shared utilities loaded on every page.
 * ─────────────────────────────────────
 * 1. Mobile nav drawer toggle
 * 2. Dropdown menus (hover desktop / click mobile)
 * 3. Scroll-reveal animations (IntersectionObserver)
 * 4. Toast / status-message helper
 */

(function () {
    'use strict';

    /* ═══════════════════════════════════════════
       1. MOBILE NAV DRAWER
       ═══════════════════════════════════════════ */

    const navToggle = document.getElementById('navToggle');
    const primaryNav = document.getElementById('primaryNav');

    /* Create backdrop overlay */
    const backdrop = document.createElement('div');
    backdrop.className = 'nav-backdrop';
    document.body.appendChild(backdrop);

    if (navToggle && primaryNav) {
        function setNavOpen(open) {
            navToggle.setAttribute('aria-expanded', String(open));
            navToggle.classList.toggle('open', open);
            primaryNav.classList.toggle('open', open);
            backdrop.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
        }

        navToggle.addEventListener('click', () => {
            setNavOpen(navToggle.getAttribute('aria-expanded') !== 'true');
        });

        // Close on backdrop click
        backdrop.addEventListener('click', () => setNavOpen(false));

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && primaryNav.classList.contains('open')) {
                setNavOpen(false);
                navToggle.focus();
            }
        });

        // Close when a plain (non-dropdown-toggle) nav link is clicked on mobile
        primaryNav.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) setNavOpen(false);
            });
        });
    }

    /* ═══════════════════════════════════════════
       2. DROPDOWN MENUS
       ═══════════════════════════════════════════ */

    const dropdownParents = document.querySelectorAll('.has-dropdown');

    const isMobile = () => window.innerWidth <= 768;

    dropdownParents.forEach(parent => {
        /* Support both old .dropdown-toggle and new split .dropdown-btn */
        const btn = parent.querySelector('.dropdown-btn') || parent.querySelector('.dropdown-toggle');
        if (!btn) return;

        /* Click arrow/button toggles dropdown */
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = parent.classList.contains('open');
            // Close all other open dropdowns
            dropdownParents.forEach(p => {
                p.classList.remove('open');
                const b = p.querySelector('.dropdown-btn') || p.querySelector('.dropdown-toggle');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
            if (!isOpen) {
                parent.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });

        /* Keyboard: open on Enter/Space, close on Escape */
        btn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                parent.classList.toggle('open');
                btn.setAttribute('aria-expanded',
                    String(parent.classList.contains('open')));
            }
            if (e.key === 'Escape') {
                parent.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
                btn.focus();
            }
        });
    });

    /* Close dropdowns when clicking outside */
    document.addEventListener('click', (e) => {
        dropdownParents.forEach(parent => {
            if (!parent.contains(e.target)) {
                parent.classList.remove('open');
                const b = parent.querySelector('.dropdown-btn') || parent.querySelector('.dropdown-toggle');
                if (b) b.setAttribute('aria-expanded', 'false');
            }
        });
    });

    /* ═══════════════════════════════════════════
       3. SCROLL-REVEAL ANIMATIONS
       ═══════════════════════════════════════════
       Any element with class .animate-on-scroll fades in
       when it enters the viewport. Add the class in PHP/HTML.
    */

    /* Auto-apply scroll animation to cards and section headers
       that are outside the sticky header / hero (already visible on load) */
    const SKIP_INSIDE = '.site-header, .hero';
    document.querySelectorAll(
        '.card, .stat-card, .chart-container, .section-header, .alert-banner'
    ).forEach(el => {
        if (!el.closest(SKIP_INSIDE)) {
            el.classList.add('animate-on-scroll');
        }
    });

    if ('IntersectionObserver' in window) {
        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    scrollObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -32px 0px'
        });

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            scrollObserver.observe(el);
        });
    } else {
        /* Fallback for old browsers */
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            el.classList.add('visible');
        });
    }

    /* ═══════════════════════════════════════════
       4. TOAST / STATUS MESSAGE HELPER
       ═══════════════════════════════════════════
       Usage:
         EWS.toast('Data loaded successfully');
         EWS.toast('Connection lost', 'error');
         EWS.toast('Saving…', 'info', 0);  // persistent until dismissed
    */

    // Create toast container once
    let toastContainer = document.getElementById('ews-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'ews-toast-container';
        toastContainer.setAttribute('aria-live', 'polite');
        toastContainer.setAttribute('aria-atomic', 'true');
        Object.assign(toastContainer.style, {
            position: 'fixed',
            bottom: '1.5rem',
            right: '1.5rem',
            display: 'flex',
            flexDirection: 'column',
            gap: '0.5rem',
            zIndex: '9999',
            maxWidth: '360px',
            width: '100%',
            pointerEvents: 'none'
        });
        document.body.appendChild(toastContainer);
    }

    const TOAST_ICONS = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    const TOAST_COLORS = {
        success: { bg: '#d1e7dd', border: '#198754', text: '#0f5132' },
        error: { bg: '#f8d7da', border: '#c0392b', text: '#842029' },
        warning: { bg: '#fff3cd', border: '#e67e22', text: '#664d03' },
        info: { bg: '#cfe2ff', border: '#2980b9', text: '#084298' }
    };

    /**
     * Show a toast message.
     * @param {string}  message  - Text to display
     * @param {string}  type     - 'success' | 'error' | 'warning' | 'info'
     * @param {number}  duration - ms before auto-dismiss (0 = manual close)
     * @returns {HTMLElement} the toast element
     */
    function showToast(message, type = 'success', duration = 4000) {
        const colors = TOAST_COLORS[type] || TOAST_COLORS.info;
        const icon = TOAST_ICONS[type] || TOAST_ICONS.info;

        const toast = document.createElement('div');
        toast.className = 'ews-toast';
        Object.assign(toast.style, {
            display: 'flex',
            alignItems: 'center',
            gap: '0.6rem',
            padding: '0.75rem 1rem',
            background: colors.bg,
            border: '1px solid ' + colors.border,
            borderLeft: '4px solid ' + colors.border,
            borderRadius: '0.5rem',
            color: colors.text,
            fontSize: '0.875rem',
            fontFamily: "'Inter', sans-serif",
            fontWeight: '500',
            boxShadow: '0 4px 12px rgba(0,0,0,.12)',
            pointerEvents: 'auto',
            opacity: '0',
            transform: 'translateX(40px)',
            transition: 'opacity 250ms ease, transform 250ms ease'
        });

        // Close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Dismiss notification');
        Object.assign(closeBtn.style, {
            marginLeft: 'auto',
            background: 'none',
            border: 'none',
            fontSize: '1.15rem',
            cursor: 'pointer',
            color: colors.text,
            lineHeight: '1',
            padding: '0 0.15rem',
            opacity: '.7'
        });

        toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
        toast.appendChild(closeBtn);
        toastContainer.appendChild(toast);

        // Slide in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        /** Dismiss the toast */
        function dismiss() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            setTimeout(() => toast.remove(), 300);
        }

        closeBtn.addEventListener('click', dismiss);

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }

        return toast;
    }

    /* ═══════════════════════════════════════════
       5. PUBLIC API
       ═══════════════════════════════════════════ */

    window.EWS = window.EWS || {};
    window.EWS.toast = showToast;

})();
