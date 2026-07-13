<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

ensure_session_started();
if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';
$flash = get_flash();
$username = '';
if (!headers_sent()) {
    header('Cache-Control: private, no-store');
}

if (is_post()) {
    verify_csrf();
    $username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    $statement = $pdo->prepare('SELECT id, username, password_hash FROM admin WHERE username = :username LIMIT 1');
    $statement->execute(['username' => $username]);
    $admin = $statement->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        setcookie('cit_admin_session', '1', [
            'expires' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        set_flash('success', 'Đăng nhập thành công.');
        redirect('dashboard.php');
    }

    $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập quản trị | <?= e(APP_NAME) ?></title>
    <link href="<?= e(versioned_asset('../assets/vendor/bootstrap/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(versioned_asset('../assets/css/admin.min.css')) ?>">
</head>
<body class="login-page">
<main class="login-card shadow-lg">
    <div class="text-center mb-4">
        <img src="../assets/images/cit/logoclb.png" alt="Logo CLB Công nghệ CIT" width="72" height="72" class="login-logo" decoding="async">
        <h1 class="h3 fw-bold mt-3">Quản trị CLB Công nghệ CIT</h1>
        <p class="text-secondary mb-0">Đăng nhập để quản lý đơn tuyển thành viên</p>
    </div>
    <?php render_component('flash', ['flash' => $flash]); ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="username">Tên đăng nhập</label>
            <input class="form-control" id="username" name="username" value="<?= e($username) ?>" maxlength="50" autocomplete="username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">Mật khẩu</label>
            <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Đăng nhập</button>
    </form>
    <a class="d-block text-center mt-4" href="../index.php">← Về trang chủ</a>
</main>
</body>
</html>
