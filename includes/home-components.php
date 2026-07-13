<?php
declare(strict_types=1);

function render_feature_card(array $feature): void
{
    ?>
    <article class="feature-card">
        <div class="feature-number"><?= e((string) $feature['number']) ?></div>
        <div class="feature-icon-wrap">
            <i class="bi <?= e((string) $feature['icon']) ?>"></i>
        </div>
        <h3 class="h5 fw-bold mb-2"><?= e((string) $feature['title']) ?></h3>
        <p class="text-secondary text-flow mb-0"><?= e((string) $feature['copy']) ?></p>
    </article>
    <?php
}

function render_activity_card(array $activity): void
{
    $image = (string) ($activity['thumb'] ?? $activity['image']);
    ?>
    <article class="activity-card">
        <div class="activity-card-img">
            <img<?= asset_attrs([
                'src' => $image,
                'alt' => $activity['alt'],
                'width' => $activity['width'],
                'height' => $activity['height'],
                'loading' => $activity['loading'] ?? 'lazy',
                'decoding' => 'async',
                'srcset' => $image . ' 480w',
                'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
            ]) ?>>
        </div>
        <div class="activity-card-body">
            <span class="activity-tag">
                <i class="bi <?= e((string) $activity['tag_icon']) ?> me-1"></i><?= e((string) $activity['tag']) ?>
            </span>
            <h3 class="h6 fw-bold mb-1"><?= e((string) $activity['title']) ?></h3>
            <p class="text-secondary card-copy mb-0"><?= e((string) $activity['copy']) ?></p>
        </div>
    </article>
    <?php
}

function render_cta_banner(array $data): void
{
    $layout = $data['layout'] ?? 'horizontal';
    $badgeIcon = $data['badgeIcon'] ?? 'bi-megaphone-fill';
    $badgeText = $data['badgeText'] ?? 'Đang mở đơn';
    $title = $data['title'] ?? '';
    $copy = $data['copy'] ?? '';
    $btnText = $data['btnText'] ?? 'Đăng ký ngay';
    $btnUrl = $data['btnUrl'] ?? 'recruitment.php';
    $btnId = $data['btnId'] ?? 'cta-join';
    $btnIcon = $data['btnIcon'] ?? 'bi-arrow-right-circle';
    $isLarge = !empty($data['isLarge']);

    if ($layout === 'vertical') {
        ?>
        <div class="cta-banner w-100 d-flex flex-column justify-content-center">
            <span class="cta-badge"><i class="bi <?= e((string) $badgeIcon) ?>"></i> <?= e((string) $badgeText) ?></span>
            <h3 class="h4 fw-bold text-white mb-2"><?= e((string) $title) ?></h3>
            <p class="cta-copy mb-4"><?= e((string) $copy) ?></p>
            <a href="<?= e((string) $btnUrl) ?>" class="btn btn-club px-4 align-self-start" id="<?= e((string) $btnId) ?>">
                <i class="bi <?= e((string) $btnIcon) ?> me-1"></i><?= e((string) $btnText) ?>
            </a>
        </div>
        <?php
        return;
    }
    ?>
    <div class="cta-banner">
        <div class="row align-items-center g-4 cta-content">
            <div class="col-lg-8">
                <span class="cta-badge"><i class="bi <?= e((string) $badgeIcon) ?>"></i> <?= e((string) $badgeText) ?></span>
                <h2 class="h2 fw-bold text-white mb-2"><?= e((string) $title) ?></h2>
                <p class="cta-copy <?= $isLarge ? 'cta-copy-lg text-narrow' : '' ?> mb-0"><?= e((string) $copy) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= e((string) $btnUrl) ?>" class="btn btn-club <?= $isLarge ? 'btn-lg px-5 py-3' : '' ?>" id="<?= e((string) $btnId) ?>">
                    <i class="bi <?= e((string) $btnIcon) ?> me-2"></i><?= e((string) $btnText) ?>
                </a>
            </div>
        </div>
    </div>
    <?php
}

function render_gallery_grid(array $albums, string $activeAlbum): void
{
    $current = $albums[$activeAlbum];
    $hasMore = !empty($current['has_more']);
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
    <?php if ($hasMore): ?>
        <div class="text-center mt-4">
            <a class="btn btn-outline-primary" href="?album=<?= urlencode($activeAlbum) ?>&all=1#gallery">
                Xem thêm album
            </a>
        </div>
    <?php endif; ?>
    </div>
    <?php
}
