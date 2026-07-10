<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

$errors = [];
$formData = ['id' => 0, 'field_label' => '', 'field_type' => 'text', 'options' => '', 'is_required' => 0];

function clear_form_cache(): void
{
    @unlink(__DIR__ . '/../cache/form-fields.php');
    require_once __DIR__ . '/../includes/page-cache.php';
    page_cache_clear();
}

if (is_post()) {
    verify_csrf();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
    $fieldId = filter_input(INPUT_POST, 'field_id', FILTER_VALIDATE_INT) ?: 0;

    if (in_array($action, ['create', 'update'], true)) {
        $label = isset($_POST['field_label']) && is_string($_POST['field_label']) ? trim($_POST['field_label']) : '';
        $type = isset($_POST['field_type']) && is_string($_POST['field_type']) ? $_POST['field_type'] : '';
        $rawOptions = isset($_POST['options']) && is_string($_POST['options']) ? $_POST['options'] : '';
        $required = isset($_POST['is_required']) ? 1 : 0;
        $formData = ['id' => $fieldId, 'field_label' => $label, 'field_type' => $type, 'options' => $rawOptions, 'is_required' => $required];

        if ($label === '' || mb_strlen($label) > 150) {
            $errors[] = 'Tên field phải có từ 1 đến 150 ký tự.';
        }
        if (!in_array($type, ALLOWED_FIELD_TYPES, true)) {
            $errors[] = 'Loại field không hợp lệ.';
        }
        $choiceType = in_array($type, ['dropdown', 'radio', 'checkbox'], true);
        $encodedOptions = $choiceType ? encode_field_options($rawOptions) : null;
        if ($choiceType && field_options($encodedOptions) === []) {
            $errors[] = 'Field lựa chọn phải có ít nhất một phương án.';
        }

        if (!$errors) {
            if ($action === 'create') {
                $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM form_fields')->fetchColumn();
                $statement = $pdo->prepare(
                    'INSERT INTO form_fields (field_label, field_name, field_type, options, is_required, sort_order)
                     VALUES (:label, :name, :type, :options, :required, :sort_order)'
                );
                $statement->execute([
                    'label' => $label,
                    'name' => unique_field_name($pdo, $label),
                    'type' => $type,
                    'options' => $encodedOptions,
                    'required' => $required,
                    'sort_order' => $nextOrder,
                ]);
                set_flash('success', 'Đã thêm field mới.');
            } else {
                $statement = $pdo->prepare(
                    'UPDATE form_fields SET field_label = :label, field_type = :type,
                     options = :options, is_required = :required WHERE id = :id'
                );
                $statement->execute(['label' => $label, 'type' => $type, 'options' => $encodedOptions, 'required' => $required, 'id' => $fieldId]);
                set_flash('success', $statement->rowCount() ? 'Đã cập nhật field.' : 'Field không thay đổi hoặc không tồn tại.');
            }
            clear_form_cache();
            redirect('form-builder.php');
        }
    } elseif ($action === 'delete' && $fieldId > 0) {
        $statement = $pdo->prepare('DELETE FROM form_fields WHERE id = :id');
        $statement->execute(['id' => $fieldId]);
        set_flash($statement->rowCount() ? 'success' : 'warning', $statement->rowCount() ? 'Đã xóa field và dữ liệu liên quan.' : 'Không tìm thấy field.');
        clear_form_cache();
        redirect('form-builder.php');
    } elseif ($action === 'move' && $fieldId > 0) {
        $direction = ($_POST['direction'] ?? '') === 'up' ? -1 : 1;
        $ids = array_map('intval', $pdo->query('SELECT id FROM form_fields ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN));
        $index = array_search($fieldId, $ids, true);
        $target = $index === false ? -1 : $index + $direction;
        if ($index !== false && isset($ids[$target])) {
            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
            $pdo->beginTransaction();
            try {
                $update = $pdo->prepare('UPDATE form_fields SET sort_order = :sort_order WHERE id = :id');
                foreach ($ids as $order => $id) {
                    $update->execute(['sort_order' => $order + 1, 'id' => $id]);
                }
                $pdo->commit();
                set_flash('success', 'Đã thay đổi thứ tự field.');
            } catch (Throwable $exception) {
                $pdo->rollBack();
                throw $exception;
            }
        }
        clear_form_cache();
        redirect('form-builder.php');
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
if (!is_post() && $editId > 0) {
    $statement = $pdo->prepare(
        'SELECT id, field_label, field_type, options, is_required
         FROM form_fields WHERE id = :id'
    );
    $statement->execute(['id' => $editId]);
    $editField = $statement->fetch();
    if ($editField) {
        $formData = [
            'id' => (int) $editField['id'],
            'field_label' => $editField['field_label'],
            'field_type' => $editField['field_type'],
            'options' => implode("\n", field_options($editField['options'])),
            'is_required' => (int) $editField['is_required'],
        ];
    }
}

$fields = $pdo->query(
    'SELECT id, field_label, field_name, field_type, options, is_required, sort_order
     FROM form_fields ORDER BY sort_order, id'
)->fetchAll();
$pageTitle = 'Quản lý form';
$adminScripts = ['../assets/js/admin-form-builder.min.js'];
require __DIR__ . '/header.php';
?>
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Form Builder</h1><p class="text-secondary mb-0">Cấu hình các câu hỏi hiển thị trên đơn tuyển thành viên.</p></div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-5 order-xl-2">
        <section class="content-card">
            <h2 class="h5 fw-bold mb-3"><?= $formData['id'] ? 'Sửa field' : 'Thêm field mới' ?></h2>
            <form method="post" action="form-builder.php<?= $formData['id'] ? '?edit=' . (int) $formData['id'] : '' ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= $formData['id'] ? 'update' : 'create' ?>">
                <input type="hidden" name="field_id" value="<?= (int) $formData['id'] ?>">
                <div class="mb-3">
                    <label class="form-label" for="field_label">Tên field</label>
                    <input class="form-control" id="field_label" name="field_label" value="<?= e($formData['field_label']) ?>" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="field_type">Loại field</label>
                    <select class="form-select" id="field_type" name="field_type" required>
                        <?php foreach (['text'=>'Văn bản','email'=>'Email','phone'=>'Số điện thoại','textarea'=>'Đoạn văn','dropdown'=>'Danh sách thả xuống','radio'=>'Chọn một','checkbox'=>'Chọn nhiều'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $formData['field_type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="option-panel mb-3" id="optionPanel">
                    <label class="form-label" for="options">Các lựa chọn</label>
                    <textarea class="form-control" id="options" name="options" rows="5" placeholder="Mỗi lựa chọn trên một dòng"><?= e($formData['options']) ?></textarea>
                    <div class="form-text">Nhập mỗi phương án trên một dòng.</div>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" <?= $formData['is_required'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_required">Bắt buộc trả lời</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><?= $formData['id'] ? 'Lưu thay đổi' : 'Thêm field' ?></button>
                    <?php if ($formData['id']): ?><a class="btn btn-outline-secondary" href="form-builder.php">Hủy</a><?php endif; ?>
                </div>
            </form>
        </section>
    </div>
    <div class="col-xl-7 order-xl-1">
        <section class="content-card">
            <h2 class="h5 fw-bold mb-3">Danh sách field</h2>
            <?php if (!$fields): ?><div class="alert alert-info mb-0">Chưa có field nào.</div><?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Thứ tự</th><th>Field</th><th>Loại</th><th>Bắt buộc</th><th class="text-end">Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($fields as $index => $field): ?>
                            <?php render_component('admin/form-builder-row', [
                                'field' => $field,
                                'index' => $index,
                                'totalFields' => count($fields),
                            ]); ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
