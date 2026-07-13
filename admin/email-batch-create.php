<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

if (!is_post()) {
    email_json_response(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
}
verify_csrf();

$messageType = isset($_POST['message_type']) && is_string($_POST['message_type']) ? $_POST['message_type'] : '';
$selectionMode = isset($_POST['selection_mode']) && is_string($_POST['selection_mode']) ? $_POST['selection_mode'] : '';
if (!in_array($messageType, RECRUITMENT_EMAIL_TYPES, true) || !in_array($selectionMode, ['page', 'filtered'], true)) {
    email_json_response(['success' => false, 'message' => 'Lựa chọn gửi email không hợp lệ.'], 422);
}
if (!smtp_is_configured()) {
    email_json_response(['success' => false, 'message' => 'SMTP chưa được cấu hình đầy đủ trong file .env.'], 422);
}

$settings = recruitment_email_settings($pdo);
$subjectTemplate = trim((string) $settings['mail_' . $messageType . '_subject']);
$bodyTemplate = sanitize_recruitment_email_html((string) $settings['mail_' . $messageType . '_body']);
$senderName = trim((string) $settings['mail_sender_name']);
$replyTo = trim((string) $settings['mail_reply_to']);
if ($subjectTemplate === '' || $bodyTemplate === '' || $senderName === '') {
    email_json_response(['success' => false, 'message' => 'Mẫu email chưa có đủ tên người gửi, tiêu đề và nội dung.'], 422);
}

$where = '';
$params = [];
$summary = '';
if ($selectionMode === 'filtered') {
    $filters = submission_filters($_POST);
    $where = submission_filter_sql($filters, $params);
    $summary = 'Toàn bộ kết quả lọc: ' . (query_string($filters) ?: 'không có bộ lọc');
} else {
    $decodedIds = json_decode(isset($_POST['ids']) && is_string($_POST['ids']) ? $_POST['ids'] : '[]', true);
    $ids = [];
    foreach (is_array($decodedIds) ? $decodedIds : [] as $candidateId) {
        $id = filter_var($candidateId, FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0) {
            $ids[$id] = $id;
        }
    }
    $ids = array_values($ids);
    if ($ids === [] || count($ids) > 20) {
        email_json_response(['success' => false, 'message' => 'Hãy chọn từ 1 đến 20 hồ sơ trên trang hiện tại.'], 422);
    }
    $where = ' WHERE fs.id IN (' . implode(',', $ids) . ')';
    $summary = count($ids) . ' hồ sơ trên trang hiện tại';
}

try {
    $countStatement = $pdo->prepare('SELECT COUNT(*) FROM form_submissions fs' . $where);
    $countStatement->execute($params);
    $selectedCount = (int) $countStatement->fetchColumn();
    if ($selectedCount < 1) {
        email_json_response(['success' => false, 'message' => 'Không còn hồ sơ nào phù hợp với lựa chọn này.'], 422);
    }

    $pdo->beginTransaction();
    $batchStatement = $pdo->prepare(
        'INSERT INTO email_batches
         (message_type, subject_template, body_template, sender_name, reply_to, selection_summary, selected_count, created_by)
         VALUES (:message_type, :subject_template, :body_template, :sender_name, :reply_to, :selection_summary, :selected_count, :created_by)'
    );
    $batchStatement->execute([
        'message_type' => $messageType,
        'subject_template' => mb_substr($subjectTemplate, 0, 255),
        'body_template' => $bodyTemplate,
        'sender_name' => mb_substr($senderName, 0, 150),
        'reply_to' => $replyTo !== '' ? mb_substr($replyTo, 0, 254) : null,
        'selection_summary' => mb_substr($summary, 0, 255),
        'selected_count' => $selectedCount,
        'created_by' => (int) $_SESSION['admin_id'],
    ]);
    $batchId = (int) $pdo->lastInsertId();

    $insertParams = ['batch_id' => $batchId, 'message_type' => $messageType] + $params;
    $deliveryStatement = $pdo->prepare(
        "INSERT IGNORE INTO email_deliveries (batch_id, submission_id, message_type, status)
         SELECT :batch_id, fs.id, :message_type, 'pending' FROM form_submissions fs" . $where
    );
    $deliveryStatement->execute($insertParams);
    $createdCount = $deliveryStatement->rowCount();
    $duplicateCount = max(0, $selectedCount - $createdCount);

    $updateParams = ['new_status' => $messageType] + $params;
    $statusStatement = $pdo->prepare('UPDATE form_submissions fs SET status = :new_status' . $where);
    $statusStatement->execute($updateParams);

    $duplicateStatement = $pdo->prepare('UPDATE email_batches SET duplicate_count = :duplicate_count WHERE id = :id');
    $duplicateStatement->execute(['duplicate_count' => $duplicateCount, 'id' => $batchId]);
    $pdo->commit();

    $progress = update_email_batch_progress($pdo, $batchId);
    email_json_response([
        'success' => true,
        'message' => $messageType === 'approved' ? 'Đã chuyển hồ sơ sang Đã duyệt.' : 'Đã chuyển hồ sơ sang Từ chối.',
        'batch' => $progress,
        'duplicate_count' => $duplicateCount,
    ]);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Create recruitment email batch failed: ' . $exception->getMessage());
    email_json_response(['success' => false, 'message' => 'Không thể tạo đợt gửi. Hãy kiểm tra migration cơ sở dữ liệu.'], 500);
}
