<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
if (!is_post()) { http_response_code(405); exit('Phương thức không được hỗ trợ.'); }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$status = isset($_POST['status']) && is_string($_POST['status']) ? $_POST['status'] : '';
if ($id < 1 || !in_array($status, ALLOWED_SUBMISSION_STATUSES, true)) { http_response_code(422); exit('Dữ liệu không hợp lệ.'); }
$statement = $pdo->prepare('UPDATE form_submissions SET status = :status WHERE id = :id');
$statement->execute(['status' => $status, 'id' => $id]);
set_flash($statement->rowCount() ? 'success' : 'warning', $statement->rowCount() ? 'Đã cập nhật trạng thái đơn.' : 'Đơn không tồn tại hoặc trạng thái không đổi.');
$returnQuery = isset($_POST['return']) && is_string($_POST['return']) ? $_POST['return'] : '';
redirect('member-detail.php?id=' . $id . ($returnQuery !== '' ? '&return=' . urlencode($returnQuery) : ''));
