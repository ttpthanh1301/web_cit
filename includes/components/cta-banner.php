<?php
declare(strict_types=1);

$layout = $layout ?? 'horizontal'; // 'horizontal' or 'vertical'
$badgeIcon = $badgeIcon ?? 'bi-megaphone-fill';
$badgeText = $badgeText ?? 'Đang mở đơn';
$title = $title ?? '';
$copy = $copy ?? '';
$btnText = $btnText ?? 'Đăng ký ngay';
$btnUrl = $btnUrl ?? 'recruitment.php';
$btnId = $btnId ?? 'cta-join';
$btnIcon = $btnIcon ?? 'bi-arrow-right-circle';
$isLarge = $isLarge ?? false;
?>
<?php if ($layout === 'vertical'): ?>
    <div class="cta-banner w-100 d-flex flex-column justify-content-center">
        <span class="cta-badge">
            <i class="bi <?= e($badgeIcon) ?>"></i> <?= e($badgeText) ?>
        </span>
        <h3 class="h4 fw-bold text-white mb-2"><?= e($title) ?></h3>
        <p class="cta-copy mb-4"><?= e($copy) ?></p>
        <a href="<?= e($btnUrl) ?>" class="btn btn-club px-4 align-self-start" id="<?= e($btnId) ?>">
            <i class="bi <?= e($btnIcon) ?> me-1"></i><?= e($btnText) ?>
        </a>
    </div>
<?php else: ?>
    <div class="cta-banner">
        <div class="row align-items-center g-4 cta-content">
            <div class="col-lg-8">
                <span class="cta-badge">
                    <i class="bi <?= e($badgeIcon) ?>"></i> <?= e($badgeText) ?>
                </span>
                <h2 class="h2 fw-bold text-white mb-2"><?= e($title) ?></h2>
                <p class="cta-copy <?= $isLarge ? 'cta-copy-lg text-narrow' : '' ?> mb-0"><?= e($copy) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= e($btnUrl) ?>" class="btn btn-club <?= $isLarge ? 'btn-lg px-5 py-3' : '' ?>" id="<?= e($btnId) ?>">
                    <i class="bi <?= e($btnIcon) ?> me-2"></i><?= e($btnText) ?>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
