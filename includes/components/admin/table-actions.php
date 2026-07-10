<?php
declare(strict_types=1);

/**
 * Admin Table Actions Component
 * Renders actions like Detail, Edit, and Delete forms inline or inside tables.
 *
 * @var array $actions List of actions to render
 */
?>
<div class="d-inline-flex gap-1">
    <?php foreach ($actions as $type => $config): ?>
        <?php if ($type === 'delete'): ?>
            <form method="post" action="<?= e((string) $config['action']) ?>" onsubmit="return confirm('<?= e((string) ($config['confirm'] ?? 'Bạn chắc chắn muốn thực hiện thao tác này?')) ?>');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="<?= e((string) ($config['field_name'] ?? 'id')) ?>" value="<?= (int) $config['field_value'] ?>">
                <?php if (isset($config['extra_params']) && is_array($config['extra_params'])): ?>
                    <?php foreach ($config['extra_params'] as $name => $val): ?>
                        <input type="hidden" name="<?= e((string) $name) ?>" value="<?= e((string) $val) ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-danger" type="submit"><?= e((string) ($config['label'] ?? 'Xóa')) ?></button>
            </form>
        <?php else: ?>
            <a class="btn btn-sm <?= $type === 'detail' ? 'btn-outline-primary' : 'btn-outline-secondary' ?>" href="<?= e((string) $config['url']) ?>">
                <?= e((string) ($config['label'] ?? ($type === 'detail' ? 'Chi tiết' : 'Sửa'))) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
