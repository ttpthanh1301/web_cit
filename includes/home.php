<?php
declare(strict_types=1);

function image_variant(string $fullPath): array
{
    return [
        'full' => $fullPath,
        'thumb' => str_replace('assets/images/cit/albums/', 'assets/images/cit/thumbs/albums/', $fullPath),
    ];
}

$albums = [
    'aeternum' => [
        'label' => 'Aeternum',
        'cover' => 'assets/images/cit/albums/aeternum-hackerrank.webp',
        'desc' => 'Cuộc thi lập trình AETERNUM HackerRank',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/aeternum/aeternum-%02d.webp', $i)),
            range(1, 14)
        ),
    ],
    'birthday' => [
        'label' => 'Birthday with CIT',
        'cover' => 'assets/images/cit/albums/birthday-with-cit.webp',
        'desc' => 'Sinh nhật cùng CLB Công nghệ CIT',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/birthday-with-cit/birthday-with-cit-%02d.webp', $i)),
            range(1, 12)
        ),
    ],
    'httt' => [
        'label' => 'HTTT',
        'cover' => 'assets/images/cit/albums/httt.webp',
        'desc' => 'Hội thảo Thông tin Thị trường Lao động',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/httt/httt-%02d.webp', $i)),
            range(1, 12)
        ),
    ],
    'vinh-danh' => [
        'label' => 'Vinh danh CIT',
        'cover' => 'assets/images/cit/albums/vinh-danh-cit.webp',
        'desc' => 'Lễ vinh danh thành viên CIT xuất sắc',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/vinh-danh-cit/vinh-danh-cit-%02d.webp', $i)),
            range(1, 8)
        ),
    ],
    'tan-cu-nhan' => [
        'label' => 'Tân cử nhân',
        'cover' => 'assets/images/cit/tan-cu-nhan-04.webp',
        'desc' => 'Chúc mừng các tân cử nhân CIT',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/tan-cu-nhan/tan-cu-nhan-%02d.webp', $i)),
            range(1, 5)
        ),
    ],
];

$features = [
    [
        'number' => '01',
        'icon' => 'bi-book-half',
        'title' => 'Học hỏi',
        'copy' => 'Workshop, seminar và nội dung fanpage chia sẻ kiến thức công nghệ thực tế dành cho sinh viên.',
    ],
    [
        'number' => '02',
        'icon' => 'bi-code-slash',
        'title' => 'Thực chiến',
        'copy' => 'Tham gia cuộc thi nội bộ HackerRank và dự án nhóm để rèn luyện tư duy và kỹ năng lập trình.',
    ],
    [
        'number' => '03',
        'icon' => 'bi-people-fill',
        'title' => 'Kết nối',
        'copy' => 'Gặp gỡ bạn bè cùng đam mê, tạo dựng mạng lưới và phát triển cộng đồng học tập.',
    ],
];

$activities = [
    [
        'image' => 'assets/images/cit/albums/aeternum-hackerrank.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/aeternum-hackerrank.webp',
        'width' => 843,
        'height' => 674,
        'alt' => 'Cuộc thi lập trình AETERNUM HackerRank',
        'tag_icon' => 'bi-trophy',
        'tag' => 'AETERNUM',
        'title' => 'AETERNUM & HackerRank nội bộ',
        'copy' => 'Kỉ niệm 2 năm CIT với cuộc thi lập trình nội bộ, khẳng định tinh thần học tập và đồng đội.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/httt.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/httt.webp',
        'width' => 843,
        'height' => 707,
        'alt' => 'Hội thảo thông tin thị trường lao động',
        'tag_icon' => 'bi-mic',
        'tag' => 'HTTT',
        'title' => 'Hội thảo HTTT',
        'copy' => 'Buổi chia sẻ nơi CIT kết nối sinh viên với xu hướng tuyển dụng và thực tế ngành IT.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/birthday-with-cit.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/birthday-with-cit.webp',
        'width' => 590,
        'height' => 835,
        'alt' => 'Sinh nhật cùng CLB CIT',
        'tag_icon' => 'bi-balloon-heart',
        'tag' => 'Gắn kết',
        'title' => 'Birthday With CIT',
        'copy' => 'Những khoảnh khắc sinh nhật ấm áp, giúp thành viên cảm nhận sự quan tâm và đồng hành trong CLB.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/vinh-danh-cit.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/vinh-danh-cit.webp',
        'width' => 590,
        'height' => 835,
        'alt' => 'Vinh danh thành viên CIT xuất sắc',
        'tag_icon' => 'bi-award',
        'tag' => 'Vinh danh',
        'title' => 'Vinh danh CIT',
        'copy' => 'Ghi nhận đóng góp, nỗ lực và tinh thần kế thừa của các thành viên đồng hành với CLB.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/tan-cu-nhan-04.webp',
        'thumb' => 'assets/images/cit/thumbs/tan-cu-nhan-04.webp',
        'width' => 1108,
        'height' => 1478,
        'alt' => 'Chúc mừng các tân cử nhân',
        'tag_icon' => 'bi-mortarboard',
        'tag' => 'Tân cử nhân',
        'title' => 'Chúc mừng tân cử nhân',
        'copy' => 'Tri ân thế hệ nối tiếp và ghi nhận hành trình sinh viên trưởng thành cùng CIT.',
        'loading' => 'lazy',
    ],
];

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
    <?php
}
