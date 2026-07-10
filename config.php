<?php
declare(strict_types=1);

// Buffer output để tránh "headers already sent" crash trên shared hosting và nén GZIP.
// PHP built-in server tự phục vụ local dev tốt hơn khi không bọc ob_gzhandler.
$isBuiltInServer = PHP_SAPI === 'cli-server';
if (!$isBuiltInServer && !ob_get_level()) {
    ob_start(extension_loaded('zlib') && !ini_get('zlib.output_compression') ? 'ob_gzhandler' : null);
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

const APP_NAME = 'CIT Club';
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
