<?php
declare(strict_types=1);

/**
 * Admin Filter Form Component
 * Renders submission filters for status, search queries, date ranges.
 *
 * @var array $filters Current active filters
 */
$filters = $filters ?? [];
?>
<form method="get" action="members.php" class="row g-3 align-items-end">
    <div class="col-12 col-lg-4">
        <label class="form-label" for="q">Tìm kiếm</label>
        <input class="form-control" id="q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Tìm trong câu trả lời...">
    </div>
    <div class="col-12 col-sm-4 col-lg-2">
        <label class="form-label" for="status">Trạng thái</label>
        <select class="form-select" id="status" name="status">
            <option value="">Tất cả</option>
            <?php foreach (ALLOWED_SUBMISSION_STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <label class="form-label" for="date_from">Từ ngày</label>
        <input class="form-control" type="date" id="date_from" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
    </div>
    <div class="col-6 col-sm-4 col-lg-2">
        <label class="form-label" for="date_to">Đến ngày</label>
        <input class="form-control" type="date" id="date_to" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
    </div>
    <div class="col-12 col-lg-2 d-flex gap-2">
        <button class="btn btn-primary flex-grow-1" type="submit">Lọc</button>
        <a class="btn btn-outline-secondary" href="members.php">Xóa lọc</a>
    </div>
</form>
