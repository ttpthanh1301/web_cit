<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
if (!is_post()) { http_response_code(405); exit('Phương thức không được hỗ trợ.'); }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($id < 1) { http_response_code(422); exit('ID không hợp lệ.'); }
$statement = $pdo->prepare('DELETE FROM form_submissions WHERE id = :id');
$statement->execute(['id' => $id]);
set_flash($statement->rowCount() ? 'success' : 'warning', $statement->rowCount() ? 'Đã xóa đơn đăng ký.' : 'Không tìm thấy đơn.');
$returnQuery = isset($_POST['return']) && is_string($_POST['return']) ? $_POST['return'] : '';
if (preg_match('/[^a-zA-Z0-9_=&%+.-]/', $returnQuery)) { $returnQuery = ''; }
redirect('members.php' . ($returnQuery !== '' ? '?' . $returnQuery : ''));
