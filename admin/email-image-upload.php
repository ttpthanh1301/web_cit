<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/../includes/email.php';

if (!is_post()) {
    email_json_response(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
}
verify_csrf();

$file = $_FILES['image'] ?? null;
if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    email_json_response(['success' => false, 'message' => 'Hãy chọn một ảnh hợp lệ.'], 422);
}
if ((int) ($file['size'] ?? 0) > 4 * 1024 * 1024) {
    email_json_response(['success' => false, 'message' => 'Ảnh không được vượt quá 4 MB.'], 422);
}

$imageInfo = @getimagesize((string) $file['tmp_name']);
$mimeType = is_array($imageInfo) ? (string) ($imageInfo['mime'] ?? '') : '';
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
if (!is_array($imageInfo) || !in_array($mimeType, $allowedTypes, true)) {
    email_json_response(['success' => false, 'message' => 'Chỉ chấp nhận ảnh JPEG, PNG hoặc WEBP.'], 422);
}
$sourceWidth = (int) ($imageInfo[0] ?? 0);
$sourceHeight = (int) ($imageInfo[1] ?? 0);
if ($sourceWidth < 1 || $sourceHeight < 1 || $sourceWidth * $sourceHeight > 8000000) {
    email_json_response(['success' => false, 'message' => 'Ảnh không hợp lệ hoặc vượt quá 8 megapixel.'], 422);
}

$targetFolder = __DIR__ . '/../uploads/email';
if (!is_dir($targetFolder) && !mkdir($targetFolder, 0755, true) && !is_dir($targetFolder)) {
    email_json_response(['success' => false, 'message' => 'Không thể tạo thư mục lưu ảnh email.'], 500);
}

try {
    $baseName = 'mail_' . bin2hex(random_bytes(10));
    $filename = '';
    $savedWidth = $sourceWidth;
    $savedHeight = $sourceHeight;
    $saved = false;
    $loaders = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    $loader = $loaders[$mimeType] ?? '';

    if (extension_loaded('gd') && function_exists('imagejpeg') && $loader !== '' && function_exists($loader)) {
        $source = @$loader((string) $file['tmp_name']);
        if ($source !== false) {
            $scale = min(1, 1200 / $sourceWidth, 1200 / $sourceHeight);
            $savedWidth = max(1, (int) round($sourceWidth * $scale));
            $savedHeight = max(1, (int) round($sourceHeight * $scale));
            $output = imagecreatetruecolor($savedWidth, $savedHeight);
            if ($output === false) {
                imagedestroy($source);
                throw new RuntimeException('Không đủ bộ nhớ để xử lý ảnh.');
            }
            $white = imagecolorallocate($output, 255, 255, 255);
            imagefill($output, 0, 0, $white);
            imagecopyresampled($output, $source, 0, 0, 0, 0, $savedWidth, $savedHeight, $sourceWidth, $sourceHeight);
            $filename = $baseName . '.jpg';
            $saved = imagejpeg($output, $targetFolder . '/' . $filename, 82);
            imagedestroy($output);
            imagedestroy($source);
        }
    }

    if (!$saved) {
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'webp',
        };
        $filename = $baseName . '.' . $extension;
        $saved = move_uploaded_file((string) $file['tmp_name'], $targetFolder . '/' . $filename);
    }
    if (!$saved) {
        throw new RuntimeException('Không thể lưu ảnh đã tải lên.');
    }

    $appUrl = rtrim((string) (getenv('APP_URL') ?: ''), '/');
    if ($appUrl === '') {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9.\-:\[\]]+$/', $host)) {
            throw new RuntimeException('APP_URL chưa được cấu hình hợp lệ.');
        }
        $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
        $rootPath = rtrim(str_replace('\\', '/', dirname(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/admin/email-image-upload.php')))), '/');
        $appUrl = ($https ? 'https://' : 'http://') . $host . ($rootPath === '' ? '' : $rootPath);
    }

    email_json_response([
        'success' => true,
        'message' => 'Ảnh đã được tối ưu và tải lên.',
        'url' => $appUrl . '/uploads/email/' . rawurlencode($filename),
        'width' => $savedWidth,
        'height' => $savedHeight,
    ]);
} catch (Throwable $exception) {
    error_log('Email image upload failed: ' . $exception->getMessage());
    email_json_response(['success' => false, 'message' => $exception->getMessage()], 500);
}
