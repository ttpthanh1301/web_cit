<?php
$membersPages = ['members.php', 'member-detail.php'];
$emailPages = ['email-settings.php'];
?>
<aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header d-lg-none">
        <h2 class="offcanvas-title h5" id="adminSidebarLabel">Điều hướng</h2>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <nav class="nav nav-pills flex-column gap-2" aria-label="Điều hướng quản trị">
            <a class="nav-link <?= $adminPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Tổng quan</a>
            <a class="nav-link <?= $adminPage === 'settings.php' ? 'active' : '' ?>" href="settings.php">Cấu hình giao diện</a>
            <a class="nav-link <?= $adminPage === 'form-builder.php' ? 'active' : '' ?>" href="form-builder.php">Quản lý form</a>
            <a class="nav-link <?= in_array($adminPage, $membersPages, true) ? 'active' : '' ?>" href="members.php">Danh sách đăng ký</a>
            <a class="nav-link <?= in_array($adminPage, $emailPages, true) ? 'active' : '' ?>" href="email-settings.php">Email tuyển thành viên</a>
            <a class="nav-link" href="../index.php" target="_blank" rel="noopener">Xem website</a>
        </nav>
        <form class="mt-auto pt-4" method="post" action="logout.php">
            <?= csrf_field() ?>
            <button class="btn btn-outline-light w-100" type="submit">Đăng xuất</button>
        </form>
    </div>
</aside>
