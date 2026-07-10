<?php
declare(strict_types=1);

// Tối ưu hóa bộ nhớ cho hosting RAM thấp (512MB)
ini_set('memory_limit', '128M');

// Tự động kiểm tra và quay vòng log lỗi định kỳ nếu vượt quá 5MB
$logFile = ini_get('error_log');
if ($logFile && is_file($logFile) && filesize($logFile) > 5 * 1024 * 1024) {
    $lines = file($logFile);
    if (is_array($lines)) {
        $trimmed = array_slice($lines, -1000); // Giữ lại 1000 dòng log cuối cùng
        file_put_contents($logFile, implode('', $trimmed), LOCK_EX);
    }
}

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

function verify_csrf(): void
{
    ensure_session_started();
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';

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
