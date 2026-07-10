(function () {
    'use strict';

    document.addEventListener('click', event => {
        const anchor = event.target.closest('a[href^="#"]');
        if (!anchor) {
            return;
        }

        const target = document.querySelector(anchor.getAttribute('href'));
        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    const navbar = document.querySelector('.club-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('is-compact', window.scrollY > 40);
        }, { passive: true });
    }
})();
