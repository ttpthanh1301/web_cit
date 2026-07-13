<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page-cache.php';
require_once __DIR__ . '/includes/editable.php';

$requestedAlbum = $_GET['album'] ?? 'aeternum';
$cacheableAlbums = ['aeternum', 'birthday', 'httt', 'vinh-danh', 'tan-cu-nhan'];
$cacheAlbum = is_string($requestedAlbum) && in_array($requestedAlbum, $cacheableAlbums, true)
    ? $requestedAlbum
    : 'aeternum';
$cacheableRequest = empty($_GET)
    || (count($_GET) === 1 && isset($_GET['album']) && $cacheAlbum === $requestedAlbum);
if ($cacheableRequest && page_cache_start('home-v3-' . $cacheAlbum, 600)) {
    exit;
}

require_once __DIR__ . '/includes/data/home.php';
require_once __DIR__ . '/includes/home-components.php';

// Tải cấu hình động
$contents = editable_contents();
$heroBg = get_hero_bg();
$heroBgSmall = $contents['hero_bg_small'] ?? $heroBg;
$heroBgWidth = max(1, (int) ($contents['hero_bg_width'] ?? 1000));
$heroBgHeight = max(1, (int) ($contents['hero_bg_height'] ?? 370));
$heroBgSmallWidth = max(1, (int) ($contents['hero_bg_small_width'] ?? $heroBgWidth));
$heroSlides = [[
    'full' => $heroBg,
    'small' => $heroBgSmall,
    'width' => $heroBgWidth,
    'height' => $heroBgHeight,
    'small_width' => $heroBgSmallWidth,
]];
for ($slideIndex = 2; $slideIndex <= 5; $slideIndex++) {
    $slideKey = 'hero_slide_' . $slideIndex . '_bg';
    if (empty($contents[$slideKey])) {
        continue;
    }

    $slideFull = $contents[$slideKey];
    $slideSmall = $contents[$slideKey . '_small'] ?? $slideFull;
    $slideWidth = max(1, (int) ($contents[$slideKey . '_width'] ?? 1920));
    $heroSlides[] = [
        'full' => $slideFull,
        'small' => $slideSmall,
        'width' => $slideWidth,
        'height' => max(1, (int) ($contents[$slideKey . '_height'] ?? 1080)),
        'small_width' => max(1, (int) ($contents[$slideKey . '_small_width'] ?? $slideWidth)),
    ];
}
$heroCtaText = $contents['hero_cta_text'] ?? 'Xem tuyển thành viên';
$heroCtaUrl = $contents['hero_cta_url'] ?? 'recruitment.php';
$heroExploreText = $contents['hero_explore_text'] ?? 'Khám phá fanpage';
$heroExploreUrl = $contents['hero_explore_url'] ?? 'https://www.facebook.com/clbcongnghe.cit';

// Tải danh sách section động
$sectionsList = [];
try {
    $cacheSectionsFile = __DIR__ . '/cache/sections.php';
    if (is_file($cacheSectionsFile)) {
        $sectionsList = require $cacheSectionsFile;
    } else {
        require_once __DIR__ . '/includes/db.php';
        $sectionsList = $pdo->query('SELECT section_key, is_visible FROM sections ORDER BY sort_order, id')->fetchAll(PDO::FETCH_ASSOC);
        write_php_cache($cacheSectionsFile, $sectionsList);
    }
} catch (Throwable $e) {
    $sectionsList = [
        ['section_key' => 'hero', 'is_visible' => 1],
        ['section_key' => 'highlights', 'is_visible' => 1],
        ['section_key' => 'stats', 'is_visible' => 1],
        ['section_key' => 'about', 'is_visible' => 1],
        ['section_key' => 'activities', 'is_visible' => 1],
        ['section_key' => 'gallery', 'is_visible' => 1],
    ];
}

const GALLERY_MAX_PHOTOS = 6;

$pageTitle = 'Trang chủ';
$pageScripts = [
    'assets/js/navbar.min.js',
    'assets/js/gallery.min.js',
];
if (count($heroSlides) > 1) {
    $pageScripts[] = 'assets/js/hero-slider.min.js';
}
$heroSrcset = $heroBgSmall !== $heroBg
    ? $heroBgSmall . ' ' . $heroBgSmallWidth . 'w, ' . $heroBg . ' ' . $heroBgWidth . 'w'
    : '';
$preloadImages = [[
    'href' => $heroBg,
    'srcset' => $heroSrcset,
    'sizes' => '100vw',
]];
$enableInlineEditing = isset($_COOKIE['cit_admin_session']) && $_COOKIE['cit_admin_session'] === '1';
$includeCsrfMeta = $enableInlineEditing;

$activeAlbum = $_GET['album'] ?? 'aeternum';
if (!is_string($activeAlbum) || !array_key_exists($activeAlbum, $albums)) {
    $activeAlbum = 'aeternum';
}
$showAllPhotos = ($_GET['all'] ?? '') === '1';
$totalAlbumPhotos = count($albums[$activeAlbum]['photos']);
if (!$showAllPhotos) {
    $albums[$activeAlbum]['photos'] = array_slice($albums[$activeAlbum]['photos'], 0, GALLERY_MAX_PHOTOS);
    $albums[$activeAlbum]['has_more'] = $totalAlbumPhotos > GALLERY_MAX_PHOTOS;
}

require_once __DIR__ . '/includes/header.php';

// Vòng lặp hiển thị Section động dựa theo cấu hình
foreach ($sectionsList as $sec) {
    if ((int)$sec['is_visible'] !== 1) {
        continue;
    }

    switch ($sec['section_key']) {
        case 'hero':
            ?>
            <section class="hero">
                <div class="hero-media" <?= count($heroSlides) > 1 ? 'data-hero-slider data-interval="3000"' : '' ?>>
                    <?php foreach ($heroSlides as $slidePosition => $slide): ?>
                        <?php
                        $slideSrcset = $slide['small'] !== $slide['full']
                            ? $slide['small'] . ' ' . $slide['small_width'] . 'w, ' . $slide['full'] . ' ' . $slide['width'] . 'w'
                            : '';
                        ?>
                        <img class="hero-bg-img hero-slide <?= $slidePosition === 0 ? 'is-active' : '' ?>"
                             <?= $slidePosition === 0 ? 'src="' . e($slide['small']) . '"' : 'data-src="' . e($slide['small']) . '"' ?>
                             <?= $slideSrcset !== '' ? ($slidePosition === 0 ? 'srcset' : 'data-srcset') . '="' . e($slideSrcset) . '" sizes="100vw"' : '' ?>
                             alt=""
                             width="<?= (int) $slide['width'] ?>"
                             height="<?= (int) $slide['height'] ?>"
                             <?= $slidePosition === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                             decoding="async"
                             aria-hidden="true">
                    <?php endforeach; ?>
                    <?php if (count($heroSlides) > 1): ?>
                        <div class="hero-slider-dots" aria-label="Chọn ảnh banner">
                            <?php foreach ($heroSlides as $slidePosition => $_slide): ?>
                                <button type="button"
                                        class="<?= $slidePosition === 0 ? 'is-active' : '' ?>"
                                        data-slide-to="<?= $slidePosition ?>"
                                        aria-label="Ảnh banner <?= $slidePosition + 1 ?>"
                                        aria-current="<?= $slidePosition === 0 ? 'true' : 'false' ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="container">
                    <div class="hero-content">
                        <span class="hero-kicker">
                            <i class="bi bi-lightning-charge-fill"></i>
                            Câu lạc bộ Công nghệ Trường Đại học Thương mại
                        </span>
                        <h1><span class="gradient-text"><?= get_editable_content('home_title_current', 'CIT Club — Cộng đồng công nghệ sinh viên TMU', 'text') ?></span></h1>
                        <p class="hero-lead"><?= get_editable_content('home_desc_current', 'CIT là câu lạc bộ về lĩnh vực Công nghệ đầu tiên trực thuộc Đoàn TNCS Hồ Chí Minh Trường Đại học Thương mại, dưới sự quản lý của Khoa Công nghệ số ứng dụng. ', 'html') ?></p>
                        <div class="hero-ctas">
                            <a class="btn btn-club btn-lg px-4 py-3" href="<?= e($heroCtaUrl) ?>" id="hero-cta-join">
                                <i class="bi bi-person-plus-fill me-2"></i><?= e($heroCtaText) ?>
                            </a>
                            <a class="btn btn-outline-glass btn-lg px-4 py-3" href="<?= e($heroExploreUrl) ?>" target="_blank" rel="noopener" id="hero-cta-explore">
                                <i class="bi bi-facebook me-2"></i><?= e($heroExploreText) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            break;

        case 'highlights':
            // Section removed per user request
            break;

        case 'stats':
            ?>
            <section class="stats-bar">
                <div class="container">
                    <div class="row g-0 justify-content-center">
                        <?php foreach ([
                            ['2+ năm', 'Hành trình CIT'],
                            ['30+ album', 'Khoảnh khắc'],
                            ['100+', 'Thành viên được vinh danh'],
                            ['100%', 'Tinh thần cởi mở'],
                        ] as [$number, $label]): ?>
                            <div class="col-6 col-sm-3 stat-item">
                                <span class="stat-number"><?= e($number) ?></span>
                                <span class="stat-label"><?= e($label) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;

        case 'about':
            ?>
            <section id="about" class="section-space">
                <div class="container">
                    <div class="row align-items-end gy-3 mb-5">
                        <div class="col-lg-7">
                            <p class="section-eyebrow">Về chúng mình</p>
                            <h2 class="display-6 fw-bold mb-0">
                                Một cộng đồng để bạn<br>tiến xa hơn cùng công nghệ
                            </h2>
                        </div>
                        <div class="col-lg-5">
                            <p class="text-secondary text-flow text-flow-lg mb-0">
                                CIT tạo môi trường thực hành cởi mở, nơi mọi thành viên đều có thể
                                chia sẻ kiến thức, thử sức với dự án thật và trưởng thành cùng nhau.
                            </p>
                        </div>
                    </div>
                    <div class="row g-4">
                        <?php foreach ($features as $feature): ?>
                            <div class="col-md-4">
                                <?php render_feature_card($feature); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;

        case 'activities':
            ?>
            <section id="activities" class="section-space section-muted">
                <div class="container">
                    <div class="text-center mb-5">
                        <p class="section-eyebrow justify-content-center">Hoạt động</p>
                        <h2 class="display-6 fw-bold">Những khoảnh khắc đáng nhớ</h2>
                        <p class="text-secondary text-narrow mx-auto mt-2 mb-0">
                            Từ hội thảo chuyên môn đến các sự kiện gắn kết - CIT luôn có điều gì đó
                            dành cho bạn.
                        </p>
                    </div>
                    <div class="row g-4">
                        <?php foreach ($activities as $activity): ?>
                            <div class="col-md-6 col-lg-4">
                                <?php render_activity_card($activity); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
            break;

        case 'gallery':
            ?>
            <section id="gallery" class="section-space">
                <div class="container">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
                        <div>
                            <p class="section-eyebrow mb-1">Album ảnh</p>
                            <h2 class="h3 fw-bold mb-0">Khoảnh khắc của CIT</h2>
                        </div>
                    </div>
                    <?php render_gallery_grid($albums, $activeAlbum); ?>
                </div>
            </section>

            <section class="section-space section-muted">
                <div class="container text-center mb-5">
                    <p class="section-eyebrow justify-content-center">Dấu ấn nổi bật</p>
                    <h2 class="display-6 fw-bold mb-2">Những khoảnh khắc làm nên CIT</h2>
                    <p class="text-secondary text-narrow mx-auto mb-0">Một vài cột mốc tiêu biểu trong học thuật, sự kiện và đời sống cộng đồng của CLB.</p>
                </div>
                <div class="container">
                    <div class="bento-grid">
                        <!-- Card 1 (2x2): Giải Nhất Startup 2025 -->
                        <div class="bento-card bento-card-2x2 overflow-hidden position-relative">
                            <div class="bento-card-glow" style="background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 70%);"></div>
                            <span class="bento-card-badge position-relative" style="z-index: 2;"><i class="bi bi-award-fill me-1"></i>Thành tích</span>
                            <div class="bento-card-media bento-card-media-featured rounded-3 overflow-hidden position-relative">
                                <img src="assets/images/cit/albums/nckh/giainhat_tmu.webp" alt="Giải Nhất TMU's Startup 2025" loading="lazy" decoding="async">
                            </div>
                            <div class="bento-card-content bento-card-content-featured position-relative" style="z-index: 2;">
                                <h3 class="h3 fw-bold mb-2">Giải Nhất TMU's Startup 2025</h3>
                                <p class="text-secondary mb-0">Dấu mốc ghi nhận tinh thần sáng tạo, khả năng làm việc nhóm và bản lĩnh trình bày ý tưởng của thành viên CIT.</p>
                            </div>
                        </div>

                        <!-- Card 2 (1x1): HackerRank nội bộ 2026 -->
                        <div class="bento-card bento-card-1x1 bento-card-compact">
                            <div class="bento-card-glow" style="background: radial-gradient(circle, rgba(124, 92, 255, 0.15) 0%, transparent 70%);"></div>
                            <span class="bento-card-badge" style="background: rgba(124, 92, 255, 0.1); color: #7c5cff;"><i class="bi bi-code-slash me-1"></i>Học thuật</span>
                            <div class="bento-card-content bento-card-content-start">
                                <h3 class="h4 fw-bold mb-2">HackerRank 2026</h3>
                                <p class="text-secondary mb-3">Sân chơi thuật toán nội bộ giúp thành viên rèn tư duy lập trình và tinh thần thi đấu lành mạnh.</p>
                                <div class="p-2 rounded bg-opacity-10 bg-dark mb-3 border border-secondary border-opacity-10" style="font-family: monospace; font-size: 0.78rem; line-height: 1.45;">
                                    <span class="text-success">$</span> rank list --top3<br>
                                    <span class="text-muted">1. Nguyễn Tuấn Anh</span><br>
                                    <span class="text-muted">2. Trần Nam Hải</span><br>
                                    <span class="text-muted">3. Đỗ Khánh Thảo</span>
                                </div>
                                <a class="fw-semibold text-decoration-none" href="https://www.facebook.com/photo/?fbid=873520835760503&set=pcb.873540535758533" target="_blank" rel="noopener">
                                    Xem chi tiết <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Card 3 (1x1): Reel nổi bật 25K views -->
                        <div class="bento-card bento-card-1x1 bento-card-compact">
                            <span class="bento-card-badge" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;"><i class="bi bi-play-btn-fill me-1"></i>Reels</span>
                            <div class="bento-card-content bento-card-content-start">
                                <h3 class="h5 fw-bold mb-1">Khoảnh khắc lan tỏa</h3>
                                <p class="text-secondary small mb-2">Video nổi bật đưa hình ảnh CIT đến gần hơn với cộng đồng sinh viên.</p>
                                <a class="btn btn-outline-glass btn-sm w-100" href="https://www.facebook.com/reel/1903048183715092/" target="_blank" rel="noopener">
                                    <i class="bi bi-facebook me-1"></i>Xem ngay
                                </a>
                            </div>
                        </div>

                        <!-- Card 4 (1x1): Luyện thuật toán -->
                        <div class="bento-card bento-card-1x1 bento-card-compact">
                            <span class="bento-card-badge" style="background: rgba(124, 92, 255, 0.1); color: #7c5cff;"><i class="bi bi-lightning-charge-fill me-1"></i>Kỹ năng</span>
                            <div class="bento-card-content bento-card-content-start">
                                <h3 class="h5 fw-bold mb-1">Luyện thuật toán</h3>
                                <p class="text-secondary small mb-2">Các buổi luyện đề giúp thành viên làm quen áp lực thời gian, tối ưu lời giải và chia sẻ cách tiếp cận bài toán.</p>
                                <div class="bento-mini-note">
                                    <span class="bento-mini-note-icon"><i class="bi bi-check2-circle"></i></span>
                                    <div>
                                        <strong>Code sạch hơn mỗi tuần</strong>
                                        <small>Review lời giải, học từ lỗi sai và cải thiện tốc độ xử lý.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4 (1x1): Reel Khoảnh khắc 5.8K views -->
                        <div class="bento-card bento-card-1x1 bento-card-compact">
                            <span class="bento-card-badge" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;"><i class="bi bi-play-btn-fill me-1"></i>Reels</span>
                            <div class="bento-card-content bento-card-content-start">
                                <h3 class="h5 fw-bold mb-1">Đời sống CLB</h3>
                                <p class="text-secondary small mb-2">Những lát cắt gần gũi từ các buổi gặp gỡ, chuẩn bị sự kiện và hoạt động nội bộ.</p>
                                <a class="btn btn-outline-glass btn-sm w-100" href="https://www.facebook.com/reel/2039372770790649/" target="_blank" rel="noopener">
                                    <i class="bi bi-facebook me-1"></i>Xem ngay
                                </a>
                            </div>
                        </div>

                        <!-- Card 5 (2x1): Nghiên cứu Khoa học cấp Khoa -->
                        <div class="bento-card bento-card-2x1">
                            <div class="bento-card-glow" style="background: radial-gradient(circle, rgba(34, 197, 94, 0.15) 0%, transparent 70%);"></div>
                            <span class="bento-card-badge" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="bi bi-journal-check me-1"></i>NCKH</span>
                            <div class="bento-card-content d-flex flex-row align-items-center justify-content-between gap-3">
                                <div>
                                    <h3 class="h4 fw-bold mb-1">Nghiên cứu khoa học</h3>
                                    <p class="text-secondary mb-0">CIT khuyến khích thành viên theo đuổi đề tài thực tiễn, rèn tư duy nghiên cứu và năng lực trình bày học thuật.</p>
                                </div>
                                <a class="btn btn-club btn-sm px-3 flex-shrink-0" href="https://www.facebook.com/photo/?fbid=850552041390716&set=pcb.850527074726546" target="_blank" rel="noopener">
                                    Xem thêm
                                </a>
                            </div>
                        </div>

                        <!-- Card 6 (1x1): Reel Câu chuyện CIT 3.8K views -->
                        <div class="bento-card bento-card-1x1">
                            <span class="bento-card-badge" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;"><i class="bi bi-play-btn-fill me-1"></i>Reels</span>
                            <div class="bento-card-content">
                                <h3 class="h5 fw-bold mb-1">Câu chuyện CIT</h3>
                                <p class="text-secondary small mb-2">Những câu chuyện nhỏ giữ lại tinh thần đồng hành, học hỏi và trưởng thành cùng nhau.</p>
                                <a class="btn btn-outline-glass btn-sm w-100" href="https://www.facebook.com/reel/998861632561849/" target="_blank" rel="noopener">
                                    <i class="bi bi-facebook me-1"></i>Xem ngay
                                </a>
                            </div>
                        </div>

                        <!-- Card 7 (1x1): Workshop Cardano -->
                        <div class="bento-card bento-card-1x1">
                            <span class="bento-card-badge"><i class="bi bi-globe2 me-1"></i>Sự kiện</span>
                            <div class="bento-card-content">
                                <h3 class="h5 fw-bold mb-1">Workshop công nghệ</h3>
                                <p class="text-secondary small mb-2">Không gian kết nối sinh viên với các chủ đề công nghệ mới, từ blockchain đến ứng dụng thực tiễn.</p>
                                <a class="fw-semibold text-decoration-none mt-auto small" href="https://www.facebook.com/photo/?fbid=789063120872942&set=a.408220595623865" target="_blank" rel="noopener">
                                    Xem thêm <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            break;
    }
}
?>

<section class="section-space-sm">
    <div class="container">
        <?php render_cta_banner([
            'layout' => 'horizontal',
            'badgeIcon' => 'bi-stars',
            'badgeText' => 'Tuyển thành viên',
            'title' => 'Sẵn sàng tìm hiểu hành trình CIT?',
            'copy' => 'Tìm hiểu quy trình, quyền lợi và để lại thông tin của bạn để không bỏ lỡ cơ hội đồng hành cùng CIT.',
            'btnText' => 'Xem thông tin tuyển thành viên',
            'btnUrl' => 'recruitment.php',
            'btnId' => 'bottom-cta-join',
            'btnIcon' => 'bi-person-plus-fill',
            'isLarge' => true,
        ]); ?>
    </div>
</section>

<div class="lightbox-overlay" id="lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
    <div class="lightbox-inner">
        <button class="lightbox-close" id="lightboxClose" aria-label="Đóng">
            <i class="bi bi-x-lg"></i>
        </button>
        <button class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Ảnh trước">
            <i class="bi bi-chevron-left"></i>
        </button>
        <img src="" alt="" id="lightboxImg" width="1200" height="900" decoding="async">
        <button class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Ảnh tiếp">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php page_cache_end(); ?>
