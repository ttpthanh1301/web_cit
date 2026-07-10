<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra quyền Admin
ensure_session_started();
if (empty($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit;
}

// 2. Kiểm tra CSRF Token từ HTTP Header
$headers = getallheaders();
// getallheaders() trả về các key có thể viết thường hoặc viết hoa, chuyển đổi sang viết thường để so khớp chính xác
$headers = array_change_key_case($headers, CASE_LOWER);
$csrfToken = $headers['x-csrf-token'] ?? '';

if (empty($csrfToken) || !hash_equals(csrf_token(), $csrfToken)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn hoặc thiếu mã bảo mật CSRF. Vui lòng tải lại trang.']);
    exit;
}

// 3. Đọc dữ liệu JSON payload gửi lên
$input = json_decode(file_get_contents('php://input'), true);
$key = isset($input['key']) ? trim((string)$input['key']) : '';
$value = isset($input['value']) ? trim((string)$input['value']) : '';
$type = isset($input['type']) ? trim((string)$input['type']) : 'text';

if ($key === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ: thiếu key nội dung.']);
    exit;
}

// 4. Sanitize Input phòng chống tấn công XSS
if ($type === 'html') {
    // Chỉ cho phép các tag định dạng văn bản cơ bản
    $safeValue = strip_tags($value, '<b><i><u><strong><em><p><br><a><ul><li><ol>');
} else {
    // Với text thông thường, loại bỏ hoàn toàn các thẻ HTML
    $safeValue = strip_tags($value);
}

try {
    // 5. Cập nhật vào DB. Dùng UPDATE vì hàm get_editable_content đã tự động INSERT khi hiển thị.
    $stmt = $pdo->prepare('UPDATE page_contents SET content_value = ? WHERE content_key = ?');
    $stmt->execute([$safeValue, $key]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật nội dung thành công!',
        'data' => [
            'key' => $key,
            'value' => $safeValue
        ]
    ]);
} catch (PDOException $e) {
    error_log('Lỗi cập nhật page_contents: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu trên máy chủ.']);
}
