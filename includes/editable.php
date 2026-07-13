<?php
declare(strict_types=1);

/**
 * Kiểm tra xem người dùng hiện tại có phải admin hay không
 * 
 * @return bool
 */
function is_admin(): bool
{
    require_once __DIR__ . '/session.php';
    ensure_session_started();
    return !empty($_SESSION['admin_id']);
}

function maybe_admin(): bool
{
    if (!isset($_COOKIE['cit_admin_session']) || $_COOKIE['cit_admin_session'] !== '1') {
        return false;
    }

    return is_admin();
}

function editable_contents(): array
{
    static $contents = null;

    if (is_array($contents)) {
        return $contents;
    }

    $cacheFile = __DIR__ . '/../cache/page-contents.php';
    if (is_file($cacheFile)) {
        $cached = require $cacheFile;
        if (is_array($cached)) {
            $contents = $cached;
            return $contents;
        }
    }

    require_once __DIR__ . '/db.php';
    $stmt = db()->query('SELECT content_key, content_value FROM page_contents');
    $contents = [];
    foreach ($stmt as $row) {
        $contents[(string) $row['content_key']] = (string) $row['content_value'];
    }

    write_php_cache($cacheFile, $contents);

    return $contents;
}

function editable_cache_clear(): void
{
    $cacheFile = __DIR__ . '/../cache/page-contents.php';
    if (is_file($cacheFile)) {
        @unlink($cacheFile);
    }
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
    $contents = editable_contents();
    $content = $contents[$key] ?? $defaultValue;

    if (maybe_admin()) {
        return '<span class="inline-editable-wrapper">' .
            '<span class="inline-editable-content" ' .
                  'id="editable-' . e($key) . '" ' .
                  'contenteditable="true" ' .
                  'data-editable-key="' . e($key) . '" ' .
                  'data-editable-type="' . e($type) . '" ' .
                  '>' .
                $content .
            '</span>' .
            '<button type="button" ' .
                    'onclick="document.getElementById(\'editable-' . e($key) . '\').focus()" ' .
                    'class="inline-editable-trigger" ' .
                    'title="Chỉnh sửa nội dung">' .
                'Sửa' .
            '</button>' .
        '</span>';
    }

    return $content;
}
