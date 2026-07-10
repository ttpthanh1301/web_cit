<?php
declare(strict_types=1);

function page_cache_file(string $key): string
{
    $safeKey = preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $key) ?: 'page';
    return __DIR__ . '/../cache/pages/' . $safeKey . '.html';
}

function page_cache_start(string $key, int $ttlSeconds): bool
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return false;
    }

    // Bỏ qua cache nếu là Admin đang đăng nhập
    require_once __DIR__ . '/session.php';
    ensure_session_started();
    if (!empty($_SESSION['admin_id'])) {
        return false;
    }

    $file = page_cache_file($key);
    if (is_file($file) && filemtime($file) + $ttlSeconds >= time()) {
        header('X-Page-Cache: HIT');
        readfile($file);
        return true;
    }

    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }

    $GLOBALS['page_cache_file'] = $file;
    ob_start();
    header('X-Page-Cache: MISS');
    return false;
}

function page_cache_end(): void
{
    $file = $GLOBALS['page_cache_file'] ?? null;
    if (!is_string($file) || !ob_get_level()) {
        return;
    }

    $html = ob_get_contents();
    if (is_string($html) && $html !== '') {
        file_put_contents($file, $html, LOCK_EX);
    }
    ob_end_flush();
    unset($GLOBALS['page_cache_file']);
}
