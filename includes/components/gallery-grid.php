<?php
declare(strict_types=1);

$current = $albums[$activeAlbum];
?>
<div id="galleryContent">
<nav class="album-tabs mb-4" aria-label="Chọn album">
    <ul class="nav gap-2 flex-wrap" id="galleryTabs" role="tablist">
        <?php foreach ($albums as $key => $album): ?>
        <li class="nav-item" role="presentation">
            <a href="?album=<?= urlencode((string) $key) ?>#gallery"
               class="nav-link <?= $activeAlbum === $key ? 'active' : '' ?>"
               aria-label="Xem album <?= e((string) $album['label']) ?>">
                <?= e((string) $album['label']) ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
<p class="text-secondary gallery-summary mb-4">
    <i class="bi bi-images me-1"></i>
    <?= e((string) $current['desc']) ?> - <?= count($current['photos']) ?> ảnh
</p>
<div class="gallery-grid" id="galleryGrid">
    <?php foreach ($current['photos'] as $idx => $photo):
        $altText = (string) $current['desc'];
        $full = is_array($photo) ? (string) ($photo['full'] ?? $photo['thumb'] ?? '') : (string) $photo;
        $thumb = is_array($photo) ? (string) ($photo['thumb'] ?? $full) : (string) $photo;
    ?>
    <figure class="gallery-item mb-0"
            data-index="<?= (int) $idx ?>"
            data-src="<?= e($full) ?>"
            data-alt="<?= e($altText) ?>"
            role="button"
            tabindex="0"
            aria-label="Xem ảnh <?= (int) $idx + 1 ?> - <?= e($altText) ?>">
        <img src="<?= e($thumb) ?>"
             alt="<?= e($altText) ?> - ảnh <?= (int) $idx + 1 ?>"
             width="480"
             height="360"
             loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>"
             decoding="async"
             sizes="(max-width: 480px) 50vw, (max-width: 992px) 33vw, 25vw">
        <div class="gallery-item-overlay">
            <span><i class="bi bi-zoom-in me-1"></i>Phóng to</span>
        </div>
    </figure>
    <?php endforeach; ?>
</div>
</div>
