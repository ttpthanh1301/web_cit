<?php
declare(strict_types=1);

// Giữ mỗi PHP worker trong giới hạn an toàn cho hosting 512 MB RAM.
ini_set('memory_limit', '128M');
ini_set('max_execution_time', '60');
ini_set('default_socket_timeout', '10');
ini_set('log_errors', '1');
ini_set('display_errors', (getenv('APP_ENV') ?: 'production') === 'local' ? '1' : '0');

date_default_timezone_set('Asia/Ho_Chi_Minh');

$envFile = __DIR__ . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key !== '' && getenv($key) === false) {
            $value = trim($value, "\"'");
            putenv($key . '=' . $value);
        }
    }
}

const APP_NAME = 'CLB Công nghệ CIT';
const ALLOWED_FIELD_TYPES = ['text', 'email', 'phone', 'textarea', 'dropdown', 'radio', 'checkbox'];
const ALLOWED_SUBMISSION_STATUSES = ['pending', 'approved', 'rejected'];

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/session.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    if (preg_match('/[\r\n]/', $path)) {
        $path = 'index.php';
    }

    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    ensure_session_started();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function ini_size_to_bytes(string $value): int
{
    $value = trim($value);
    if ($value === '') {
        return 0;
    }

    $number = (float) $value;
    return match (strtolower(substr($value, -1))) {
        'g' => (int) ($number * 1024 * 1024 * 1024),
        'm' => (int) ($number * 1024 * 1024),
        'k' => (int) ($number * 1024),
        default => (int) $number,
    };
}

function verify_csrf(): void
{
    ensure_session_started();
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';

    if ($token === '') {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postLimit = ini_size_to_bytes((string) ini_get('post_max_size'));
        if ($contentLength > 0 && $postLimit > 0 && $contentLength > $postLimit) {
            http_response_code(413);
            exit('Tổng dung lượng tải lên vượt giới hạn máy chủ. Vui lòng giảm kích thước hoặc tải ảnh theo từng nhóm nhỏ.');
        }
    }

    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
    }
}

function set_flash(string $type, string $message): void
{
    ensure_session_started();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    ensure_session_started();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}
