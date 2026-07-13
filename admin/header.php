<?php
$pageTitle = $pageTitle ?? 'Quản trị';
$adminPage = basename($_SERVER['SCRIPT_NAME'] ?? 'dashboard.php');
$adminScripts = $adminScripts ?? [];
$flash = get_flash();
if (!headers_sent()) {
    header('Cache-Control: private, no-store');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> | Quản trị <?= e(APP_NAME) ?></title>
    <link href="<?= e(versioned_asset('../assets/vendor/bootstrap/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(versioned_asset('../assets/css/admin.min.css')) ?>">
</head>
<body class="admin-body">
<nav class="navbar navbar-dark admin-topbar sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand fw-bold" href="dashboard.php">CIT Admin</a>
        <span class="text-white-50 small d-none d-sm-inline">Xin chào, <?= e((string) ($_SESSION['admin_username'] ?? 'admin')) ?></span>
    </div>
</nav>
<div class="admin-layout">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <main class="admin-content">
        <div class="container-fluid py-4">
            <?php render_component('flash', ['flash' => $flash]); ?>
