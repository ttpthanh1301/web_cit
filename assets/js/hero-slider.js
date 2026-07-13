(function () {
    'use strict';

    const slider = document.querySelector('[data-hero-slider]');
    if (!slider) return;

    const slides = Array.from(slider.querySelectorAll('.hero-slide'));
    const dots = Array.from(slider.querySelectorAll('[data-slide-to]'));
    if (slides.length < 2) return;

    const interval = Math.max(2000, Number(slider.dataset.interval) || 3000);
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let current = 0;
    let timer = null;

    function loadSlide(index) {
        const slide = slides[index];
        if (!slide || !slide.dataset.src) return;

        slide.src = slide.dataset.src;
        if (slide.dataset.srcset) slide.srcset = slide.dataset.srcset;
        delete slide.dataset.src;
        delete slide.dataset.srcset;
    }

    function showSlide(index) {
        const next = (index + slides.length) % slides.length;
        loadSlide(next);
        slides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === next));
        dots.forEach((dot, dotIndex) => {
            const active = dotIndex === next;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-current', active ? 'true' : 'false');
        });
        current = next;
        loadSlide((current + 1) % slides.length);
    }

    function stop() {
        if (timer !== null) window.clearInterval(timer);
        timer = null;
    }

    function start() {
        if (reduceMotion || document.hidden || timer !== null) return;
        timer = window.setInterval(() => showSlide(current + 1), interval);
    }

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            showSlide(Number(dot.dataset.slideTo) || 0);
            stop();
            start();
        });
    });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());

    if (!reduceMotion) window.setTimeout(() => loadSlide(1), 1000);
    start();
})();
