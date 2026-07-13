<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/data/club-content.php';
require_once __DIR__ . '/includes/page-cache.php';
require_once __DIR__ . '/includes/editable.php';

if (empty($_GET) && page_cache_start('about', 3600)) {
    exit;
}

$pageTitle = 'Giới thiệu';
$pageScripts = ['assets/js/navbar.min.js'];
$contents = editable_contents();
$aboutHero = $contents['about_hero_bg'] ?? 'assets/images/cit/cit-cover.webp';
$aboutHeroSmall = $contents['about_hero_bg_small'] ?? $aboutHero;
$aboutHeroWidth = max(1, (int) ($contents['about_hero_bg_width'] ?? 1000));
$aboutHeroHeight = max(1, (int) ($contents['about_hero_bg_height'] ?? 370));
$aboutHeroSmallWidth = max(1, (int) ($contents['about_hero_bg_small_width'] ?? $aboutHeroWidth));
$aboutHeroSrcset = $aboutHeroSmall !== $aboutHero ? $aboutHeroSmall . ' ' . $aboutHeroSmallWidth . 'w, ' . $aboutHero . ' ' . $aboutHeroWidth . 'w' : '';
$preloadImages = [['href' => $aboutHero, 'srcset' => $aboutHeroSrcset, 'sizes' => '100vw']];

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-about">
    <img class="page-hero-img"
         src="<?= e($aboutHeroSmall) ?>"
         <?= $aboutHeroSrcset !== '' ? 'srcset="' . e($aboutHeroSrcset) . '" sizes="100vw"' : '' ?>
         alt=""
         width="<?= $aboutHeroWidth ?>"
         height="<?= $aboutHeroHeight ?>"
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
                        CIT đồng hành cùng sinh viên yêu công nghệ thông qua workshop, seminar, cuộc thi nội bộ, nghiên cứu khoa học và các hoạt động cộng đồng. Qua đó giúp các thành viên tích lũy kinh nghiệm thực tế, phát triển tư duy công nghệ và tự tin mở rộng cơ hội nghề nghiệp.
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

<section class="section-space section-muted position-relative overflow-hidden">
    <div class="container">
        <div class="text-center mx-auto mb-3" style="max-width: 700px;">
            <div class="d-flex justify-content-center">
                <span class="section-eyebrow">Kim chỉ nam hoạt động</span>
            </div>
            <h2 class="display-6 fw-bold text-center mb-3">4 Giá Trị Cốt Lõi Của CIT</h2>
            <div class="values-slogan-pill mx-auto mb-3">
                <span>TRI THỨC • TỬ TẾ • TRÁCH NHIỆM • HỢP TÁC</span>
            </div>
            <p class="text-secondary text-flow mx-auto mb-0" style="max-width: 650px; text-align: center;">
                Phương châm hành động xuyên suốt, định hình văn hóa và dẫn dắt mọi thế hệ thành viên CIT trên hành trình chinh phục công nghệ.
            </p>
        </div>
        <div class="row g-4">
            <?php foreach ($clubValues as $value): ?>
                <div class="col-md-6 col-lg-3">
                    <article class="mini-card core-value-card h-100">
                        <div class="value-card-icon"><i class="bi <?= e((string) $value['icon']) ?>"></i></div>
                        <h3 class="h4 fw-bold mb-2"><?= e((string) $value['title']) ?></h3>
                        <p class="text-secondary text-flow mb-0"><?= e((string) $value['copy']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<section class="section-space overflow-hidden position-relative">
    <div class="container-fluid container-xxl">
        <div class="text-center mx-auto mb-5" style="max-width: 700px;">
            <div class="d-flex justify-content-center">
                <span class="section-eyebrow">Hành trình phát triển</span>
            </div>
            <h2 class="display-6 fw-bold text-center mb-3">Dấu Mốc Lịch Sử CIT</h2>
            <p class="text-secondary text-flow mx-auto mb-0" style="max-width: 650px; text-align: center;">
                Từng bước trưởng thành, bứt phá chuyên môn và khẳng định vị thế cộng đồng công nghệ sinh viên hàng đầu TMU.
            </p>
        </div>

        <!-- Horizontal Roadmap Track (Flows Left to Right) -->
        <div class="horizontal-roadmap-track">
            <div class="horizontal-roadmap-container position-relative">
                <!-- Horizontal SVG Winding Wave Path for Desktop -->
                <svg class="horizontal-roadmap-svg d-none d-lg-block" viewBox="0 0 1400 300" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="h-roadmap-grad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#3b82f6" />
                            <stop offset="50%" stop-color="#8b5cf6" />
                            <stop offset="100%" stop-color="#f97316" />
                        </linearGradient>
                        <filter id="h-glow" x="-10%" y="-10%" width="120%" height="120%">
                            <feGaussianBlur stdDeviation="3" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                    </defs>
                    <path d="M 100 150 
                             C 200 70, 300 230, 400 150 
                             C 500 70, 600 230, 700 150 
                             C 800 70, 900 230, 1000 150 
                             C 1100 70, 1200 230, 1300 150" 
                          fill="none" 
                          stroke="url(#h-roadmap-grad)" 
                          stroke-width="4.5" 
                          stroke-dasharray="8 6"
                          filter="url(#h-glow)" />
                </svg>

                <div class="horizontal-roadmap-grid">
                    <?php foreach ($clubTimeline as $index => $item): 
                        $isOdd = ($index % 2 !== 0);
                        $stepNum = sprintf("%02d", $index + 1);
                        $icon = $item['icon'] ?? 'bi-geo-alt-fill';
                    ?>
                        <div class="h-roadmap-step <?= $isOdd ? 'step-bottom' : 'step-top' ?>">
                            <div class="h-roadmap-card-wrap">
                                <article class="h-roadmap-card mini-card" tabindex="0">
                                    <div class="h-roadmap-summary">
                                        <span class="roadmap-badge mb-2">
                                            <span class="badge-text"><?= e((string) $item['label']) ?></span>
                                        </span>
                                        <span class="h-roadmap-title h6 fw-bold mb-0"><?= e((string) $item['title']) ?></span>
                                    </div>
                                    <p class="h-roadmap-copy text-secondary text-flow small mb-0">
                                        <span><?= e((string) $item['copy']) ?></span>
                                    </p>
                                </article>
                            </div>

                            <div class="h-roadmap-marker">
                                <span class="marker-number"><?= $stepNum ?></span>
                                <div class="marker-icon"><i class="bi <?= e((string) $icon) ?>"></i></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
                    Ban điều hành và các tiểu ban phối hợp vận hành hoạt động học thuật, truyền thông, đối ngoại và sự kiện, tạo nền tảng phát triển bền vững cho CIT.
                </p>
            </div>
        </div>

        <?php
        $bcn = array_slice($clubLeaders, 0, 3);
        $trung_ban = array_slice($clubLeaders, 3);
        ?>

        <h3 class="h4 fw-bold mb-4 text-center">Ban Chủ Nhiệm (Nhiệm kỳ 2026 - 2027)</h3>
        <div class="row g-4 mb-5 justify-content-center align-items-center">
            <?php foreach ($bcn as $leader): 
                $isPresident = ($leader['role'] === 'Chủ nhiệm');
                $colClass = $isPresident ? 'col-md-5 col-lg-4' : 'col-md-3.5 col-lg-3';
                $cardClass = $isPresident ? 'mini-card card-president h-100 text-center' : 'mini-card h-100 text-center';
                $avatarClass = $isPresident ? 'leader-avatar-wrap president-avatar mx-auto mb-3' : 'leader-avatar-wrap mx-auto mb-3';
            ?>
                <div class="<?= $colClass ?>">
                    <article class="<?= $cardClass ?>" style="border-top: 3px solid var(--club-accent);">
                        <?php if (!empty($leader['image'])): ?>
                            <div class="<?= $avatarClass ?>">
                                <img src="<?= e((string) $leader['image']) ?>" alt="<?= e((string) $leader['name']) ?>" class="leader-avatar" loading="lazy" decoding="async">
                            </div>
                        <?php else: ?>
                            <div class="mini-card-icon mx-auto"><i class="bi bi-award-fill"></i></div>
                        <?php endif; ?>
                        <h4 class="<?= $isPresident ? 'h4 fw-bold mb-2' : 'h5 fw-bold mb-1' ?>"><?= e((string) $leader['name']) ?></h4>
                        <p class="text-primary fw-semibold mb-0"><?= e((string) $leader['role']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 class="h4 fw-bold mb-4 text-center">Trưởng Các Tiểu Ban</h3>
        <div class="row g-3 mb-5 justify-content-center">
            <?php foreach ($trung_ban as $leader): ?>
                <div class="col-sm-6 col-md-4 col-5-cols">
                    <article class="mini-card card-sub-leader h-100 text-center">
                        <?php if (!empty($leader['image'])): ?>
                            <div class="leader-avatar-wrap sub-leader mx-auto mb-3">
                                <img src="<?= e((string) $leader['image']) ?>" alt="<?= e((string) $leader['name']) ?>" class="leader-avatar" loading="lazy" decoding="async">
                            </div>
                        <?php else: ?>
                            <div class="mini-card-icon mx-auto" style="width: 40px; height: 40px; font-size: 1rem;"><i class="bi bi-person-fill"></i></div>
                        <?php endif; ?>
                        <h4 class="h6 fw-bold mb-1" style="font-size: 0.9rem;"><?= e((string) $leader['name']) ?></h4>
                        <p class="text-secondary leader-role mb-0"><?= e((string) $leader['role']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($clubTeams as $team): ?>
                <div class="col-md-6 col-lg-4">
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
                    CIT kết nối cùng nhà trường, khoa chuyên môn và doanh nghiệp công nghệ để mở rộng trải nghiệm học tập, nghiên cứu và định hướng nghề nghiệp cho thành viên.
                </p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($clubPartners as $partner): ?>
                <div class="col-md-6 col-lg-3">
                    <article class="mini-card h-100">
                        <?php if (!empty($partner['image'])): ?>
                            <div class="partner-logo-wrap mb-3">
                                <img src="<?= e((string) $partner['image']) ?>" alt="<?= e((string) $partner['title']) ?>" class="partner-logo" loading="lazy" decoding="async">
                            </div>
                        <?php else: ?>
                            <div class="mini-card-icon"><i class="bi <?= e((string) $partner['icon']) ?>"></i></div>
                        <?php endif; ?>
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
