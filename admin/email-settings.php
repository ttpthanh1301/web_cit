<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

$settings = recruitment_email_settings($pdo);
if (is_post()) {
    verify_csrf();
    $senderName = trim(isset($_POST['mail_sender_name']) && is_string($_POST['mail_sender_name']) ? $_POST['mail_sender_name'] : '');
    $replyTo = trim(isset($_POST['mail_reply_to']) && is_string($_POST['mail_reply_to']) ? $_POST['mail_reply_to'] : '');
    $approvedSubject = trim(isset($_POST['mail_approved_subject']) && is_string($_POST['mail_approved_subject']) ? $_POST['mail_approved_subject'] : '');
    $rejectedSubject = trim(isset($_POST['mail_rejected_subject']) && is_string($_POST['mail_rejected_subject']) ? $_POST['mail_rejected_subject'] : '');
    $approvedBody = sanitize_recruitment_email_html(isset($_POST['mail_approved_body']) && is_string($_POST['mail_approved_body']) ? $_POST['mail_approved_body'] : '');
    $rejectedBody = sanitize_recruitment_email_html(isset($_POST['mail_rejected_body']) && is_string($_POST['mail_rejected_body']) ? $_POST['mail_rejected_body'] : '');

    $errors = [];
    if ($senderName === '') {
        $errors[] = 'Tên người gửi không được để trống.';
    }
    if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Địa chỉ Reply-To không hợp lệ.';
    }
    if ($approvedSubject === '' || $approvedBody === '') {
        $errors[] = 'Mẫu thư chấp nhận chưa đầy đủ.';
    }
    if ($rejectedSubject === '' || $rejectedBody === '') {
        $errors[] = 'Mẫu thư từ chối chưa đầy đủ.';
    }

    $settings = [
        'mail_sender_name' => mb_substr($senderName, 0, 150),
        'mail_reply_to' => mb_substr($replyTo, 0, 254),
        'mail_approved_subject' => mb_substr($approvedSubject, 0, 255),
        'mail_approved_body' => $approvedBody,
        'mail_rejected_subject' => mb_substr($rejectedSubject, 0, 255),
        'mail_rejected_body' => $rejectedBody,
    ];

    if ($errors === []) {
        save_recruitment_email_settings($pdo, $settings);
        set_flash('success', 'Đã lưu cấu hình email tuyển thành viên.');
        redirect('email-settings.php');
    }
    set_flash('danger', implode(' ', $errors));
}

$settings['mail_approved_body'] = sanitize_recruitment_email_html((string) $settings['mail_approved_body']);
$settings['mail_rejected_body'] = sanitize_recruitment_email_html((string) $settings['mail_rejected_body']);
$fields = $pdo->query('SELECT field_label, field_name FROM form_fields ORDER BY sort_order, id')->fetchAll();
$smtp = smtp_configuration();
$recentBatches = [];
try {
    $recentBatches = $pdo->query(
        'SELECT id, message_type, selected_count, processed_count, sent_count, failed_count, skipped_count, status, created_at
         FROM email_batches ORDER BY id DESC LIMIT 8'
    )->fetchAll();
} catch (Throwable) {
    set_flash('warning', 'Chưa có bảng lưu email. Hãy chạy migration 20260713_recruitment_email.sql.');
}

$pageTitle = 'Email tuyển thành viên';
$adminScripts = ['../assets/js/admin-mail-settings.min.js', '../assets/js/admin-members-email.min.js'];
require __DIR__ . '/header.php';

$coreVariables = [
    '{{ho_ten}}' => 'Họ và tên',
    '{{email}}' => 'Email',
    '{{ma_don}}' => 'Mã hồ sơ',
    '{{ngay_nop}}' => 'Ngày nộp',
    '{{ten_clb}}' => 'Tên CLB',
];
?>
<div class="email-settings-page">
    <div class="settings-heading">
        <div>
            <span class="settings-kicker">Tuyển thành viên</span>
            <h1>Email theo kết quả</h1>
            <p>Soạn hai mẫu thư và chủ động gửi từ danh sách đăng ký.</p>
        </div>
        <a class="btn btn-outline-primary settings-preview-button" href="members.php"><i class="bi bi-people"></i> Danh sách hồ sơ</a>
    </div>

    <section class="mail-smtp-strip <?= smtp_is_configured() ? 'is-ready' : 'is-missing' ?>" aria-label="Trạng thái SMTP">
        <span class="mail-smtp-icon"><i class="bi <?= smtp_is_configured() ? 'bi-shield-check' : 'bi-exclamation-triangle' ?>"></i></span>
        <div><strong><?= smtp_is_configured() ? 'SMTP sẵn sàng' : 'SMTP chưa hoàn tất' ?></strong><small><?= smtp_is_configured() ? e($smtp['host'] . ':' . $smtp['port'] . ' · ' . $smtp['from_address']) : 'Bổ sung MAIL_HOST và MAIL_FROM_ADDRESS trong file .env.' ?></small></div>
        <code><?= e(strtoupper($smtp['encryption'] ?: 'none')) ?></code>
    </section>

    <form method="post" id="email-settings-form" class="email-settings-workspace">
        <?= csrf_field() ?>
        <section class="mail-sender-panel" aria-labelledby="sender-heading">
            <div><span class="settings-kicker">Người gửi</span><h2 id="sender-heading">Thông tin hiển thị</h2></div>
            <label><span>Tên người gửi</span><input class="form-control" name="mail_sender_name" maxlength="150" required value="<?= e((string) $settings['mail_sender_name']) ?>"></label>
            <label><span>Reply-To</span><input class="form-control" name="mail_reply_to" type="email" maxlength="254" placeholder="contact@example.com" value="<?= e((string) $settings['mail_reply_to']) ?>"></label>
        </section>

        <nav class="nav nav-tabs mail-template-tabs" role="tablist" aria-label="Mẫu email">
            <button class="nav-link active" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-panel" type="button" role="tab" aria-controls="approved-panel" aria-selected="true"><i class="bi bi-check-circle"></i> Thư chấp nhận</button>
            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-panel" type="button" role="tab" aria-controls="rejected-panel" aria-selected="false"><i class="bi bi-x-circle"></i> Thư từ chối</button>
        </nav>

        <div class="tab-content">
            <?php foreach (['approved' => 'Chấp nhận', 'rejected' => 'Từ chối'] as $type => $label): ?>
                <section class="tab-pane fade <?= $type === 'approved' ? 'show active' : '' ?> mail-template-panel" id="<?= $type ?>-panel" role="tabpanel" aria-labelledby="<?= $type ?>-tab" data-template-panel="<?= $type ?>">
                    <div class="mail-editor-column">
                        <label class="mail-subject-field"><span>Tiêu đề email</span><input class="form-control" name="mail_<?= $type ?>_subject" maxlength="255" required value="<?= e((string) $settings['mail_' . $type . '_subject']) ?>" data-email-subject></label>
                        <div class="mail-editor-label"><span>Nội dung</span><small>Dùng các biến bên dưới để cá nhân hóa.</small></div>
                        <div class="mail-editor-toolbar" role="toolbar" aria-label="Định dạng nội dung">
                            <button type="button" data-command="bold" title="In đậm" aria-label="In đậm"><i class="bi bi-type-bold"></i></button>
                            <button type="button" data-command="italic" title="In nghiêng" aria-label="In nghiêng"><i class="bi bi-type-italic"></i></button>
                            <button type="button" data-command="underline" title="Gạch chân" aria-label="Gạch chân"><i class="bi bi-type-underline"></i></button>
                            <button type="button" data-command="insertUnorderedList" title="Danh sách" aria-label="Danh sách"><i class="bi bi-list-ul"></i></button>
                            <button type="button" data-command="justifyLeft" title="Căn trái" aria-label="Căn trái"><i class="bi bi-text-left"></i></button>
                            <button type="button" data-command="justifyCenter" title="Căn giữa" aria-label="Căn giữa"><i class="bi bi-text-center"></i></button>
                            <button type="button" data-command="justifyRight" title="Căn phải" aria-label="Căn phải"><i class="bi bi-text-right"></i></button>
                            <button type="button" data-command="justifyFull" title="Căn đều" aria-label="Căn đều"><i class="bi bi-justify"></i></button>
                            <button type="button" data-command="createLink" title="Chèn liên kết" aria-label="Chèn liên kết"><i class="bi bi-link-45deg"></i></button>
                            <button type="button" data-upload-image title="Chèn ảnh" aria-label="Chèn ảnh"><i class="bi bi-image"></i></button>
                            <button type="button" data-command="removeFormat" title="Xóa định dạng" aria-label="Xóa định dạng"><i class="bi bi-eraser"></i></button>
                            <button type="button" data-toggle-html title="Chỉnh sửa HTML" aria-label="Chỉnh sửa HTML"><i class="bi bi-code-slash"></i></button>
                            <input class="d-none" type="file" accept="image/jpeg,image/png,image/webp" data-email-image-input>
                        </div>
                        <div class="mail-rich-editor" contenteditable="true" role="textbox" aria-multiline="true" data-email-editor><?= (string) $settings['mail_' . $type . '_body'] ?></div>
                        <textarea class="mail-html-source d-none" name="mail_<?= $type ?>_body" spellcheck="false" aria-label="Mã HTML mẫu thư <?= e(mb_strtolower($label)) ?>" data-email-body><?= e((string) $settings['mail_' . $type . '_body']) ?></textarea>
                        <div class="mail-html-hint d-none" data-html-hint><i class="bi bi-shield-check"></i> HTML sẽ được lọc an toàn khi lưu. Hỗ trợ tiêu đề, đoạn văn, liên kết, danh sách, ảnh và căn chỉnh.</div>

                        <div class="mail-variable-list" aria-label="Biến mẫu email">
                            <?php foreach ($coreVariables as $token => $tokenLabel): ?><button type="button" data-email-token="<?= e($token) ?>" title="Chèn <?= e($tokenLabel) ?>"><code><?= e($token) ?></code><span><?= e($tokenLabel) ?></span></button><?php endforeach; ?>
                            <?php foreach ($fields as $field): $token = '{{field:' . $field['field_name'] . '}}'; ?><button type="button" data-email-token="<?= e($token) ?>" title="Chèn <?= e($field['field_label']) ?>"><code><?= e($token) ?></code><span><?= e($field['field_label']) ?></span></button><?php endforeach; ?>
                        </div>
                        <div class="mail-upload-status" role="status" aria-live="polite" data-upload-status></div>
                    </div>

                    <aside class="mail-preview-column">
                        <div class="mail-preview-heading"><div><span>Xem trước</span><strong><?= e($label) ?></strong></div><i class="bi bi-envelope-paper"></i></div>
                        <iframe class="mail-preview-frame" sandbox title="Xem trước mẫu thư <?= e(mb_strtolower($label)) ?>" data-email-preview></iframe>
                        <div class="mail-test-row">
                            <label><span>Email nhận thử</span><input class="form-control" type="email" placeholder="you@example.com" data-test-recipient></label>
                            <button class="btn btn-outline-primary" type="button" data-send-test><i class="bi bi-send"></i> Gửi thử</button>
                        </div>
                        <div class="mail-test-status" role="status" aria-live="polite" data-test-status></div>
                    </aside>
                </section>
            <?php endforeach; ?>
        </div>

        <div class="mail-settings-savebar"><span>Mẫu được chụp lại khi tạo đợt gửi, nên lần gửi lại không bị đổi nội dung.</span><button class="btn btn-primary" type="submit"><i class="bi bi-floppy"></i> Lưu cấu hình email</button></div>
    </form>

    <section class="email-history-section">
        <div class="email-history-heading"><div><span class="settings-kicker">Hoạt động gần đây</span><h2>Lịch sử gửi</h2></div><small>Tối đa 8 đợt gần nhất</small></div>
        <?php if (!$recentBatches): ?><div class="email-empty-state"><i class="bi bi-inbox"></i><span>Chưa có đợt gửi email nào.</span></div><?php else: ?>
            <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Đợt</th><th>Loại</th><th>Tiến độ</th><th>Đã gửi</th><th>Lỗi</th><th>Thời gian</th><th></th></tr></thead><tbody>
            <?php foreach ($recentBatches as $batch): ?>
                <tr>
                    <td class="fw-bold">#<?= (int) $batch['id'] ?></td>
                    <td><span class="badge text-bg-<?= $batch['message_type'] === 'approved' ? 'success' : 'danger' ?>"><?= $batch['message_type'] === 'approved' ? 'Đạt' : 'Không đạt' ?></span></td>
                    <td><?= (int) $batch['processed_count'] ?>/<?= (int) $batch['selected_count'] ?></td>
                    <td class="text-success fw-semibold"><?= (int) $batch['sent_count'] ?></td>
                    <td class="text-danger fw-semibold"><?= (int) $batch['failed_count'] ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($batch['created_at']))) ?></td>
                    <td><?php if ($batch['status'] !== 'completed'): ?><button type="button" class="btn btn-sm btn-outline-primary" data-resume-batch="<?= (int) $batch['id'] ?>"><i class="bi bi-play"></i> Tiếp tục</button><?php else: ?><span class="text-secondary small">Hoàn tất</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
<input type="hidden" id="email-csrf-token" value="<?= e(csrf_token()) ?>">
<?php require __DIR__ . '/footer.php'; ?>
