<?php
declare(strict_types=1);

if ($totalPages <= 1) {
    return;
}

$startPage = max(1, $page - 2);
$endPage = min($totalPages, $page + 2);
?>
<nav class="mt-4" aria-label="Phân trang">
    <ul class="pagination flex-wrap mb-0">
        <?php if ($startPage > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= e($baseUrl) ?>?<?= e(query_string(array_merge($filters, ['page' => 1]))) ?>">1</a></li>
            <?php if ($startPage > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
        <?php endif; ?>
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($baseUrl) ?>?<?= e(query_string(array_merge($filters, ['page' => $i]))) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
            <li class="page-item"><a class="page-link" href="<?= e($baseUrl) ?>?<?= e(query_string(array_merge($filters, ['page' => $totalPages]))) ?>"><?= $totalPages ?></a></li>
        <?php endif; ?>
    </ul>
</nav>
