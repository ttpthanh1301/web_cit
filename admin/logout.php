<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

if (!is_post()) {
    http_response_code(405);
    exit('Phương thức không được hỗ trợ.');
}

verify_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
setcookie('cit_admin_session', '', [
    'expires' => time() - 42000,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_destroy();
session_start();
set_flash('success', 'Bạn đã đăng xuất.');
redirect('login.php');
