<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page-cache.php';
require_once __DIR__ . '/includes/editable.php';

$requestedAlbum = $_GET['album'] ?? 'aeternum';
$cacheAlbum = is_string($requestedAlbum) ? $requestedAlbum : 'aeternum';
if (count($_GET) <= 1 && page_cache_start('home-' . $cacheAlbum, 600)) {
    exit;
}

require_once __DIR__ . '/includes/home.php';

const GALLERY_MAX_PHOTOS = 6;

$pageTitle = 'Trang chủ';
$pageScripts = [
    'assets/js/navbar.min.js',
    'assets/js/gallery.min.js',
];
$preloadImages = ['assets/images/cit/cit-cover.webp'];

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
?>
<section class="hero">
    <img class="hero-bg-img"
         src="assets/images/cit/cit-cover.webp"
         alt=""
         width="1000"
         height="370"
         fetchpriority="high"
         decoding="async"
         aria-hidden="true">
    <div class="container">
        <div class="hero-content">
            <span class="hero-kicker">
                <i class="bi bi-lightning-charge-fill"></i>
                Câu lạc bộ Công nghệ Thông tin
            </span>
            <h1><?= get_editable_content('home_title', 'CIT Club — Cộng đồng công nghệ sinh viên TMU', 'text') ?></h1>
            <p class="hero-lead"><?= get_editable_content('home_desc', 'Đây là điểm đến trực tuyến của fanpage chính thức CIT, nơi chúng tôi chọn lọc hình ảnh và câu chuyện từ AETERNUM, HTTT, Birthday With CIT và Vinh danh CIT. Khám phá phong cách hoạt động: học thật, làm thật và kết nối thật.', 'html') ?></p>
            <div class="hero-ctas">
                <a class="btn btn-club btn-lg px-4 py-3" href="recruitment.php" id="hero-cta-join">
                    <i class="bi bi-person-plus-fill me-2"></i>Ứng tuyển CIT ngay
                </a>
                <a class="btn btn-outline-glass btn-lg px-4 py-3" href="https://www.facebook.com/clbcongnghe.cit" target="_blank" rel="noopener" id="hero-cta-explore">
                    <i class="bi bi-facebook me-2"></i>Khám phá fanpage
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section-space section-muted">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Fanpage CIT Club</p>
                <h2 class="display-6 fw-bold mb-0">Điểm nhấn từ fanpage chính thức</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-narrow mx-auto mb-0">
                    Các hoạt động nổi bật được chọn lọc từ album công khai và bài đăng fanpage, phản ánh đời sống thực tế của CLB.
                </p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-fire"></i></div>
                    <h3 class="h5 fw-bold mb-2">AETERNUM</h3>
                    <p class="text-secondary text-flow mb-0">Khoảnh khắc kỷ niệm 2 năm thành lập và cuộc thi HackerRank nội bộ, thể hiện tinh thần học hỏi và sáng tạo của CIT.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-mic-fill"></i></div>
                    <h3 class="h5 fw-bold mb-2">HTTT</h3>
                    <p class="text-secondary text-flow mb-0">Hội thảo chuyên môn giúp thành viên tiếp cận thực tế ngành và xây dựng kỹ năng cạnh tranh.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-people-fill"></i></div>
                    <h3 class="h5 fw-bold mb-2">Vinh danh & Sinh nhật</h3>
                    <p class="text-secondary text-flow mb-0">Khoảnh khắc gắn kết nội bộ và vinh danh thành viên, tạo niềm tin cộng đồng trong mỗi hoạt động.</p>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="stats-bar">
    <div class="container">
        <div class="row g-0 justify-content-center">
            <?php foreach ([
                ['2+ năm', 'Hành trình CIT'],
                ['30+ album', 'Khoảnh khắc'],
                ['140+', 'Thành viên được vinh danh'],
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
            <div class="col-md-6 col-lg-4 d-flex">
                <?php render_cta_banner([
                    'layout' => 'vertical',
                    'badgeIcon' => 'bi-megaphone-fill',
                    'badgeText' => 'Đang mở đơn',
                    'title' => 'Trở thành thành viên CIT!',
                    'copy' => 'Không cần kinh nghiệm - chỉ cần tinh thần học hỏi và sự chủ động.',
                    'btnText' => 'Đăng ký ngay',
                    'btnUrl' => 'recruitment.php',
                    'btnId' => 'activities-cta-join',
                    'btnIcon' => 'bi-arrow-right-circle',
                ]); ?>
            </div>
        </div>
    </div>
</section>

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

<section class="section-space-sm">
    <div class="container">
        <?php render_cta_banner([
            'layout' => 'horizontal',
            'badgeIcon' => 'bi-stars',
            'badgeText' => 'Mở đơn tuyển thành viên',
            'title' => 'Sẵn sàng gia nhập gia đình CIT?',
            'copy' => 'Đừng lo nếu bạn chưa có nhiều kinh nghiệm - chúng mình tìm kiếm tinh thần học hỏi, sự chủ động và nhiệt huyết của bạn.',
            'btnText' => 'Đăng ký thành viên',
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
