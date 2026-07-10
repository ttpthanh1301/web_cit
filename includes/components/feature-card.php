<?php
declare(strict_types=1);
?>
<article class="feature-card">
    <div class="feature-number"><?= e((string) $feature['number']) ?></div>
    <div class="feature-icon-wrap">
        <i class="bi <?= e((string) $feature['icon']) ?>"></i>
    </div>
    <h3 class="h5 fw-bold mb-2"><?= e((string) $feature['title']) ?></h3>
    <p class="text-secondary text-flow mb-0"><?= e((string) $feature['copy']) ?></p>
</article>
