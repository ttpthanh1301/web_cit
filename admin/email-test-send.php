<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

if (!is_post()) {
    email_json_response(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
}
verify_csrf();

$recipient = trim(isset($_POST['recipient']) && is_string($_POST['recipient']) ? $_POST['recipient'] : '');
$senderName = trim(isset($_POST['sender_name']) && is_string($_POST['sender_name']) ? $_POST['sender_name'] : '');
$replyTo = trim(isset($_POST['reply_to']) && is_string($_POST['reply_to']) ? $_POST['reply_to'] : '');
$subjectTemplate = trim(isset($_POST['subject']) && is_string($_POST['subject']) ? $_POST['subject'] : '');
$bodyTemplate = sanitize_recruitment_email_html(isset($_POST['body']) && is_string($_POST['body']) ? $_POST['body'] : '');

if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    email_json_response(['success' => false, 'message' => 'Email nhận thử không hợp lệ.'], 422);
}
if ($senderName === '' || $subjectTemplate === '' || $bodyTemplate === '') {
    email_json_response(['success' => false, 'message' => 'Hãy nhập đủ tên người gửi, tiêu đề và nội dung.'], 422);
}
if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
    email_json_response(['success' => false, 'message' => 'Địa chỉ Reply-To không hợp lệ.'], 422);
}

$sample = [
    'name' => 'Nguyễn Minh Anh',
    'email' => $recipient,
    'fields' => ['ho_ten' => 'Nguyễn Minh Anh', 'email' => $recipient, 'lop_nganh' => 'K58 Công nghệ thông tin'],
    'values' => [
        'ho_ten' => 'Nguyễn Minh Anh',
        'email' => $recipient,
        'ma_don' => 'CIT-000123',
        'ngay_nop' => date('d/m/Y H:i'),
        'ten_clb' => APP_NAME,
    ],
];
$result = send_recruitment_email([
    'recipient_email' => $recipient,
    'recipient_name' => 'Nguyễn Minh Anh',
    'sender_name' => $senderName,
    'reply_to' => $replyTo,
    'subject' => render_recruitment_email_template($subjectTemplate, $sample, false),
    'body_html' => render_recruitment_email_template($bodyTemplate, $sample, true),
]);

email_json_response([
    'success' => !empty($result['success']),
    'message' => !empty($result['success']) ? 'Email thử đã được gửi.' : ($result['error'] ?? 'Không thể gửi email thử.'),
], !empty($result['success']) ? 200 : 422);
