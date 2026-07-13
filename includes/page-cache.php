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

    if (isset($_COOKIE['cit_admin_session']) && $_COOKIE['cit_admin_session'] === '1') {
        return false;
    }

    $file = page_cache_file($key);
    if (is_file($file) && (int) filemtime($file) + $ttlSeconds >= time()) {
        header('X-Page-Cache: HIT');
        header('Cache-Control: public, max-age=' . min(300, $ttlSeconds) . ', stale-while-revalidate=600');
        readfile($file);
        return true;
    }

    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }

    $lock = fopen($file . '.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
        if (is_resource($lock)) {
            fclose($lock);
        }

        // Một worker khác đang tạo cache: ưu tiên bản cũ để tránh dồn CPU/MySQL.
        if (is_file($file)) {
            header('X-Page-Cache: STALE');
            header('Cache-Control: public, max-age=0, stale-while-revalidate=60');
            readfile($file);
            return true;
        }

        header('X-Page-Cache: BYPASS');
        return false;
    }

    // Cache có thể vừa được worker trước hoàn tất trước khi lấy được lock.
    clearstatcache(true, $file);
    if (is_file($file) && (int) filemtime($file) + $ttlSeconds >= time()) {
        flock($lock, LOCK_UN);
        fclose($lock);
        header('X-Page-Cache: HIT');
        header('Cache-Control: public, max-age=' . min(300, $ttlSeconds) . ', stale-while-revalidate=600');
        readfile($file);
        return true;
    }

    $GLOBALS['page_cache_file'] = $file;
    $GLOBALS['page_cache_lock'] = $lock;
    ob_start();
    header('X-Page-Cache: MISS');
    return false;
}

function page_cache_end(): void
{
    $file = $GLOBALS['page_cache_file'] ?? null;
    $lock = $GLOBALS['page_cache_lock'] ?? null;
    if (!is_string($file) || !ob_get_level()) {
        if (is_resource($lock)) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return;
    }

    $html = ob_get_contents();
    if (is_string($html) && $html !== '') {
        $temporary = tempnam(dirname($file), '.page-');
        if (is_string($temporary)) {
            if (file_put_contents($temporary, $html, LOCK_EX) !== false) {
                @chmod($temporary, 0644);
                @rename($temporary, $file);
            }
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }
    ob_end_flush();
    if (is_resource($lock)) {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    unset($GLOBALS['page_cache_file'], $GLOBALS['page_cache_lock']);
}

function page_cache_clear(?string $prefix = null): void
{
    $directory = __DIR__ . '/../cache/pages';
    if (!is_dir($directory)) {
        return;
    }

    $files = glob($directory . '/*.html') ?: [];
    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }

        if ($prefix === null || str_starts_with(basename($file), $prefix)) {
            @unlink($file);
        }
    }
}
