<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/data/club-content.php';
require_once __DIR__ . '/includes/page-cache.php';

if (empty($_GET) && page_cache_start('about', 3600)) {
    exit;
}

$pageTitle = 'Giới thiệu';
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
                <i class="bi bi-info-circle-fill"></i>
                Giới thiệu CIT
            </span>
            <h1>Cộng đồng sinh viên yêu công nghệ</h1>
            <p class="hero-lead mb-0">
                CIT là câu lạc bộ về lĩnh vực Công nghệ đầu tiên trực thuộc Đoàn TNCS Hồ Chí Minh Trường Đại học Thương mại và dưới sự quản lý của Khoa Công nghệ số ứng dụng.
            </p>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-6">
                <article class="info-panel h-100">
                    <span class="section-eyebrow">Sứ mệnh</span>
                    <h2 class="h3 fw-bold mb-3">Tạo môi trường học thật, làm thật, kết nối thật</h2>
                    <p class="text-secondary text-flow mb-0">
                        CIT đồng hành cùng sinh viên yêu công nghệ thông qua workshop, seminar, cuộc thi nội bộ, nghiên cứu khoa học và các hoạt động cộng đồng. Những nội dung này được minh chứng qua album và bài đăng công khai trên fanpage chính thức.
                    </p>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="info-panel h-100">
                    <span class="section-eyebrow">Tầm nhìn</span>
                    <h2 class="h3 fw-bold mb-3">Một cộng đồng công nghệ sinh viên năng động</h2>
                    <p class="text-secondary text-flow mb-0">
                        CIT hướng tới việc trở thành nơi kết nối sinh viên có chung đam mê, hỗ trợ phát triển chuyên môn, kỹ năng làm việc và tinh thần kế thừa giữa các thế hệ thành viên.
                    </p>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="section-space section-muted">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Giá trị cốt lõi</p>
                <h2 class="display-6 fw-bold mb-0">Những điều CIT luôn giữ trong mỗi hoạt động</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-flow text-flow-lg mb-0">
                    
                </p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($clubValues as $value): ?>
                <div class="col-md-6 col-lg-3">
                    <article class="mini-card h-100">
                        <div class="mini-card-icon"><i class="bi <?= e((string) $value['icon']) ?>"></i></div>
                        <h3 class="h5 fw-bold mb-2"><?= e((string) $value['title']) ?></h3>
                        <p class="text-secondary text-flow mb-0"><?= e((string) $value['copy']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space section-muted">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Nguồn fanpage</p>
                <h2 class="display-6 fw-bold mb-0">Album chính thức của CIT trên Facebook</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-flow text-flow-lg mb-0">
                    
                </p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-fire"></i></div>
                    <h3 class="h5 fw-bold mb-2">AETERNUM</h3>
                    <p class="text-secondary text-flow mb-0">Kỉ niệm 2 năm CIT và hành trình hoạt động chuyên môn.</p>
                </article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-mic-fill"></i></div>
                    <h3 class="h5 fw-bold mb-2">HTTT</h3>
                    <p class="text-secondary text-flow mb-0">Hội thảo chuyên môn về thị trường lao động và kỹ năng nghề nghiệp.</p>
                </article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-balloon-heart"></i></div>
                    <h3 class="h5 fw-bold mb-2">Birthday With CIT</h3>
                    <p class="text-secondary text-flow mb-0">Sinh nhật ấm áp và hoạt động gắn kết nội bộ.</p>
                </article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="mini-card h-100">
                    <div class="mini-card-icon"><i class="bi bi-award"></i></div>
                    <h3 class="h5 fw-bold mb-2">Vinh danh CIT</h3>
                    <p class="text-secondary text-flow mb-0">Tôn vinh đóng góp và truyền cảm hứng kế thừa.</p>
                </article>
            </div>
        </div>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-eyebrow justify-content-center">Lịch sử</p>
            <h2 class="display-6 fw-bold">Dấu mốc phát triển</h2>
            <p class="text-secondary text-narrow mx-auto mb-0">
                
            </p>
        </div>
        <div class="timeline-list">
            <?php foreach ($clubTimeline as $item): ?>
                <article class="timeline-item">
                    <span class="timeline-label"><?= e((string) $item['label']) ?></span>
                    <div>
                        <h3 class="h5 fw-bold mb-2"><?= e((string) $item['title']) ?></h3>
                        <p class="text-secondary text-flow mb-0"><?= e((string) $item['copy']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space section-muted">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Thành viên & Ban điều hành</p>
                <h2 class="display-6 fw-bold mb-0">Cấu trúc Câu lạc bộ</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-flow text-flow-lg mb-0">
                    Ban điều hành và đại diện các tiểu ban chuyên môn dẫn dắt các hoạt động học thuật, truyền thông và sự kiện của CIT.
                </p>
            </div>
        </div>

        <?php
        $bcn = array_slice($clubLeaders, 0, 3);
        $trung_ban = array_slice($clubLeaders, 3);
        ?>

        <h3 class="h4 fw-bold mb-4 text-center">Ban Chủ Nhiệm (Nhiệm kỳ 2025 - 2026)</h3>
        <div class="row g-4 mb-5 justify-content-center">
            <?php foreach ($bcn as $leader): ?>
                <div class="col-md-4">
                    <article class="mini-card h-100 text-center" style="border-top: 3px solid var(--club-accent);">
                        <div class="mini-card-icon mx-auto"><i class="bi bi-award-fill"></i></div>
                        <h4 class="h5 fw-bold mb-1"><?= e((string) $leader['name']) ?></h4>
                        <p class="text-primary fw-semibold mb-3"><?= e((string) $leader['role']) ?></p>
                        <a class="fw-semibold text-decoration-none small" href="<?= e((string) $leader['source']) ?>" target="_blank" rel="noopener">
                            Xem ảnh gốc <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 class="h4 fw-bold mb-4 text-center">Trưởng Các Tiểu Ban</h3>
        <div class="row g-3 mb-5 justify-content-center">
            <?php foreach ($trung_ban as $leader): ?>
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <article class="mini-card h-100 text-center">
                        <div class="mini-card-icon mx-auto" style="width: 40px; height: 40px; font-size: 1rem;"><i class="bi bi-person-fill"></i></div>
                        <h4 class="h6 fw-bold mb-1" style="font-size: 0.9rem;"><?= e((string) $leader['name']) ?></h4>
                        <p class="text-secondary small mb-3" style="font-size: 0.75rem;"><?= e((string) $leader['role']) ?></p>
                        <a class="fw-semibold text-decoration-none small" style="font-size: 0.75rem;" href="<?= e((string) $leader['source']) ?>" target="_blank" rel="noopener">
                            Ảnh nguồn <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($clubTeams as $team): ?>
                <div class="col-md-6 col-lg-3">
                    <article class="mini-card h-100">
                        <div class="mini-card-icon"><i class="bi <?= e((string) $team['icon']) ?>"></i></div>
                        <h3 class="h5 fw-bold mb-2"><?= e((string) $team['title']) ?></h3>
                        <p class="text-secondary text-flow mb-0"><?= e((string) $team['copy']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row align-items-end gy-3 mb-5">
            <div class="col-lg-7">
                <p class="section-eyebrow">Đối tác & doanh nghiệp</p>
                <h2 class="display-6 fw-bold mb-0">Đơn vị liên quan trong hành trình CIT</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-secondary text-flow text-flow-lg mb-0">
                    
                </p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($clubPartners as $partner): ?>
                <div class="col-md-6 col-lg-3">
                    <article class="mini-card h-100">
                        <div class="mini-card-icon"><i class="bi <?= e((string) $partner['icon']) ?>"></i></div>
                        <h3 class="h5 fw-bold mb-2"><?= e((string) $partner['title']) ?></h3>
                        <p class="text-secondary text-flow mb-0"><?= e((string) $partner['copy']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space-sm">
    <div class="container">
        <div class="cta-banner">
            <div class="row align-items-center g-4 cta-content">
                <div class="col-lg-8">
                    <span class="cta-badge"><i class="bi bi-stars"></i> Gia nhập CIT</span>
                    <h2 class="h2 fw-bold text-white mb-2">Muốn trở thành một phần của cộng đồng?</h2>
                    <p class="cta-copy cta-copy-lg text-narrow mb-0">
                        Bắt đầu bằng đơn ứng tuyển thành viên và cho CIT biết bạn muốn học hỏi, đóng góp điều gì.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="recruitment.php" class="btn btn-club btn-lg px-5 py-3">
                        <i class="bi bi-person-plus-fill me-2"></i>Ứng tuyển ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php page_cache_end(); ?>
