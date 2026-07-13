<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

if (!is_post()) {
    email_json_response(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
}
verify_csrf();
$deliveryId = filter_input(INPUT_POST, 'delivery_id', FILTER_VALIDATE_INT) ?: 0;
if ($deliveryId < 1) {
    email_json_response(['success' => false, 'message' => 'Email cần gửi lại không hợp lệ.'], 422);
}

try {
    $result = process_email_delivery($pdo, $deliveryId, true);
    email_json_response([
        'success' => $result['status'] === 'sent',
        'message' => $result['status'] === 'sent' ? 'Đã gửi lại email thành công.' : ($result['error'] ?? 'Chưa thể gửi lại email.'),
        'delivery' => $result,
    ], $result['status'] === 'sent' ? 200 : 422);
} catch (Throwable $exception) {
    error_log('Retry recruitment email failed: ' . $exception->getMessage());
    email_json_response(['success' => false, 'message' => 'Không thể gửi lại email.'], 500);
}
