(function () {
    'use strict';

    // Immediate theme check to prevent screen flash
    const storedTheme = localStorage.getItem('cit-theme') || 'light';
    if (storedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Theme switch logic
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            const updateToggleIcon = () => {
                const isDark = document.body.classList.contains('dark-theme');
                themeToggle.innerHTML = isDark ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-stars-fill"></i>';
                themeToggle.setAttribute('aria-label', isDark ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối');
            };
            
            updateToggleIcon();
            
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-theme');
                const isDark = document.body.classList.contains('dark-theme');
                localStorage.setItem('cit-theme', isDark ? 'dark' : 'light');
                updateToggleIcon();
            });
        }
    });

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

    const revealItems = document.querySelectorAll('.section-space, .section-space-sm, .stats-bar, .feature-card, .activity-card, .mini-card, .gallery-item, .cta-banner');
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
        revealItems.forEach(item => item.setAttribute('data-reveal', ''));
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealItems.forEach(item => observer.observe(item));
    } else {
        revealItems.forEach(item => item.classList.add('is-visible'));
    }
})();
