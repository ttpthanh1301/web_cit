<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/data/club-content.php';
require_once __DIR__ . '/includes/page-cache.php';

if (empty($_GET) && page_cache_start('activities', 3600)) {
    exit;
}

$pageTitle = 'Hoạt động';
$pageScripts = ['assets/js/navbar.min.js'];
$preloadImages = ['assets/images/cit/albums/aeternum-hackerrank.webp'];

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero-activities">
    <img class="page-hero-img"
         src="assets/images/cit/albums/aeternum-hackerrank.webp"
         alt=""
         width="843"
         height="674"
         fetchpriority="high"
         decoding="async"
         aria-hidden="true">
    <div class="container">
        <div class="page-hero-content">
            <span class="hero-kicker">
                <i class="bi bi-calendar-heart-fill"></i>
                Hoạt động CIT
            </span>
            <h1>Chuyên môn, văn hóa và câu chuyện thành viên</h1>
            <p class="hero-lead mb-0">
                Những hoạt động được chọn lọc từ fanpage công khai, từ AETERNUM đến Collab, Birthday With CIT và Teambuilding CIT. Đây là cốt lõi đời sống CLB mà website muốn phản ánh.
            </p>
        </div>
    </div>
</section>

<?php foreach ($clubActivityGroups as $groupIndex => $group): ?>
    <section class="section-space <?= $groupIndex % 2 === 1 ? 'section-muted' : '' ?>">
        <div class="container">
            <div class="row align-items-end gy-3 mb-5">
                <div class="col-lg-7">
                    <p class="section-eyebrow"><?= e((string) $group['group']) ?></p>
                    <h2 class="display-6 fw-bold mb-0"><?= e((string) $group['group']) ?> tại CIT</h2>
                </div>
                <div class="col-lg-5">
                    <p class="text-secondary text-flow text-flow-lg mb-0"><?= e((string) $group['intro']) ?></p>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($group['items'] as $activity): ?>
                    <?php
                    $activityThumb = (string) ($activity['thumb'] ?? $activity['image']);
                    $activityFull = (string) $activity['image'];
                    $activityWidth = max(480, (int) ($activity['width'] ?? 1000));
                    ?>
                    <div class="col-md-6 <?= count($group['items']) === 1 ? 'col-lg-6' : 'col-lg-4' ?>">
                        <article class="activity-card activity-card-linked">
                            <a class="activity-card-link" href="<?= e((string) $activity['gallery']) ?>" aria-label="Xem album <?= e((string) $activity['title']) ?>">
                                <div class="activity-card-img">
                                    <img src="<?= e($activityThumb) ?>"
                                         srcset="<?= e($activityThumb) ?> 480w, <?= e($activityFull) ?> <?= $activityWidth ?>w"
                                         sizes="(max-width: 767px) 100vw, (max-width: 1199px) 50vw, 33vw"
                                         alt="<?= e((string) $activity['alt']) ?>"
                                         width="480"
                                         height="360"
                                         loading="<?= $groupIndex === 0 ? 'eager' : 'lazy' ?>"
                                         decoding="async">
                                </div>
                                <div class="activity-card-body">
                                    <span class="activity-tag">
                                        <i class="bi <?= e((string) $activity['icon']) ?> me-1"></i><?= e((string) $activity['tag']) ?>
                                    </span>
                                    <h3 class="h5 fw-bold mb-2"><?= e((string) $activity['title']) ?></h3>
                                    <p class="text-secondary text-flow mb-3"><?= e((string) $activity['copy']) ?></p>
                                    <span class="text-primary fw-semibold">
                                        Xem album công khai trên fanpage <i class="bi bi-arrow-right-short"></i>
                                    </span>
                                </div>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endforeach; ?>

<section class="section-space-sm">
    <div class="container">
        <div class="cta-banner">
            <div class="row align-items-center g-4 cta-content">
                <div class="col-lg-8">
                    <span class="cta-badge"><i class="bi bi-facebook"></i> Fanpage CIT</span>
                    <h2 class="h2 fw-bold text-white mb-2">Theo dõi hoạt động mới nhất</h2>
                    <p class="cta-copy cta-copy-lg text-narrow mb-0">
                        Website dùng ảnh local để ổn định trên hosting; fanpage là nơi cập nhật các bài đăng và album mới nhất của CIT.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="https://www.facebook.com/clbcongnghe.cit" target="_blank" rel="noopener" class="btn btn-club btn-lg px-5 py-3">
                        <i class="bi bi-facebook me-2"></i>Mở fanpage
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php page_cache_end(); ?>
