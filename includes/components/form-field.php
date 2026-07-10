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
?>
<div class="col-12">
    <div class="mb-3">
        <label class="form-label" for="<?= e($inputId) ?>">
            <?= e($fieldLabel) ?><?= $isRequired ? ' <span class="text-danger">*</span>' : '' ?>
        </label>

        <?php if ($fieldType === 'textarea'): ?>
            <textarea
                class="form-control<?= $hasValidation ? ' is-invalid' : '' ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($fieldName) ?>"
                rows="5"
                <?= $isRequired ? 'required' : '' ?>><?= e((string) $fieldValue) ?></textarea>
        <?php elseif ($fieldType === 'dropdown'): ?>
            <select
                class="form-select<?= $hasValidation ? ' is-invalid' : '' ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($fieldName) ?>"
                <?= $isRequired ? 'required' : '' ?>>
                <option value="">Chọn một lựa chọn</option>
                <?php foreach ($fieldOptions as $option): ?>
                    <option value="<?= e($option) ?>" <?= (string) $fieldValue === $option ? 'selected' : '' ?>>
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($fieldType === 'radio'): ?>
            <div class="mb-0">
                <?php foreach ($fieldOptions as $index => $option):
                    $optionId = $inputId . '-radio-' . $index;
                ?>
                    <div class="form-check">
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
                    <div class="form-check">
                        <input
                            class="form-check-input<?= $hasValidation ? ' is-invalid' : '' ?>"
                            type="checkbox"
                            id="<?= e($optionId) ?>"
                            name="<?= e($fieldName) ?>[]"
                            value="<?= e($option) ?>"
                            <?= in_array($option, $selectedValues, true) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="<?= e($optionId) ?>">
                            <?= e($option) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <input
                class="form-control<?= $hasValidation ? ' is-invalid' : '' ?>"
                type="<?= $fieldType === 'email' ? 'email' : ($fieldType === 'phone' ? 'tel' : 'text') ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($fieldName) ?>"
                value="<?= e((string) $fieldValue) ?>"
                <?= $isRequired ? 'required' : '' ?>
            >
        <?php endif; ?>

        <?php if ($fieldError): ?>
            <div class="invalid-feedback"><?= e($fieldError) ?></div>
        <?php endif; ?>
    </div>
</div>
