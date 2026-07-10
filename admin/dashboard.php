<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';

// Khởi tạo các biến thống kê
$totalCount = 0;
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;

try {
    // Truy vấn đếm số lượng đơn theo trạng thái
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM form_submissions GROUP BY status");
    $stats = $stmt->fetchAll();
    
    foreach ($stats as $stat) {
        $count = (int)$stat['count'];
        $totalCount += $count;
        if ($stat['status'] === 'pending') {
            $pendingCount = $count;
        } elseif ($stat['status'] === 'approved') {
            $approvedCount = $count;
        } elseif ($stat['status'] === 'rejected') {
            $rejectedCount = $count;
        }
    }
} catch (PDOException $e) {
    error_log("Lỗi tải thống kê dashboard: " . $e->getMessage());
}

$pageTitle = 'Tổng quan';
require_once __DIR__ . '/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase mb-2 text-white-50">Tổng số đơn tuyển</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?= $totalCount ?></span>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase mb-2 text-dark-50">Đơn chờ duyệt</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?= $pendingCount ?></span>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase mb-2 text-white-50">Đơn đã duyệt</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?= $approvedCount ?></span>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-uppercase mb-2 text-white-50">Đơn đã từ chối</h6>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="display-5 fw-bold"><?= $rejectedCount ?></span>
                    <i class="bi bi-x-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0">Hệ thống quản trị CIT Club</h5>
    </div>
    <div class="card-body">
        <p>Chào mừng bạn quay trở lại trang quản trị! Bạn có thể sử dụng thanh điều hướng bên cạnh để quản lý đơn ứng tuyển thành viên hoặc tùy chỉnh cấu hình form đăng ký.</p>
        <div class="d-flex gap-2 mt-4">
            <a href="members.php" class="btn btn-primary"><i class="bi bi-people-fill me-2"></i>Duyệt đơn ứng tuyển</a>
            <a href="form-builder.php" class="btn btn-outline-secondary"><i class="bi bi-gear-fill me-2"></i>Cấu hình Form</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
