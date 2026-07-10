<?php
$pageTitle = $pageTitle ?? APP_NAME;
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$pageScripts = $pageScripts ?? [];
$pageCss = $pageCss ?? [];
$bodyClass = $bodyClass ?? '';
$preloadImages = $preloadImages ?? [];
$includeCsrfMeta = $includeCsrfMeta ?? false;
// Cache tĩnh cho trang công khai (không POST, không admin)
$isPublicGet = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
    && strpos($currentPage, 'admin') === false;
if ($isPublicGet && !headers_sent()) {
    header('Cache-Control: public, max-age=300, stale-while-revalidate=600');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Câu lạc bộ Công nghệ CIT - kết nối, học hỏi và sáng tạo cùng sinh viên đam mê công nghệ.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    <?php foreach ($preloadImages as $image): ?>
        <link rel="preload" as="image" href="<?= e((string) $image) ?>" fetchpriority="high">
    <?php endforeach; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/app.min.css">
    <?php
    require_once __DIR__ . '/helpers.php';
    require_once __DIR__ . '/editable.php';
    $contents = editable_contents();
    $primaryColor = $contents['theme_primary_color'] ?? '#3b82f6';
    $secondaryColor = $contents['theme_secondary_color'] ?? '#06b6d4';
    $accentColor = $contents['theme_accent_color'] ?? '#f97316';
    $primaryRgb = hex_to_rgb($primaryColor);
    ?>
    <style>
    :root {
        --club-primary: <?= e($primaryColor) ?>;
        --club-primary-rgb: <?= e($primaryRgb) ?>;
        --club-secondary: <?= e($secondaryColor) ?>;
        --club-accent: <?= e($accentColor) ?>;
    }
    </style>
    <?php foreach ($pageCss as $css): ?>
        <link rel="stylesheet" href="<?= e((string) $css) ?>">
    <?php endforeach; ?>
    <?php if ($includeCsrfMeta): ?>
        <meta name="csrf-token" content="<?= csrf_token() ?>">
    <?php endif; ?>
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top club-navbar" aria-label="Điều hướng chính">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
            <img src="<?= e(get_logo_url()) ?>" alt="Logo CLB Công nghệ CIT" width="42" height="42" decoding="async" class="rounded-circle">
            <span>CLB Công nghệ CIT</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>" href="about.php">Giới thiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'activities.php' ? 'active' : '' ?>" href="activities.php">Hoạt động</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'recruitment.php' ? 'active' : '' ?>" href="recruitment.php">Tuyển thành viên</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>" href="contact.php">Liên hệ</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <button class="btn-theme-toggle" id="theme-toggle" type="button" aria-label="Thay đổi giao diện">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-light btn-sm px-3 fw-semibold" href="admin/login.php">
                        <i class="bi bi-shield-lock me-1"></i>Quản trị
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main>
