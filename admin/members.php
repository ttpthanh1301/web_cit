<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

$fields = $pdo->query('SELECT id, field_label, field_type FROM form_fields ORDER BY sort_order, id')->fetchAll();
$filters = submission_filters($_GET);
$params = [];
$where = submission_filter_sql($filters, $params);
$countStatement = $pdo->prepare('SELECT COUNT(*) FROM form_submissions fs' . $where);
$countStatement->execute($params);
$total = (int) $countStatement->fetchColumn();
$perPage = 20;
$totalPages = max(1, (int) ceil($total / $perPage));
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listStatement = $pdo->prepare(
    'SELECT fs.id, fs.submitted_at, fs.status FROM form_submissions fs' . $where .
    ' ORDER BY fs.submitted_at DESC, fs.id DESC LIMIT :limit OFFSET :offset'
);
foreach ($params as $key => $value) {
    $listStatement->bindValue(':' . $key, $value);
}
$listStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStatement->execute();
$submissions = $listStatement->fetchAll();

$valuesBySubmission = [];
if ($submissions) {
    $ids = array_column($submissions, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $valueStatement = $pdo->prepare("SELECT submission_id, field_id, value FROM form_submission_values WHERE submission_id IN ({$placeholders})");
    $valueStatement->execute($ids);
    foreach ($valueStatement as $valueRow) {
        $valuesBySubmission[(int) $valueRow['submission_id']][(int) $valueRow['field_id']] = $valueRow['value'];
    }
}

$filterQuery = query_string($filters);
$emailSettings = recruitment_email_settings($pdo);
$incompleteBatches = [];
try {
    $incompleteBatches = $pdo->query(
        "SELECT id, message_type, selected_count, processed_count, created_at
         FROM email_batches WHERE status <> 'completed' ORDER BY id DESC LIMIT 5"
    )->fetchAll();
} catch (Throwable) {
    // Migration warning is shown in the email settings page.
}
$pageTitle = 'Danh sách đăng ký';
$adminScripts = ['../assets/js/admin-members-email.min.js'];
require __DIR__ . '/header.php';
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div><h1 class="h3 fw-bold mb-1">Danh sách đăng ký</h1><p class="text-secondary mb-0">Tổng cộng <?= number_format($total) ?> đơn phù hợp.</p></div>
    <a class="btn btn-success" href="export-excel.php<?= $filterQuery ? '?' . e($filterQuery) : '' ?>">Xuất Excel</a>
</div>
<section class="content-card mb-4">
    <?php render_component('admin/filter-form', ['filters' => $filters]); ?>
</section>
<?php if ($incompleteBatches): ?>
    <section class="mail-resume-strip mb-4" aria-label="Đợt gửi chưa hoàn tất">
        <div><i class="bi bi-envelope-exclamation"></i><span><strong>Có <?= count($incompleteBatches) ?> đợt gửi đang dở</strong><small>Bạn có thể tiếp tục mà không gửi lại những email đã hoàn tất.</small></span></div>
        <div class="d-flex flex-wrap gap-2"><?php foreach ($incompleteBatches as $batch): ?><button class="btn btn-sm btn-outline-primary" type="button" data-resume-batch="<?= (int) $batch['id'] ?>">Tiếp tục đợt #<?= (int) $batch['id'] ?></button><?php endforeach; ?></div>
    </section>
<?php endif; ?>
<section class="content-card">
    <?php if (!$submissions): ?><div class="alert alert-info mb-0">Không tìm thấy đơn đăng ký phù hợp.</div><?php else: ?>
        <div class="member-mail-actions" id="member-email-bulk"
             data-total="<?= $total ?>"
             data-page-count="<?= count($submissions) ?>"
             data-filters="<?= e(json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
             data-approved-subject="<?= e((string) $emailSettings['mail_approved_subject']) ?>"
             data-rejected-subject="<?= e((string) $emailSettings['mail_rejected_subject']) ?>">
            <input type="hidden" data-csrf value="<?= e(csrf_token()) ?>">
            <div class="member-mail-actions-main">
                <div class="member-mail-selection"><span data-selection-count>0 hồ sơ</span><small>được chọn</small></div>
                <label class="member-mail-result"><span>Kết quả</span><select class="form-select" data-message-type><option value="approved">Đạt (Approved)</option><option value="rejected">Không đạt (Rejected)</option></select></label>
                <button class="btn btn-primary" type="button" data-open-email-confirm disabled><i class="bi bi-send-check"></i> Cập nhật &amp; gửi email</button>
            </div>
            <div class="member-mail-limit-note" role="note">
                <i class="bi bi-layers"></i>
                <span><strong>Giới hạn gửi: tối đa 50 hồ sơ/nhóm.</strong> Nếu chọn nhiều hơn, hệ thống tự chia nhóm; admin gửi lần lượt bằng nút “Gửi nhóm tiếp theo”.</span>
            </div>
            <div class="member-mail-select-all d-none" data-select-all-notice><span>Đã chọn <?= count($submissions) ?> hồ sơ trên trang này.</span><button type="button" data-select-all-filtered>Chọn tất cả <?= number_format($total) ?> kết quả</button></div>
            <?php if (!smtp_is_configured()): ?><div class="member-mail-warning"><i class="bi bi-exclamation-triangle"></i><span>SMTP chưa sẵn sàng. <a href="email-settings.php">Kiểm tra cấu hình email</a> trước khi gửi.</span></div><?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-dynamic align-middle mb-0">
                <thead><tr><th class="selection-column"><input class="form-check-input" type="checkbox" data-select-page aria-label="Chọn tất cả hồ sơ trên trang"></th><th>#</th><th>Ngày nộp</th><th>Trạng thái</th><?php foreach ($fields as $field): ?><th><?= e($field['field_label']) ?></th><?php endforeach; ?><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td class="selection-column"><input class="form-check-input" type="checkbox" value="<?= (int) $submission['id'] ?>" data-submission-checkbox aria-label="Chọn hồ sơ #<?= (int) $submission['id'] ?>"></td><td><?= (int) $submission['id'] ?></td><td><?= e(date('d/m/Y H:i', strtotime($submission['submitted_at']))) ?></td><td data-status-cell><?php render_component('admin/status-badge', ['status' => $submission['status']]); ?></td>
                        <?php foreach ($fields as $field): $display = format_submission_value($valuesBySubmission[(int) $submission['id']][(int) $field['id']] ?? null, $field['field_type']); ?><td><span class="text-truncate-cell" title="<?= e($display) ?>"><?= e($display) ?></span></td><?php endforeach; ?>
                        <td>
                            <?php render_component('admin/table-actions', [
                                'actions' => [
                                    'detail' => [
                                        'url' => 'member-detail.php?id=' . (int) $submission['id'] . '&return=' . urlencode($filterQuery . ($filterQuery ? '&' : '') . 'page=' . $page),
                                        'label' => 'Chi tiết'
                                    ]
                                ]
                            ]); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php render_component('pagination', [
            'baseUrl' => 'members.php',
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
        ]); ?>
    <?php endif; ?>
</section>

<div class="modal fade" id="emailConfirmModal" tabindex="-1" aria-labelledby="emailConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="emailConfirmTitle">Xác nhận gửi email</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div><div class="modal-body"><div class="email-confirm-summary"><i class="bi bi-people"></i><div><strong data-confirm-count></strong><span data-confirm-result></span></div></div><div class="email-confirm-template"><span>Mẫu sẽ sử dụng</span><strong data-confirm-subject></strong></div><p class="small text-secondary mb-2">Trạng thái hồ sơ được cập nhật trước khi gửi. Nếu SMTP lỗi, trạng thái vẫn được giữ và email có thể gửi lại.</p><p class="small text-secondary mb-0"><i class="bi bi-layers"></i> <strong>Giới hạn gửi: tối đa 50 hồ sơ/nhóm.</strong> Các nhóm tiếp theo chỉ được gửi khi admin chủ động xác nhận.</p></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="button" class="btn btn-primary" data-confirm-send><i class="bi bi-send"></i> Xác nhận gửi</button></div></div></div>
</div>

<div class="modal fade" id="emailProgressModal" tabindex="-1" aria-labelledby="emailProgressTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="emailProgressTitle">Đang gửi email</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Ẩn tiến độ"></button></div><div class="modal-body"><div class="email-progress-number"><strong data-progress-label>0/0</strong><span>đã xử lý</span></div><div class="progress" role="progressbar" aria-label="Tiến độ gửi email" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" data-progress-bar style="width:0%"></div></div><div class="email-progress-stats"><span class="is-sent"><i class="bi bi-check-circle"></i> Đã gửi <strong data-progress-sent>0</strong></span><span class="is-failed"><i class="bi bi-x-circle"></i> Lỗi <strong data-progress-failed>0</strong></span><span class="is-skipped"><i class="bi bi-skip-forward"></i> Bỏ qua <strong data-progress-skipped>0</strong></span></div><div class="email-progress-message" data-progress-message>Đang chuẩn bị...</div></div><div class="modal-footer"><a class="btn btn-outline-secondary" href="email-settings.php">Xem lịch sử</a><button type="button" class="btn btn-primary d-none" data-next-batch><i class="bi bi-send"></i> Gửi nhóm tiếp theo</button><button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button></div></div></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
