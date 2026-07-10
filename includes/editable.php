<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

/**
 * Kiểm tra xem người dùng hiện tại có phải admin hay không
 * 
 * @return bool
 */
function is_admin(): bool
{
    ensure_session_started();
    return !empty($_SESSION['admin_id']);
}

/**
 * Lấy nội dung chỉnh sửa trực tiếp (inline editable) cho admin, hoặc text thường cho người dùng
 * 
 * @param string $key Khóa duy nhất định danh đoạn nội dung
 * @param string $defaultValue Giá trị mặc định nếu chưa có trong DB
 * @param string $type Loại nội dung ('text' hoặc 'html')
 * @return string
 */
function get_editable_content(string $key, string $defaultValue, string $type = 'text'): string
{
    global $pdo;
    
    // Tìm nội dung từ database
    $stmt = $pdo->prepare('SELECT content_value FROM page_contents WHERE content_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    
    if ($row) {
        $content = $row['content_value'];
    } else {
        // Tự động thêm giá trị mặc định vào DB nếu chưa tồn tại
        $stmtInsert = $pdo->prepare('INSERT INTO page_contents (content_key, content_value) VALUES (?, ?)');
        $stmtInsert->execute([$key, $defaultValue]);
        $content = $defaultValue;
    }
    
    // Nếu là admin thì bọc bằng thẻ div/span editable và bút chì
    if (is_admin()) {
        return '<span class="inline-editable-wrapper" style="position: relative; display: inline-flex; align-items: center; gap: 4px;">' .
            '<span class="inline-editable-content" ' .
                  'id="editable-' . e($key) . '" ' .
                  'contenteditable="true" ' .
                  'data-editable-key="' . e($key) . '" ' .
                  'data-editable-type="' . e($type) . '" ' .
                  'style="outline: none; border-bottom: 1px dashed #3b82f6; padding-right: 4px; transition: background-color 0.2s; cursor: text;" ' .
                  'onfocus="this.style.backgroundColor=\'#eff6ff\'" ' .
                  'onblur="this.style.backgroundColor=\'transparent\'">' .
                $content .
            '</span>' .
            '<button type="button" ' .
                    'onclick="document.getElementById(\'editable-' . e($key) . '\').focus()" ' .
                    'style="background: none; border: none; cursor: pointer; opacity: 0.5; font-size: 0.8rem; padding: 0; display: inline-flex; align-items: center;" ' .
                    'title="Chỉnh sửa nội dung">' .
                '✏️' .
            '</button>' .
        '</span>';
    }
    
    // Đối với người dùng bình thường thì trả về text gốc
    return $content;
}
