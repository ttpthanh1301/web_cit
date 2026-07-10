<?php
$pageTitle = $pageTitle ?? APP_NAME;
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$pageScripts = $pageScripts ?? [];
$pageCss = $pageCss ?? [];
$bodyClass = $bodyClass ?? '';
$preloadImages = $preloadImages ?? [];
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
    <meta name="description" content="Câu lạc bộ Công nghệ Thông tin CIT - kết nối, học hỏi và sáng tạo cùng sinh viên đam mê công nghệ.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    <?php foreach ($preloadImages as $image): ?>
        <link rel="preload" as="image" href="<?= e((string) $image) ?>" fetchpriority="high">
    <?php endforeach; ?>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <?php foreach ($pageCss as $css): ?>
        <link rel="stylesheet" href="<?= e((string) $css) ?>">
    <?php endforeach; ?>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top club-navbar" aria-label="Điều hướng chính">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
            <img src="assets/images/cit/logoclb.png" alt="Logo CIT Club" width="42" height="42" decoding="async">
            <span>CIT Club</span>
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
                    <a class="nav-link <?= $currentPage === 'recruitment.php' ? 'active' : '' ?>" href="recruitment.php">Tuyển thành viên</a>
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
