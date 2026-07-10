<?php
declare(strict_types=1);

/**
 * Admin Status Badge Component
 * Renders status badges (Success, Warning, Danger) dynamically.
 *
 * @var string $status
 */
$status = $status ?? 'pending';
$class = $class ?? '';
?>
<span class="badge <?= e($class) ?> text-bg-<?= e(status_badge($status)) ?>"><?= e(status_label($status)) ?></span>
