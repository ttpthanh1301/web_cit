/* ─── CIT Club — Gallery JS (Lightbox) ─────────────── */
(function () {
    'use strict';

    const overlay   = document.getElementById('lightbox');
    const lightImg  = document.getElementById('lightboxImg');
    const closeBtn  = document.getElementById('lightboxClose');
    const prevBtn   = document.getElementById('lightboxPrev');
    const nextBtn   = document.getElementById('lightboxNext');

    if (!overlay) return;

    let items   = [];
    let current = 0;

    function collectItems() {
        items = Array.from(document.querySelectorAll('.gallery-item[data-src]'));
    }

    function openLightbox(idx) {
        collectItems();
        current = idx;
        showImage();
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    function closeLightbox() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function showImage() {
        const item = items[current];
        if (!item) return;
        lightImg.src  = item.dataset.src;
        lightImg.alt  = item.dataset.alt || '';
        prevBtn.style.display = items.length <= 1 ? 'none' : '';
        nextBtn.style.display = items.length <= 1 ? 'none' : '';
    }

    function goNext() {
        current = (current + 1) % items.length;
        showImage();
    }
    function goPrev() {
        current = (current - 1 + items.length) % items.length;
        showImage();
    }

    /* click gallery items */
    document.addEventListener('click', e => {
        const item = e.target.closest('.gallery-item[data-src]');
        if (item) {
            collectItems();
            openLightbox(items.indexOf(item));
        }
    });

    /* keyboard on gallery items */
    document.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            const item = document.activeElement?.closest('.gallery-item[data-src]');
            if (item) {
                e.preventDefault();
                collectItems();
                openLightbox(items.indexOf(item));
            }
        }
        if (!overlay.classList.contains('active')) return;
        if (e.key === 'Escape')      closeLightbox();
        if (e.key === 'ArrowRight') goNext();
        if (e.key === 'ArrowLeft')  goPrev();
    });

    closeBtn?.addEventListener('click', closeLightbox);
    nextBtn?.addEventListener('click', goNext);
    prevBtn?.addEventListener('click', goPrev);

    /* close on backdrop click */
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeLightbox();
    });
})();
