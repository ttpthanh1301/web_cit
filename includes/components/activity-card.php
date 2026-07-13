<?php
declare(strict_types=1);

$thumb = (string) ($activity['thumb'] ?? $activity['image']);
$full = (string) $activity['image'];
$srcset = $thumb !== $full
    ? $thumb . ' 480w, ' . $full . ' ' . (int) $activity['width'] . 'w'
    : null;
?>
<article class="activity-card">
    <div class="activity-card-img">
        <img<?= asset_attrs([
            'src' => $thumb,
            'alt' => $activity['alt'],
            'width' => $activity['width'],
            'height' => $activity['height'],
            'loading' => $activity['loading'] ?? 'lazy',
            'decoding' => 'async',
            'srcset' => $srcset,
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
