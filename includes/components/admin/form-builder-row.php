<?php
declare(strict_types=1);

/**
 * Admin Form Builder Row Component
 *
 * @var array $field
 * @var int $index
 * @var int $totalFields
 */
?>
<tr>
    <td>
        <div class="btn-group btn-group-sm" aria-label="Sắp xếp <?= e((string) $field['field_label']) ?>">
            <form method="post" action="form-builder.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="move">
                <input type="hidden" name="field_id" value="<?= (int) $field['id'] ?>">
                <input type="hidden" name="direction" value="up">
                <button class="btn btn-outline-secondary" type="submit" <?= $index === 0 ? 'disabled' : '' ?> aria-label="Đưa lên">↑</button>
            </form>
            <form method="post" action="form-builder.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="move">
                <input type="hidden" name="field_id" value="<?= (int) $field['id'] ?>">
                <input type="hidden" name="direction" value="down">
                <button class="btn btn-outline-secondary" type="submit" <?= $index === $totalFields - 1 ? 'disabled' : '' ?> aria-label="Đưa xuống">↓</button>
            </form>
        </div>
    </td>
    <td>
        <strong><?= e((string) $field['field_label']) ?></strong>
        <small class="d-block text-secondary"><?= e((string) $field['field_name']) ?></small>
    </td>
    <td><?= e((string) $field['field_type']) ?></td>
    <td>
        <?= (int) $field['is_required']
            ? '<span class="badge text-bg-danger">Có</span>'
            : '<span class="badge text-bg-secondary">Không</span>' ?>
    </td>
    <td class="text-end">
        <?php render_component('admin/table-actions', [
            'actions' => [
                'edit' => [
                    'url' => 'form-builder.php?edit=' . (int) $field['id'],
                    'label' => 'Sửa'
                ],
                'delete' => [
                    'action' => 'form-builder.php',
                    'field_name' => 'field_id',
                    'field_value' => (int) $field['id'],
                    'confirm' => 'Xóa field này? Các câu trả lời liên quan cũng sẽ bị xóa.'
                ]
            ]
        ]); ?>
    </td>
</tr>
