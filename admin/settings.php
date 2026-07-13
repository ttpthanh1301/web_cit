<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/editable.php';
require_once __DIR__ . '/../includes/page-cache.php';

// Hàm nén và upload ảnh chuyển sang WebP (giúp tiết kiệm SSD 2GB)
function upload_and_compress_image(array $file, string $targetFolder): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $mimeType = is_array($imageInfo) ? ($imageInfo['mime'] ?? '') : '';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!is_array($imageInfo) || !in_array($mimeType, $allowedTypes, true)) {
        throw new Exception('Chỉ chấp nhận ảnh dạng JPEG, PNG, WEBP.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Ảnh không được vượt quá 5MB.');
    }

    $sourceWidth = (int) ($imageInfo[0] ?? 0);
    $sourceHeight = (int) ($imageInfo[1] ?? 0);
    if ($sourceWidth < 1 || $sourceHeight < 1 || $sourceWidth * $sourceHeight > 12000000) {
        throw new Exception('Ảnh không hợp lệ hoặc vượt quá 12 megapixel.');
    }

    $filename = uniqid('img_', true) . '.webp';
    $targetPath = $targetFolder . '/' . $filename;

    if (!is_dir($targetFolder)) {
        mkdir($targetFolder, 0755, true);
    }

    $success = false;
    if (extension_loaded('gd') && function_exists('imagewebp')) {
        $loader = match ($mimeType) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
            default => '',
        };
        $image = $loader !== '' && function_exists($loader)
            ? @$loader($file['tmp_name'])
            : false;

        if ($image !== false) {
            $scale = min(1, 1920 / $sourceWidth, 1440 / $sourceHeight);
            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
            $outputImage = $image;

            if ($targetWidth !== $sourceWidth || $targetHeight !== $sourceHeight) {
                $outputImage = imagecreatetruecolor($targetWidth, $targetHeight);
                if ($outputImage === false) {
                    imagedestroy($image);
                    throw new Exception('Không đủ bộ nhớ để xử lý ảnh.');
                }
                imagealphablending($outputImage, false);
                imagesavealpha($outputImage, true);
                $transparent = imagecolorallocatealpha($outputImage, 0, 0, 0, 127);
                imagefilledrectangle($outputImage, 0, 0, $targetWidth, $targetHeight, $transparent);
                imagecopyresampled(
                    $outputImage,
                    $image,
                    0,
                    0,
                    0,
                    0,
                    $targetWidth,
                    $targetHeight,
                    $sourceWidth,
                    $sourceHeight
                );
            }

            $success = imagewebp($outputImage, $targetPath, 78);
            if ($outputImage !== $image) {
                imagedestroy($outputImage);
            }
            imagedestroy($image);
        }
    }

    if (!$success) {
        // Fallback nếu máy chủ không hỗ trợ thư viện GD
        if (is_file($targetPath)) {
            @unlink($targetPath);
        }
        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'webp',
        };
        $filename = uniqid('img_', true) . '.' . $ext;
        $targetPath = $targetFolder . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $success = true;
        }
    }

    return $success ? 'uploads/' . $filename : null;
}

function remove_replaced_upload(?string $oldPath, string $newPath): void
{
    if (!is_string($oldPath) || !str_starts_with($oldPath, 'uploads/') || $oldPath === $newPath) {
        return;
    }

    $oldFile = __DIR__ . '/../uploads/' . basename($oldPath);
    if (is_file($oldFile)) {
        @unlink($oldFile);
    }
}

$errors = [];
$successMessage = '';

if (is_post()) {
    verify_csrf();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'update_sections') {
        // Cập nhật sắp xếp & ẩn hiện các Section
        $orders = isset($_POST['orders']) && is_array($_POST['orders']) ? $_POST['orders'] : [];
        $visibles = isset($_POST['visibles']) && is_array($_POST['visibles']) ? $_POST['visibles'] : [];

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE `sections` SET `sort_order` = :sort_order, `is_visible` = :is_visible WHERE `section_key` = :section_key");
            foreach ($orders as $key => $orderVal) {
                $orderNum = (int)$orderVal;
                $isVisible = isset($visibles[$key]) ? 1 : 0;
                $stmt->execute([
                    'sort_order' => $orderNum,
                    'is_visible' => $isVisible,
                    'section_key' => $key
                ]);
            }
            $pdo->commit();

            // Xóa cache
            @unlink(__DIR__ . '/../cache/sections.php');
            page_cache_clear();

            set_flash('success', 'Đã lưu cấu trúc sắp xếp các Section.');
            redirect('settings.php?tab=sections');
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Lỗi lưu dữ liệu: ' . $e->getMessage();
        }
    } elseif ($action === 'update_theme_colors') {
        // Cập nhật Màu sắc
        $primary = isset($_POST['theme_primary_color']) && is_string($_POST['theme_primary_color']) ? trim($_POST['theme_primary_color']) : '#3b82f6';
        $secondary = isset($_POST['theme_secondary_color']) && is_string($_POST['theme_secondary_color']) ? trim($_POST['theme_secondary_color']) : '#06b6d4';
        $accent = isset($_POST['theme_accent_color']) && is_string($_POST['theme_accent_color']) ? trim($_POST['theme_accent_color']) : '#f97316';

        // Validate hex color format
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primary) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $secondary) || !preg_match('/^#[0-9A-Fa-f]{6}$/', $accent)) {
            $errors[] = 'Định dạng mã màu Hex không hợp lệ (cần dạng #RRGGBB).';
        }

        if (!$errors) {
            try {
                $stmt = $pdo->prepare("INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `content_value` = VALUES(`content_value`)");
                $stmt->execute(['theme_primary_color', $primary]);
                $stmt->execute(['theme_secondary_color', $secondary]);
                $stmt->execute(['theme_accent_color', $accent]);

                // Xóa cache
                editable_cache_clear();
                page_cache_clear();

                set_flash('success', 'Đã lưu màu sắc giao diện mới.');
                redirect('settings.php?tab=colors');
            } catch (PDOException $e) {
                $errors[] = 'Lỗi lưu dữ liệu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_ctas') {
        // Cập nhật liên kết / nhãn nút bấm
        $ctaText = isset($_POST['hero_cta_text']) && is_string($_POST['hero_cta_text']) ? trim($_POST['hero_cta_text']) : '';
        $ctaUrl = isset($_POST['hero_cta_url']) && is_string($_POST['hero_cta_url']) ? trim($_POST['hero_cta_url']) : '';
        $exploreText = isset($_POST['hero_explore_text']) && is_string($_POST['hero_explore_text']) ? trim($_POST['hero_explore_text']) : '';
        $exploreUrl = isset($_POST['hero_explore_url']) && is_string($_POST['hero_explore_url']) ? trim($_POST['hero_explore_url']) : '';

        if ($ctaText === '' || $ctaUrl === '' || $exploreText === '' || $exploreUrl === '') {
            $errors[] = 'Vui lòng điền đầy đủ thông tin các nút bấm.';
        }

        if (!$errors) {
            try {
                $stmt = $pdo->prepare("INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `content_value` = VALUES(`content_value`)");
                $stmt->execute(['hero_cta_text', $ctaText]);
                $stmt->execute(['hero_cta_url', $ctaUrl]);
                $stmt->execute(['hero_explore_text', $exploreText]);
                $stmt->execute(['hero_explore_url', $exploreUrl]);

                // Xóa cache
                editable_cache_clear();
                page_cache_clear();

                set_flash('success', 'Đã cập nhật các nút kêu gọi hành động (CTA).');
                redirect('settings.php?tab=ctas');
            } catch (PDOException $e) {
                $errors[] = 'Lỗi lưu dữ liệu: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'upload_images') {
        // Tải ảnh Logo hoặc Hero BG lên
        $targetFolder = __DIR__ . '/../uploads';

        try {
            $uploaded = false;
            $stmt = $pdo->prepare("INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `content_value` = VALUES(`content_value`)");

            $existingImages = editable_contents();
            if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $logoPath = upload_and_compress_image($_FILES['logo_file'], $targetFolder);
                if ($logoPath) {
                    $stmt->execute(['site_logo', $logoPath]);
                    remove_replaced_upload($existingImages['site_logo'] ?? null, $logoPath);
                    $uploaded = true;
                }
            }

            if (isset($_FILES['hero_file']) && $_FILES['hero_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $heroPath = upload_and_compress_image($_FILES['hero_file'], $targetFolder);
                if ($heroPath) {
                    $stmt->execute(['hero_bg', $heroPath]);
                    remove_replaced_upload($existingImages['hero_bg'] ?? null, $heroPath);
                    $uploaded = true;
                }
            }

            if ($uploaded) {
                // Xóa cache
                editable_cache_clear();
                page_cache_clear();

                set_flash('success', 'Tải ảnh lên và đồng bộ dữ liệu thành công.');
                redirect('settings.php?tab=images');
            } else {
                $errors[] = 'Vui lòng chọn ít nhất một tệp tin ảnh hợp lệ.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Lỗi tải ảnh: ' . $e->getMessage();
        }
    }
}

// Lấy thông tin thiết lập hiện có
$sections = $pdo->query("SELECT * FROM `sections` ORDER BY `sort_order` ASC, `id` ASC")->fetchAll(PDO::FETCH_ASSOC);

$contents = editable_contents();
$primaryColor = $contents['theme_primary_color'] ?? '#3b82f6';
$secondaryColor = $contents['theme_secondary_color'] ?? '#06b6d4';
$accentColor = $contents['theme_accent_color'] ?? '#f97316';

$logoPath = $contents['site_logo'] ?? 'assets/images/cit/logoclb.png';
$heroBgPath = $contents['hero_bg'] ?? 'assets/images/cit/cit-cover.webp';

$heroCtaText = $contents['hero_cta_text'] ?? 'Xem tuyển thành viên';
$heroCtaUrl = $contents['hero_cta_url'] ?? 'recruitment.php';
$heroExploreText = $contents['hero_explore_text'] ?? 'Khám phá fanpage';
$heroExploreUrl = $contents['hero_explore_url'] ?? 'https://www.facebook.com/clbcongnghe.cit';

$currentTab = $_GET['tab'] ?? 'sections';
$pageTitle = 'Cấu hình giao diện';
require_once __DIR__ . '/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 fw-bold mb-0">Cấu hình Giao diện</h1>
    <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Xem website công khai
    </a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $currentTab === 'sections' ? 'active' : '' ?>" href="?tab=sections">
                    <i class="bi bi-grid-3x3-gap me-1"></i>Sắp xếp Section
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentTab === 'colors' ? 'active' : '' ?>" href="?tab=colors">
                    <i class="bi bi-palette me-1"></i>Màu sắc chủ đạo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentTab === 'images' ? 'active' : '' ?>" href="?tab=images">
                    <i class="bi bi-images me-1"></i>Logo & Banner
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentTab === 'ctas' ? 'active' : '' ?>" href="?tab=ctas">
                    <i class="bi bi-link-45deg me-1"></i>Nút CTA đầu trang
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content">
            <!-- TAB 1: SECTIONS -->
            <div class="tab-pane fade <?= $currentTab === 'sections' ? 'show active' : '' ?>" role="tabpanel">
                <p class="text-secondary small mb-4">Thay đổi thứ tự hiển thị hoặc ẩn/hiện các khối nội dung trên Trang chủ mà không ảnh hưởng đến dữ liệu nguồn.</p>
                <form method="post" action="settings.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_sections">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Thứ tự</th>
                                    <th>Khối Giao diện (Section)</th>
                                    <th style="width: 120px;" class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sections as $sec): ?>
                                    <tr>
                                        <td>
                                            <input type="number" 
                                                   name="orders[<?= e($sec['section_key']) ?>]" 
                                                   value="<?= (int)$sec['sort_order'] ?>" 
                                                   class="form-control form-control-sm text-center" 
                                                   min="1" 
                                                   max="50" 
                                                   required>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block"><?= e($sec['section_name']) ?></strong>
                                            <span class="text-secondary small">Key: <code><?= e($sec['section_key']) ?></code></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="visibles[<?= e($sec['section_key']) ?>]" 
                                                       id="visible_<?= e($sec['section_key']) ?>" 
                                                       value="1" 
                                                       <?= (int)$sec['is_visible'] === 1 ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary px-4 mt-3">Lưu thay đổi thứ tự</button>
                </form>
            </div>

            <!-- TAB 2: THEME COLORS -->
            <div class="tab-pane fade <?= $currentTab === 'colors' ? 'show active' : '' ?>" role="tabpanel">
                <p class="text-secondary small mb-4">Điều chỉnh hệ thống màu sắc chủ đạo của website nhanh chóng qua Color Picker.</p>
                <form method="post" action="settings.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_theme_colors">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Màu chủ đạo (Primary Color)</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="theme_primary_color" value="<?= e($primaryColor) ?>" class="form-control form-control-color" style="width: 60px; height: 40px;">
                                <input type="text" value="<?= e($primaryColor) ?>" class="form-control text-uppercase" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Màu phụ (Secondary Color)</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="theme_secondary_color" value="<?= e($secondaryColor) ?>" class="form-control form-control-color" style="width: 60px; height: 40px;">
                                <input type="text" value="<?= e($secondaryColor) ?>" class="form-control text-uppercase" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Màu điểm nhấn (Accent Color)</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="theme_accent_color" value="<?= e($accentColor) ?>" class="form-control form-control-color" style="width: 60px; height: 40px;">
                                <input type="text" value="<?= e($accentColor) ?>" class="form-control text-uppercase" readonly>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4 mt-4">Lưu cấu hình màu sắc</button>
                </form>
            </div>

            <!-- TAB 3: LOGO & BANNER IMAGES -->
            <div class="tab-pane fade <?= $currentTab === 'images' ? 'show active' : '' ?>" role="tabpanel">
                <p class="text-secondary small mb-4">Tải ảnh Logo hoặc ảnh nền đầu trang (Hero Background) mới. Hệ thống sẽ tự động chuyển đổi sang định dạng <code>.webp</code> nén chất lượng cao để tiết kiệm dung lượng SSD của bạn.</p>
                <form method="post" action="settings.php" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="upload_images">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6 border-end">
                            <h5 class="fw-bold mb-3">Logo câu lạc bộ</h5>
                            <div class="mb-3">
                                <label class="form-label text-secondary small d-block">Ảnh đang sử dụng:</label>
                                <img src="../<?= e($logoPath) ?>" alt="Current Logo" class="img-thumbnail bg-light mb-3" style="max-height: 80px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="logo_file">Chọn ảnh logo mới (.png, .jpg, .webp)</label>
                                <input type="file" name="logo_file" id="logo_file" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">Ảnh nền đầu trang (Hero Banner)</h5>
                            <div class="mb-3">
                                <label class="form-label text-secondary small d-block">Ảnh đang sử dụng:</label>
                                <img src="../<?= e($heroBgPath) ?>" alt="Current Hero Banner" class="img-thumbnail mb-3" style="max-height: 80px; width: 100%; object-fit: cover;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="hero_file">Chọn ảnh banner mới (.png, .jpg, .webp)</label>
                                <input type="file" name="hero_file" id="hero_file" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Tải ảnh lên & Áp dụng</button>
                </form>
            </div>

            <!-- TAB 4: CTA BUTTONS -->
            <div class="tab-pane fade <?= $currentTab === 'ctas' ? 'show active' : '' ?>" role="tabpanel">
                <p class="text-secondary small mb-4">Chỉnh sửa liên kết và nội dung hiển thị của hai nút kêu gọi hành động chính ở phần Banner đầu trang.</p>
                <form method="post" action="settings.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_ctas">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h5 class="fw-bold text-primary mb-3">Nút chính (Primary CTA)</h5>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="hero_cta_text">Nhãn nút (Button Text)</label>
                                <input type="text" name="hero_cta_text" id="hero_cta_text" value="<?= e($heroCtaText) ?>" class="form-control" required maxlength="50">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="hero_cta_url">Đường dẫn liên kết (URL)</label>
                                <input type="text" name="hero_cta_url" id="hero_cta_url" value="<?= e($heroCtaUrl) ?>" class="form-control" required placeholder="recruitment.php hoặc đường dẫn ngoài">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="fw-bold text-success mb-3">Nút phụ (Secondary CTA)</h5>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="hero_explore_text">Nhãn nút (Button Text)</label>
                                <input type="text" name="hero_explore_text" id="hero_explore_text" value="<?= e($heroExploreText) ?>" class="form-control" required maxlength="50">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="hero_explore_url">Đường dẫn liên kết (URL)</label>
                                <input type="text" name="hero_explore_url" id="hero_explore_url" value="<?= e($heroExploreUrl) ?>" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4 mt-4">Lưu liên kết CTA</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[type="color"]').forEach(picker => {
    picker.addEventListener('input', (e) => {
        e.target.nextElementSibling.value = e.target.value;
    });
});
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
