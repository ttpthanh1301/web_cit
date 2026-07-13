<?php
declare(strict_types=1);

$fieldId = (int) ($field['id'] ?? 0);
$fieldLabel = trim((string) ($field['field_label'] ?? ''));
$fieldType = (string) ($field['field_type'] ?? 'text');
$fieldName = 'fields[' . $fieldId . ']';
$fieldValue = $answers[$fieldId] ?? '';
$fieldOptions = field_options((string) ($field['options'] ?? ''));
$isRequired = (int) ($field['is_required'] ?? 0) === 1;
$fieldError = isset($errors[$fieldId]) ? trim((string) $errors[$fieldId]) : '';
$inputId = 'field-' . $fieldId;
$hasValidation = $fieldError !== '';
$fieldTypeClass = preg_replace('/[^a-z0-9_-]+/i', '', $fieldType) ?: 'text';
$wideTypes = ['textarea', 'radio', 'checkbox'];
$isChoiceType = in_array($fieldType, ['radio', 'checkbox'], true);
$columnClass = in_array($fieldType, $wideTypes, true) ? 'col-12' : 'col-md-6';
$iconMap = [
    'text' => 'bi-pencil-square',
    'email' => 'bi-envelope-at',
    'phone' => 'bi-telephone',
    'textarea' => 'bi-chat-left-text',
    'dropdown' => 'bi-menu-button-wide',
    'radio' => 'bi-record-circle',
    'checkbox' => 'bi-check2-square',
];
$inputType = $fieldType === 'email' ? 'email' : ($fieldType === 'phone' ? 'tel' : 'text');
$inputMode = $fieldType === 'phone' ? 'tel' : null;
$autocomplete = match ($fieldType) {
    'email' => 'email',
    'phone' => 'tel',
    default => str_contains(mb_strtolower($fieldLabel), 'họ') || str_contains(mb_strtolower($fieldLabel), 'tên') ? 'name' : null,
};
$placeholderMap = [
    'text' => 'Nhập ' . mb_strtolower($fieldLabel),
    'email' => 'example@email.com',
    'phone' => 'Nhập số điện thoại',
    'textarea' => 'Chia sẻ ngắn gọn câu trả lời của bạn...',
    'dropdown' => 'Chọn một lựa chọn',
];
$placeholder = $placeholderMap[$fieldType] ?? 'Nhập câu trả lời';
$requiredText = $isRequired ? 'Bắt buộc' : 'Tùy chọn';
?>
<div class="<?= e($columnClass) ?>">
    <div class="recruitment-field recruitment-field-<?= e($fieldTypeClass) ?><?= $hasValidation ? ' has-error' : '' ?>">
        <<?= $isChoiceType ? 'div' : 'label' ?> class="recruitment-field-label" id="<?= e($inputId) ?>-label"<?= $isChoiceType ? '' : ' for="' . e($inputId) . '"' ?>>
            <span class="recruitment-field-icon"><i class="bi <?= e($iconMap[$fieldType] ?? 'bi-pencil-square') ?>"></i></span>
            <span class="recruitment-field-title">
                <span><?= e($fieldLabel) ?><?= $isRequired ? ' <span class="required-mark">*</span>' : '' ?></span>
                <small><?= e($requiredText) ?></small>
            </span>
        </<?= $isChoiceType ? 'div' : 'label' ?>>

        <?php if ($fieldType === 'textarea'): ?>
            <textarea
                class="form-control recruitment-control<?= $hasValidation ? ' is-invalid' : '' ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($fieldName) ?>"
                rows="6"
                placeholder="<?= e($placeholder) ?>"
                <?= $isRequired ? 'required' : '' ?>><?= e((string) $fieldValue) ?></textarea>
        <?php elseif ($fieldType === 'dropdown'): ?>
            <select
                class="form-select recruitment-control<?= $hasValidation ? ' is-invalid' : '' ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($fieldName) ?>"
                <?= $isRequired ? 'required' : '' ?>>
                <option value=""><?= e($placeholder) ?></option>
                <?php foreach ($fieldOptions as $option): ?>
                    <option value="<?= e($option) ?>" <?= (string) $fieldValue === $option ? 'selected' : '' ?>>
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($fieldType === 'radio'): ?>
            <div class="choice-list choice-list-radio" role="radiogroup" aria-labelledby="<?= e($inputId) ?>-label">
                <?php foreach ($fieldOptions as $index => $option):
                    $optionId = $inputId . '-radio-' . $index;
                ?>
                    <div class="form-check choice-card">
                        <input
                            class="form-check-input<?= $hasValidation ? ' is-invalid' : '' ?>"
                            type="radio"
                            id="<?= e($optionId) ?>"
                            name="<?= e($fieldName) ?>"
                            value="<?= e($option) ?>"
                            <?= (string) $fieldValue === $option ? 'checked' : '' ?>
                            <?= $isRequired ? 'required' : '' ?>
                        >
                        <label class="form-check-label" for="<?= e($optionId) ?>">
                            <span class="choice-indicator"></span>
                            <?= e($option) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($fieldType === 'checkbox'): ?>
            <?php
                $selectedValues = is_array($fieldValue) ? $fieldValue : [];
                $selectedValues = array_map('strval', $selectedValues);
            ?>
            <div class="mb-0">
                <?php foreach ($fieldOptions as $index => $option):
                    $optionId = $inputId . '-checkbox-' . $index;
                ?>
                    <div class="form-check choice-card">
                        <input
                            class="form-check-input<?= $hasValidation ? ' is-invalid' : '' ?>"
                            type="checkbox"
                            id="<?= e($optionId) ?>"
                            name="<?= e($fieldName) ?>[]"
                            value="<?= e($option) ?>"
                            <?= in_array($option, $selectedValues, true) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="<?= e($optionId) ?>">
                            <span class="choice-indicator"></span>
                            <?= e($option) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <input
                class="form-control recruitment-control<?= $hasValidation ? ' is-invalid' : '' ?>"
                type="<?= e($inputType) ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($fieldName) ?>"
                value="<?= e((string) $fieldValue) ?>"
                placeholder="<?= e($placeholder) ?>"
                <?= $inputMode ? 'inputmode="' . e($inputMode) . '"' : '' ?>
                <?= $autocomplete ? 'autocomplete="' . e($autocomplete) . '"' : '' ?>
                <?= $isRequired ? 'required' : '' ?>
            >
        <?php endif; ?>

        <?php if ($fieldError): ?>
            <div class="invalid-feedback d-block"><?= e($fieldError) ?></div>
        <?php endif; ?>
    </div>
</div>
