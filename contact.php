<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/page-cache.php';

if (empty($_GET) && page_cache_start('contact', 3600)) {
    exit;
}

$pageTitle = 'Liên hệ';
$pageScripts = ['assets/js/navbar.min.js'];
$preloadImages = ['assets/images/cit/cit-cover.webp'];

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-about">
    <img class="page-hero-img"
         src="assets/images/cit/cit-cover.webp"
         alt=""
         width="1000"
         height="370"
         fetchpriority="high"
         decoding="async"
         aria-hidden="true">
    <div class="container">
        <div class="page-hero-content">
            <span class="hero-kicker">
                <i class="bi bi-chat-dots-fill"></i>
                Liên hệ CIT
            </span>
            <h1>Kết nối với Câu lạc bộ Công nghệ Thông tin CIT</h1>
            <p class="hero-lead mb-0">
                Theo dõi fanpage để cập nhật hoạt động mới nhất, hoặc gửi đơn ứng tuyển nếu bạn muốn trở thành thành viên của CIT.
            </p>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7">
                <article class="info-panel h-100">
                    <span class="section-eyebrow">Kênh chính thức</span>
                    <h2 class="h3 fw-bold mb-3">Fanpage CLB Công nghệ CIT</h2>
                    <p class="text-secondary text-flow mb-4">
                        Fanpage là kênh cập nhật hoạt động chính thức của CIT: bài đăng, album ảnh, thông báo tuyển thành viên và câu chuyện đời sống CLB. Website chọn lọc nội dung để hiển thị những điểm nhấn tiêu biểu.
                    </p>
                    <a class="btn btn-club px-4 py-3" href="https://www.facebook.com/clbcongnghe.cit" target="_blank" rel="noopener">
                        <i class="bi bi-facebook me-2"></i>Theo dõi fanpage CIT
                    </a>
                </article>
            </div>
            <div class="col-lg-5">
                <article class="info-panel h-100">
                    <span class="section-eyebrow">Tham gia CLB</span>
                    <h2 class="h3 fw-bold mb-3">Gửi đơn ứng tuyển</h2>
                    <p class="text-secondary text-flow mb-4">
                        Nếu bạn muốn học hỏi, thực hành dự án và đồng hành trong các hoạt động công nghệ, hãy gửi thông tin qua form tuyển thành viên.
                    </p>
                    <a class="btn btn-outline-primary px-4 py-3" href="recruitment.php">
                        <i class="bi bi-person-plus-fill me-2"></i>Đến form ứng tuyển
                    </a>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="section-space section-muted">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-eyebrow justify-content-center">Thông tin nhanh</p>
            <h2 class="display-6 fw-bold">Các cách tìm CIT</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-geo-alt"></i></div>
                    <h3 class="h5 fw-bold mb-2">Địa điểm</h3>
                    <p class="text-secondary text-flow mb-0">Trường Đại học Thương mại.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-facebook"></i></div>
                    <h3 class="h5 fw-bold mb-2">Facebook</h3>
                    <p class="text-secondary text-flow mb-0">facebook.com/clbcongnghe.cit — kênh chính thức cập nhật album và tin tức CIT.</p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <h3 class="h5 fw-bold mb-2">Ứng tuyển</h3>
                    <p class="text-secondary text-flow mb-0">Form tuyển thành viên được lưu trực tiếp vào hệ thống quản trị hiện có và giúp ban điều hành theo dõi ứng viên nhanh nhất.</p>                </article>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php page_cache_end(); ?>
