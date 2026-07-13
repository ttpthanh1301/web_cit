<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/editable.php';
require_once __DIR__ . '/../includes/page-cache.php';

/**
 * Lưu ảnh WebP chất lượng cao và tạo thêm biến thể nhỏ cho màn hình di động.
 *
 * @return array{path:string,width:int,height:int,small_path:string,small_width:int,small_height:int}|null
 */
function upload_responsive_image(
    array $file,
    string $targetFolder,
    int $maxWidth = 2560,
    int $maxHeight = 1920,
    bool $createSmall = true
): ?array
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

    if (!is_dir($targetFolder)) {
        if (!mkdir($targetFolder, 0755, true) && !is_dir($targetFolder)) {
            throw new Exception('Không thể tạo thư mục lưu ảnh.');
        }
    }

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
            $baseName = uniqid('img_', true);
            $filename = $baseName . '.webp';
            $smallFilename = $baseName . '-sm.webp';
            $targetPath = $targetFolder . '/' . $filename;
            $smallTargetPath = $targetFolder . '/' . $smallFilename;

            $writeVariant = static function (
                $source,
                int $sourceWidth,
                int $sourceHeight,
                string $path,
                int $limitWidth,
                int $limitHeight,
                int $quality
            ): array {
                $scale = min(1, $limitWidth / $sourceWidth, $limitHeight / $sourceHeight);
                $width = max(1, (int) round($sourceWidth * $scale));
                $height = max(1, (int) round($sourceHeight * $scale));
                $output = $source;

                if ($width !== $sourceWidth || $height !== $sourceHeight) {
                    $output = imagecreatetruecolor($width, $height);
                    if ($output === false) {
                        throw new Exception('Không đủ bộ nhớ để xử lý ảnh.');
                    }
                    imagealphablending($output, false);
                    imagesavealpha($output, true);
                    $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
                    imagefilledrectangle($output, 0, 0, $width, $height, $transparent);
                    imagecopyresampled($output, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
                }

                $saved = imagewebp($output, $path, $quality);
                if ($output !== $source) {
                    imagedestroy($output);
                }
                if (!$saved) {
                    throw new Exception('Không thể nén ảnh WebP.');
                }

                return ['width' => $width, 'height' => $height];
            };

            try {
                $fullSize = $writeVariant($image, $sourceWidth, $sourceHeight, $targetPath, $maxWidth, $maxHeight, 82);
                $smallSize = $fullSize;
                $smallPath = $targetPath;
                $smallPublicPath = 'uploads/' . $filename;

                if ($createSmall && $fullSize['width'] > 960) {
                    $smallSize = $writeVariant($image, $sourceWidth, $sourceHeight, $smallTargetPath, 960, 960, 76);
                    $smallPath = $smallTargetPath;
                    $smallPublicPath = 'uploads/' . $smallFilename;
                }

                imagedestroy($image);

                return [
                    'path' => 'uploads/' . $filename,
                    'width' => $fullSize['width'],
                    'height' => $fullSize['height'],
                    'small_path' => $smallPublicPath,
                    'small_width' => $smallSize['width'],
                    'small_height' => $smallSize['height'],
                ];
            } catch (Throwable $e) {
                imagedestroy($image);
                @unlink($targetPath);
                @unlink($smallTargetPath);
                throw $e;
            }
        }
    }

    // Fallback nhẹ khi hosting không có GD: giữ nguyên tệp hợp lệ.
    $ext = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        default => 'webp',
    };
    $filename = uniqid('img_', true) . '.' . $ext;
    $targetPath = $targetFolder . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Không thể lưu ảnh đã tải lên.');
    }

    return [
        'path' => 'uploads/' . $filename,
        'width' => $sourceWidth,
        'height' => $sourceHeight,
        'small_path' => 'uploads/' . $filename,
        'small_width' => $sourceWidth,
        'small_height' => $sourceHeight,
    ];
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
$isAsyncUpload = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

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
    } elseif ($action === 'update_layout') {
        $contentWidth = isset($_POST['layout_content_width']) && is_string($_POST['layout_content_width'])
            ? $_POST['layout_content_width']
            : '1320px';
        $cardRadius = isset($_POST['layout_card_radius']) && is_string($_POST['layout_card_radius'])
            ? $_POST['layout_card_radius']
            : '16px';
        $sectionSpacing = isset($_POST['layout_section_spacing']) && is_string($_POST['layout_section_spacing'])
            ? $_POST['layout_section_spacing']
            : 'balanced';
        $baseFontSize = isset($_POST['layout_base_font_size']) && is_string($_POST['layout_base_font_size'])
            ? $_POST['layout_base_font_size']
            : '16px';

        $allowedWidths = ['1140px', '1200px', '1320px'];
        $allowedRadii = ['8px', '12px', '16px'];
        $allowedSpacings = ['compact', 'balanced', 'airy'];
        $allowedFontSizes = ['15px', '16px', '17px'];

        if (!in_array($contentWidth, $allowedWidths, true)
            || !in_array($cardRadius, $allowedRadii, true)
            || !in_array($sectionSpacing, $allowedSpacings, true)
            || !in_array($baseFontSize, $allowedFontSizes, true)) {
            $errors[] = 'Cấu hình bố cục không hợp lệ.';
        }

        if (!$errors) {
            try {
                $stmt = $pdo->prepare("INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `content_value` = VALUES(`content_value`)");
                foreach ([
                    'layout_content_width' => $contentWidth,
                    'layout_card_radius' => $cardRadius,
                    'layout_section_spacing' => $sectionSpacing,
                    'layout_base_font_size' => $baseFontSize,
                ] as $key => $value) {
                    $stmt->execute([$key, $value]);
                }

                editable_cache_clear();
                page_cache_clear();
                set_flash('success', 'Đã áp dụng bố cục toàn website.');
                redirect('settings.php?tab=layout');
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
        $targetFolder = __DIR__ . '/../uploads';

        try {
            $uploaded = false;
            $stmt = $pdo->prepare("INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `content_value` = VALUES(`content_value`)");
            $existingImages = editable_contents();

            $imageFields = [
                'logo_file' => ['key' => 'site_logo', 'small_key' => null, 'max_width' => 512, 'max_height' => 512, 'small' => false],
                'hero_file' => ['key' => 'hero_bg', 'small_key' => 'hero_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'hero_slide_2_file' => ['key' => 'hero_slide_2_bg', 'small_key' => 'hero_slide_2_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'hero_slide_3_file' => ['key' => 'hero_slide_3_bg', 'small_key' => 'hero_slide_3_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'hero_slide_4_file' => ['key' => 'hero_slide_4_bg', 'small_key' => 'hero_slide_4_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'hero_slide_5_file' => ['key' => 'hero_slide_5_bg', 'small_key' => 'hero_slide_5_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'about_hero_file' => ['key' => 'about_hero_bg', 'small_key' => 'about_hero_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'activities_hero_file' => ['key' => 'activities_hero_bg', 'small_key' => 'activities_hero_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'recruitment_hero_file' => ['key' => 'recruitment_hero_bg', 'small_key' => 'recruitment_hero_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
                'contact_hero_file' => ['key' => 'contact_hero_bg', 'small_key' => 'contact_hero_bg_small', 'max_width' => 2560, 'max_height' => 1920, 'small' => true],
            ];

            $removableImageKeys = [
                'hero_slide_2_bg', 'hero_slide_3_bg', 'hero_slide_4_bg', 'hero_slide_5_bg',
                'about_hero_bg', 'activities_hero_bg', 'recruitment_hero_bg', 'contact_hero_bg',
            ];
            $removeImages = isset($_POST['remove_images']) && is_array($_POST['remove_images'])
                ? array_keys($_POST['remove_images'])
                : [];
            $deleteContent = $pdo->prepare('DELETE FROM `page_contents` WHERE `content_key` = ?');

            foreach ($removeImages as $removeKey) {
                if (!is_string($removeKey) || !in_array($removeKey, $removableImageKeys, true)) {
                    continue;
                }

                $smallKey = $removeKey . '_small';
                remove_replaced_upload($existingImages[$removeKey] ?? null, '');
                remove_replaced_upload($existingImages[$smallKey] ?? null, '');
                foreach ([$removeKey, $removeKey . '_width', $removeKey . '_height', $smallKey, $smallKey . '_width', $smallKey . '_height'] as $contentKey) {
                    $deleteContent->execute([$contentKey]);
                }
                $uploaded = true;
            }

            foreach ($imageFields as $field => $config) {
                if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
                    continue;
                }
                $uploadError = (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE);
                if ($uploadError === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($uploadError !== UPLOAD_ERR_OK) {
                    $uploadErrorMessage = match ($uploadError) {
                        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ảnh vượt giới hạn dung lượng của máy chủ.',
                        UPLOAD_ERR_PARTIAL => 'Ảnh chỉ được tải lên một phần. Vui lòng thử lại.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ thiếu thư mục tạm để nhận ảnh.',
                        UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi ảnh vào ổ đĩa.',
                        UPLOAD_ERR_EXTENSION => 'Tiện ích PHP đã dừng quá trình tải ảnh.',
                        default => 'Dữ liệu tải ảnh không hợp lệ.',
                    };
                    throw new RuntimeException($uploadErrorMessage);
                }
                if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file((string) $_FILES[$field]['tmp_name'])) {
                    throw new RuntimeException('Máy chủ không nhận được tệp ảnh mới hợp lệ.');
                }

                $image = upload_responsive_image(
                    $_FILES[$field],
                    $targetFolder,
                    $config['max_width'],
                    $config['max_height'],
                    $config['small']
                );
                if ($image === null) {
                    continue;
                }

                $key = $config['key'];
                $stmt->execute([$key, $image['path']]);
                $stmt->execute([$key . '_width', (string) $image['width']]);
                $stmt->execute([$key . '_height', (string) $image['height']]);
                remove_replaced_upload($existingImages[$key] ?? null, $image['path']);

                if (is_string($config['small_key'])) {
                    $smallKey = $config['small_key'];
                    $stmt->execute([$smallKey, $image['small_path']]);
                    $stmt->execute([$smallKey . '_width', (string) $image['small_width']]);
                    $stmt->execute([$smallKey . '_height', (string) $image['small_height']]);
                    remove_replaced_upload($existingImages[$smallKey] ?? null, $image['small_path']);
                }

                $uploaded = true;
            }

            if ($uploaded) {
                editable_cache_clear();
                page_cache_clear();

                if ($isAsyncUpload) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                set_flash('success', 'Đã cập nhật ảnh chất lượng cao và các bản responsive.');
                redirect('settings.php?tab=images');
            } else {
                $errors[] = 'Vui lòng chọn ít nhất một tệp tin ảnh hợp lệ.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Lỗi tải ảnh: ' . $e->getMessage();
        }
    }
}

if ($isAsyncUpload && $errors) {
    http_response_code(422);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy thông tin thiết lập hiện có
$sections = $pdo->query("SELECT * FROM `sections` ORDER BY `sort_order` ASC, `id` ASC")->fetchAll(PDO::FETCH_ASSOC);

$contents = editable_contents();
$primaryColor = $contents['theme_primary_color'] ?? '#3b82f6';
$secondaryColor = $contents['theme_secondary_color'] ?? '#06b6d4';
$accentColor = $contents['theme_accent_color'] ?? '#f97316';

$logoPath = $contents['site_logo'] ?? 'assets/images/cit/logoclb.png';
$heroBgPath = $contents['hero_bg'] ?? 'assets/images/cit/cit-cover.webp';
$heroSlidePaths = [
    1 => $heroBgPath,
    2 => $contents['hero_slide_2_bg'] ?? '',
    3 => $contents['hero_slide_3_bg'] ?? '',
    4 => $contents['hero_slide_4_bg'] ?? '',
    5 => $contents['hero_slide_5_bg'] ?? '',
];
$aboutHeroBgPath = $contents['about_hero_bg'] ?? 'assets/images/cit/cit-cover.webp';
$activitiesHeroBgPath = $contents['activities_hero_bg'] ?? 'assets/images/cit/albums/aeternum-hackerrank.webp';
$recruitmentHeroBgPath = $contents['recruitment_hero_bg'] ?? 'assets/images/cit/cit-cover.webp';
$contactHeroBgPath = $contents['contact_hero_bg'] ?? 'assets/images/cit/cit-cover.webp';

$contentWidth = $contents['layout_content_width'] ?? '1320px';
$cardRadius = $contents['layout_card_radius'] ?? '16px';
$sectionSpacing = $contents['layout_section_spacing'] ?? 'balanced';
$baseFontSize = $contents['layout_base_font_size'] ?? '16px';

$heroCtaText = $contents['hero_cta_text'] ?? 'Xem tuyển thành viên';
$heroCtaUrl = $contents['hero_cta_url'] ?? 'recruitment.php';
$heroExploreText = $contents['hero_explore_text'] ?? 'Khám phá fanpage';
$heroExploreUrl = $contents['hero_explore_url'] ?? 'https://www.facebook.com/clbcongnghe.cit';

$currentTab = isset($_GET['tab']) && is_string($_GET['tab']) ? $_GET['tab'] : 'sections';
$validTabs = ['sections', 'colors', 'layout', 'images', 'ctas'];
if (!in_array($currentTab, $validTabs, true)) {
    $currentTab = 'sections';
}
if (isset($_GET['uploaded']) && is_string($_GET['uploaded']) && ctype_digit($_GET['uploaded'])) {
    $uploadedCount = max(1, min(10, (int) $_GET['uploaded']));
    $successMessage = 'Đã tối ưu và tải thành công ' . $uploadedCount . ' ảnh.';
}
$pageTitle = 'Cấu hình giao diện';
$adminScripts = ['../assets/js/admin-settings.min.js'];
require_once __DIR__ . '/header.php';
?>

<div class="settings-page">
<header class="settings-heading">
    <div>
        <span class="settings-kicker">Website CIT</span>
        <h1>Cấu hình giao diện</h1>
        <p>Quản lý nhận diện, bố cục, hình ảnh và các khối nội dung.</p>
    </div>
    <a href="../index.php" target="_blank" rel="noopener" class="btn btn-primary settings-preview-button">
        <i class="bi bi-box-arrow-up-right"></i>
        Xem website
    </a>
</header>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($successMessage !== ''): ?>
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i><?= e($successMessage) ?>
    </div>
<?php endif; ?>

<section class="settings-overview" aria-label="Tổng quan cấu hình">
    <div class="settings-brand-summary">
        <img src="../<?= e($logoPath) ?>" alt="Logo CIT hiện tại" width="52" height="52">
        <div>
            <span>Nhận diện hiện tại</span>
            <strong>CLB Công nghệ CIT</strong>
        </div>
    </div>
    <div class="settings-metric">
        <i class="bi bi-layout-text-window-reverse"></i>
        <div><strong><?= count($sections) ?></strong><span>khối trang chủ</span></div>
    </div>
    <div class="settings-metric">
        <i class="bi bi-images"></i>
        <div><strong>5</strong><span>banner trang</span></div>
    </div>
    <div class="settings-metric">
        <i class="bi bi-speedometer2"></i>
        <div><strong>WebP</strong><span>ảnh responsive</span></div>
    </div>
</section>

<div class="settings-workspace">
    <nav class="settings-navigation" aria-label="Nhóm cấu hình giao diện">
        <?php foreach ([
            'sections' => ['bi-grid-1x2', 'Khối trang chủ', 'Thứ tự và trạng thái'],
            'colors' => ['bi-palette2', 'Màu thương hiệu', 'Bảng màu toàn site'],
            'layout' => ['bi-columns-gap', 'Bố cục', 'Kích thước và khoảng cách'],
            'images' => ['bi-image', 'Hình ảnh', 'Logo và banner các trang'],
            'ctas' => ['bi-cursor', 'Nút hành động', 'Nhãn và liên kết'],
        ] as $tabKey => [$tabIcon, $tabLabel, $tabDescription]): ?>
            <a class="settings-nav-item <?= $currentTab === $tabKey ? 'active' : '' ?>" href="?tab=<?= e($tabKey) ?>">
                <i class="bi <?= e($tabIcon) ?>"></i>
                <span><strong><?= e($tabLabel) ?></strong><small><?= e($tabDescription) ?></small></span>
                <i class="bi bi-chevron-right settings-nav-arrow"></i>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="settings-panel">
        <?php if ($currentTab === 'sections'): ?>
            <div class="settings-panel-header">
                <div><span>Trang chủ</span><h2>Khối nội dung</h2></div>
                <p>Sắp xếp và bật tắt các phần đang hiển thị.</p>
            </div>
                <form method="post" action="settings.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_sections">
                    <div class="section-control-list">
                        <?php foreach ($sections as $sec): ?>
                            <div class="section-control-row">
                                <i class="bi bi-grip-vertical section-control-handle" aria-hidden="true"></i>
                                <label class="section-order-field">
                                    <span>Vị trí</span>
                                    <input type="number"
                                           name="orders[<?= e($sec['section_key']) ?>]"
                                           value="<?= (int) $sec['sort_order'] ?>"
                                           min="1"
                                           max="50"
                                           required>
                                </label>
                                <div class="section-control-name">
                                    <strong><?= e($sec['section_name']) ?></strong>
                                    <code><?= e($sec['section_key']) ?></code>
                                </div>
                                <label class="settings-switch" for="visible_<?= e($sec['section_key']) ?>">
                                    <input type="checkbox"
                                           name="visibles[<?= e($sec['section_key']) ?>]"
                                           id="visible_<?= e($sec['section_key']) ?>"
                                           value="1"
                                           <?= (int) $sec['is_visible'] === 1 ? 'checked' : '' ?>>
                                    <span aria-hidden="true"></span>
                                    <em>Hiển thị</em>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="settings-savebar">
                        <span><i class="bi bi-info-circle"></i> Thay đổi được áp dụng sau khi lưu.</span>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i>Lưu khối nội dung</button>
                    </div>
                </form>

        <?php elseif ($currentTab === 'colors'): ?>
            <div class="settings-panel-header">
                <div><span>Nhận diện</span><h2>Màu thương hiệu</h2></div>
                <p>Màu được áp dụng cho nút, liên kết, điểm nhấn và gradient.</p>
            </div>
                <form method="post" action="settings.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_theme_colors">
                    <div class="settings-editor-grid">
                        <div class="settings-field-stack">
                            <?php foreach ([
                                ['theme_primary_color', 'Màu chủ đạo', 'Nút chính và liên kết', $primaryColor],
                                ['theme_secondary_color', 'Màu phụ', 'Gradient và trạng thái', $secondaryColor],
                                ['theme_accent_color', 'Màu điểm nhấn', 'Nhãn và chi tiết nổi bật', $accentColor],
                            ] as [$colorName, $colorLabel, $colorHint, $colorValue]): ?>
                                <label class="color-control">
                                    <input type="color" name="<?= e($colorName) ?>" value="<?= e($colorValue) ?>">
                                    <span><strong><?= e($colorLabel) ?></strong><small><?= e($colorHint) ?></small></span>
                                    <input type="text" value="<?= e($colorValue) ?>" class="color-value" readonly aria-label="Mã màu <?= e($colorLabel) ?>">
                                </label>
                            <?php endforeach; ?>
                            <div class="color-presets" aria-label="Bảng màu gợi ý">
                                <button type="button" data-theme-preset="#2563EB,#06B6D4,#FF7A59"><span style="--preset:#2563EB"></span>CIT</button>
                                <button type="button" data-theme-preset="#0F766E,#22C55E,#F59E0B"><span style="--preset:#0F766E"></span>Tươi sáng</button>
                                <button type="button" data-theme-preset="#334155,#0284C7,#E11D48"><span style="--preset:#334155"></span>Hiện đại</button>
                            </div>
                        </div>
                        <div class="theme-preview" id="themePreview" style="--preview-primary:<?= e($primaryColor) ?>;--preview-secondary:<?= e($secondaryColor) ?>;--preview-accent:<?= e($accentColor) ?>">
                            <div class="theme-preview-nav"><img src="../<?= e($logoPath) ?>" alt="" width="34" height="34"><span>CIT Club</span><i class="bi bi-list"></i></div>
                            <div class="theme-preview-hero">
                                <span>Cộng đồng công nghệ TMU</span>
                                <strong>Học hỏi. Sáng tạo. Kết nối.</strong>
                                <p>Cùng phát triển chuyên môn và tạo nên những dấu mốc mới.</p>
                                <div><b>Tham gia CIT</b><em>Khám phá</em></div>
                            </div>
                            <div class="theme-preview-cards"><span></span><span></span><span></span></div>
                        </div>
                    </div>
                    <div class="settings-savebar">
                        <span>Preview cập nhật ngay khi chọn màu.</span>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i>Lưu bảng màu</button>
                    </div>
                </form>

        <?php elseif ($currentTab === 'layout'): ?>
            <div class="settings-panel-header">
                <div><span>Toàn website</span><h2>Bố cục và hiển thị</h2></div>
                <p>Điều chỉnh mật độ nội dung trên máy tính và thiết bị di động.</p>
            </div>
            <form method="post" action="settings.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_layout">
                <div class="layout-control-grid">
                    <label class="layout-control">
                        <i class="bi bi-arrows-angle-expand"></i>
                        <span><strong>Độ rộng nội dung</strong><small>Giới hạn vùng đọc trên màn hình lớn</small></span>
                        <select name="layout_content_width" class="form-select">
                            <option value="1140px" <?= $contentWidth === '1140px' ? 'selected' : '' ?>>Gọn · 1140px</option>
                            <option value="1200px" <?= $contentWidth === '1200px' ? 'selected' : '' ?>>Cân đối · 1200px</option>
                            <option value="1320px" <?= $contentWidth === '1320px' ? 'selected' : '' ?>>Rộng · 1320px</option>
                        </select>
                    </label>
                    <label class="layout-control">
                        <i class="bi bi-bounding-box-circles"></i>
                        <span><strong>Độ bo góc</strong><small>Áp dụng cho card và khối nội dung</small></span>
                        <select name="layout_card_radius" class="form-select">
                            <option value="8px" <?= $cardRadius === '8px' ? 'selected' : '' ?>>Gọn · 8px</option>
                            <option value="12px" <?= $cardRadius === '12px' ? 'selected' : '' ?>>Hiện đại · 12px</option>
                            <option value="16px" <?= $cardRadius === '16px' ? 'selected' : '' ?>>Mềm · 16px</option>
                        </select>
                    </label>
                    <label class="layout-control">
                        <i class="bi bi-distribute-vertical"></i>
                        <span><strong>Khoảng cách section</strong><small>Mật độ giữa các phần của trang</small></span>
                        <select name="layout_section_spacing" class="form-select">
                            <option value="compact" <?= $sectionSpacing === 'compact' ? 'selected' : '' ?>>Gọn</option>
                            <option value="balanced" <?= $sectionSpacing === 'balanced' ? 'selected' : '' ?>>Cân đối</option>
                            <option value="airy" <?= $sectionSpacing === 'airy' ? 'selected' : '' ?>>Thoáng</option>
                        </select>
                    </label>
                    <label class="layout-control">
                        <i class="bi bi-fonts"></i>
                        <span><strong>Cỡ chữ cơ sở</strong><small>Tỷ lệ chữ cho toàn bộ giao diện</small></span>
                        <select name="layout_base_font_size" class="form-select">
                            <option value="15px" <?= $baseFontSize === '15px' ? 'selected' : '' ?>>Nhỏ · 15px</option>
                            <option value="16px" <?= $baseFontSize === '16px' ? 'selected' : '' ?>>Tiêu chuẩn · 16px</option>
                            <option value="17px" <?= $baseFontSize === '17px' ? 'selected' : '' ?>>Lớn · 17px</option>
                        </select>
                    </label>
                </div>
                <div class="layout-preview" id="layoutPreview">
                    <span class="layout-preview-line wide"></span><span class="layout-preview-line"></span>
                    <div><span></span><span></span><span></span></div>
                </div>
                <div class="settings-savebar">
                    <span>Các giá trị đều dùng CSS, không tăng tải máy chủ.</span>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i>Lưu bố cục</button>
                </div>
            </form>

        <?php elseif ($currentTab === 'images'): ?>
            <div class="settings-panel-header">
                <div><span>Thư viện giao diện</span><h2>Logo và banner</h2></div>
                <p>Ảnh lớn WebP cho màn hình nét cao, kèm bản nhẹ 960px cho thiết bị nhỏ.</p>
            </div>
            <div class="image-optimization-strip">
                <span><i class="bi bi-badge-hd"></i><strong>Tối đa 2560px</strong></span>
                <span><i class="bi bi-filetype-webp"></i><strong>WebP 82%</strong></span>
                <span><i class="bi bi-phone"></i><strong>Thumbnail 960px</strong></span>
                <span><i class="bi bi-hdd"></i><strong>Tối ưu SSD</strong></span>
            </div>
                <form method="post" action="settings.php" enctype="multipart/form-data" id="imageSettingsForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="upload_images">
                    <div class="image-group-heading">
                        <div><strong>Slideshow Trang chủ</strong><small>Tự động chuyển ảnh sau mỗi 3 giây</small></div>
                        <span><?= count(array_filter($heroSlidePaths)) ?>/5 ảnh</span>
                    </div>
                    <div class="image-settings-grid">
                        <?php for ($slideIndex = 1; $slideIndex <= 5; $slideIndex++): ?>
                            <?php
                            $slidePath = $heroSlidePaths[$slideIndex];
                            $slideFileId = $slideIndex === 1 ? 'hero_file' : 'hero_slide_' . $slideIndex . '_file';
                            $slideContentKey = $slideIndex === 1 ? 'hero_bg' : 'hero_slide_' . $slideIndex . '_bg';
                            ?>
                            <div class="image-setting-card <?= $slidePath === '' ? 'is-empty' : '' ?>">
                                <span class="image-setting-title"><strong>Ảnh <?= $slideIndex ?>/5</strong><small><?= $slideIndex === 1 ? 'Banner chính' : 'Ảnh chuyển tiếp' ?></small></span>
                                <span class="image-setting-preview">
                                    <?php if ($slidePath !== ''): ?>
                                        <img src="../<?= e($slidePath) ?>" alt="Ảnh slideshow <?= $slideIndex ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="image-empty-state"><i class="bi bi-image"></i><em>Chưa có ảnh</em></span>
                                    <?php endif; ?>
                                </span>
                                <label class="image-setting-action" for="<?= e($slideFileId) ?>"><i class="bi bi-upload"></i><?= $slidePath === '' ? 'Thêm ảnh' : 'Thay ảnh' ?></label>
                                <input type="file" name="<?= e($slideFileId) ?>" id="<?= e($slideFileId) ?>" accept="image/jpeg,image/png,image/webp" data-image-preview>
                                <?php if ($slideIndex > 1 && $slidePath !== ''): ?>
                                    <label class="image-remove-action"><input type="checkbox" name="remove_images[<?= e($slideContentKey) ?>]" value="1"><span><i class="bi bi-trash3"></i>Xóa khi lưu</span></label>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="image-group-heading image-group-heading-spaced">
                        <div><strong>Nhận diện và banner từng trang</strong><small>Ảnh riêng cho mỗi khu vực chính</small></div>
                    </div>
                    <div class="image-settings-grid">
                        <?php foreach ([
                            ['logo_file', 'Logo website', 'Navbar và footer', $logoPath, true, ''],
                            ['about_hero_file', 'Giới thiệu', 'Banner trang giới thiệu', $aboutHeroBgPath, false, 'about_hero_bg'],
                            ['activities_hero_file', 'Hoạt động', 'Banner trang hoạt động', $activitiesHeroBgPath, false, 'activities_hero_bg'],
                            ['recruitment_hero_file', 'Tuyển thành viên', 'Banner trang tuyển thành viên', $recruitmentHeroBgPath, false, 'recruitment_hero_bg'],
                            ['contact_hero_file', 'Liên hệ', 'Banner trang liên hệ', $contactHeroBgPath, false, 'contact_hero_bg'],
                        ] as [$fileId, $imageLabel, $imageLocation, $imagePath, $isLogo, $removableKey]): ?>
                            <div class="image-setting-card <?= $isLogo ? 'is-logo' : '' ?>">
                                <span class="image-setting-title"><strong><?= e($imageLabel) ?></strong><small><?= e($imageLocation) ?></small></span>
                                <span class="image-setting-preview"><img src="../<?= e($imagePath) ?>" alt="Ảnh <?= e($imageLabel) ?> hiện tại" loading="lazy"></span>
                                <label class="image-setting-action" for="<?= e($fileId) ?>"><i class="bi bi-upload"></i>Chọn ảnh mới</label>
                                <input type="file" name="<?= e($fileId) ?>" id="<?= e($fileId) ?>" accept="image/jpeg,image/png,image/webp" data-image-preview>
                                <?php if ($removableKey !== '' && isset($contents[$removableKey])): ?>
                                    <label class="image-remove-action"><input type="checkbox" name="remove_images[<?= e($removableKey) ?>]" value="1"><span><i class="bi bi-arrow-counterclockwise"></i>Dùng ảnh mặc định</span></label>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="settings-savebar">
                        <span id="imageUploadStatus" aria-live="polite">JPEG, PNG hoặc WebP · hệ thống tự nén và tải tuần tự.</span>
                        <button type="submit" class="btn btn-primary" data-upload-submit><i class="bi bi-cloud-arrow-up"></i><span>Tải lên và áp dụng</span></button>
                    </div>
                </form>

        <?php else: ?>
            <div class="settings-panel-header">
                <div><span>Banner trang chủ</span><h2>Nút hành động</h2></div>
                <p>Nhãn ngắn gọn và liên kết cho hai hành động chính.</p>
            </div>
                <form method="post" action="settings.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_ctas">
                    <div class="cta-settings-grid">
                        <section class="cta-setting-card">
                            <span class="cta-setting-icon primary"><i class="bi bi-person-plus"></i></span>
                            <div><span class="settings-kicker">Nút chính</span><h3>Tuyển thành viên</h3></div>
                            <div>
                                <label class="form-label fw-semibold" for="hero_cta_text">Nhãn hiển thị</label>
                                <input type="text" name="hero_cta_text" id="hero_cta_text" value="<?= e($heroCtaText) ?>" class="form-control" required maxlength="50">
                            </div>
                            <div>
                                <label class="form-label fw-semibold" for="hero_cta_url">Liên kết</label>
                                <input type="text" name="hero_cta_url" id="hero_cta_url" value="<?= e($heroCtaUrl) ?>" class="form-control" required placeholder="recruitment.php hoặc đường dẫn ngoài">
                            </div>
                        </section>
                        <section class="cta-setting-card">
                            <span class="cta-setting-icon secondary"><i class="bi bi-facebook"></i></span>
                            <div><span class="settings-kicker">Nút phụ</span><h3>Kênh cộng đồng</h3></div>
                            <div>
                                <label class="form-label fw-semibold" for="hero_explore_text">Nhãn hiển thị</label>
                                <input type="text" name="hero_explore_text" id="hero_explore_text" value="<?= e($heroExploreText) ?>" class="form-control" required maxlength="50">
                            </div>
                            <div>
                                <label class="form-label fw-semibold" for="hero_explore_url">Liên kết</label>
                                <input type="text" name="hero_explore_url" id="hero_explore_url" value="<?= e($heroExploreUrl) ?>" class="form-control" required>
                            </div>
                        </section>
                    </div>
                    <div class="settings-savebar">
                        <span>Liên kết nội bộ hoặc URL đầy đủ đều được hỗ trợ.</span>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i>Lưu nút hành động</button>
                    </div>
                </form>
        <?php endif; ?>
    </div>
</div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
