<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

if (!is_post()) {
    email_json_response(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
}
verify_csrf();
$batchId = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
if ($batchId < 1) {
    email_json_response(['success' => false, 'message' => 'Đợt gửi không hợp lệ.'], 422);
}

try {
    $reset = $pdo->prepare(
        "UPDATE email_deliveries SET status = 'pending'
         WHERE batch_id = :batch_id AND status = 'sending'
           AND last_attempt_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)"
    );
    $reset->execute(['batch_id' => $batchId]);

    $next = $pdo->prepare(
        "SELECT id FROM email_deliveries WHERE batch_id = :batch_id AND status = 'pending' ORDER BY id LIMIT 1"
    );
    $next->execute(['batch_id' => $batchId]);
    $deliveryId = (int) ($next->fetchColumn() ?: 0);
    $delivery = null;
    if ($deliveryId > 0) {
        $delivery = process_email_delivery($pdo, $deliveryId);
    }
    $progress = update_email_batch_progress($pdo, $batchId);

    email_json_response(['success' => true, 'delivery' => $delivery, 'batch' => $progress]);
} catch (Throwable $exception) {
    error_log('Process recruitment email batch failed: ' . $exception->getMessage());
    email_json_response(['success' => false, 'message' => 'Không thể xử lý email tiếp theo.'], 500);
}
