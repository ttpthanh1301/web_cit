/* ─── CIT Club — Counter JS (Stats Bar) ─────────────── */
(function () {
    'use strict';

    function animateCounter(el) {
        const text  = el.textContent.trim();
        const match = text.match(/\d+/);
        if (!match) return;
        const end   = parseInt(match[0], 10);
        const suffix = text.replace(/\d+/, '').trim();
        const dur   = 1400;
        const step  = dur / 60;
        let cur     = 0;
        const timer = setInterval(() => {
            cur = Math.min(cur + Math.ceil(end / 60), end);
            el.textContent = cur + suffix;
            if (cur >= end) clearInterval(timer);
        }, step);
    }

    const statsObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.querySelectorAll('.stat-number').forEach(animateCounter);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: .4 });

    document.querySelectorAll('.stats-bar').forEach(el => statsObserver.observe(el));
})();
