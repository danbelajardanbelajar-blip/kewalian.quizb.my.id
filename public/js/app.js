/**
 * app.js — Dashboard Wali Kelas
 * Global JavaScript utilities
 */

'use strict';

// ============================================================
// Clock widget di footer
// ============================================================
(function initClock() {
    const el = document.getElementById('clockDisplay');
    if (!el) return;

    function updateClock() {
        const now = new Date();
        const hh  = String(now.getHours()).padStart(2, '0');
        const mm  = String(now.getMinutes()).padStart(2, '0');
        const ss  = String(now.getSeconds()).padStart(2, '0');
        el.textContent = `${hh}:${mm}:${ss}`;
    }

    updateClock();
    setInterval(updateClock, 1000);
})();

// ============================================================
// Auto-dismiss flash messages setelah 5 detik
// ============================================================
(function autoDismissFlash() {
    const alerts = document.querySelectorAll('#flash-messages .alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
})();

// ============================================================
// Tooltip Bootstrap (aktifkan semua)
// ============================================================
(function initTooltips() {
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"], [title]');
    tooltipEls.forEach(el => {
        if (el.hasAttribute('title') && !el.getAttribute('data-bs-toggle')) {
            el.setAttribute('data-bs-toggle', 'tooltip');
        }
    });
    bootstrap.Tooltip.Default.allowList['button'] = [];
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
})();

// ============================================================
// Konfirmasi sebelum aksi hapus (backup, karena sudah ada inline)
// ============================================================
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    const msg = btn.dataset.confirm || 'Apakah Anda yakin?';
    if (!confirm(msg)) e.preventDefault();
});

// ============================================================
// Animasi entry untuk cards
// ============================================================
(function animateCards() {
    const cards = document.querySelectorAll('.card-main, .stat-card');
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity    = '0';
        card.style.transform  = 'translateY(16px)';
        card.style.transition = 'opacity .4s ease, transform .4s ease';
        observer.observe(card);
    });
})();
