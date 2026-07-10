<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    // 1. Tạo bảng sections nếu chưa tồn tại
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sections` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `section_key` VARCHAR(50) NOT NULL UNIQUE,
            `section_name` VARCHAR(100) NOT NULL,
            `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
            `is_visible` TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Bảng `sections` đã được tạo hoặc đã tồn tại.\n";

    // 2. Chèn các section mặc định nếu chưa có
    $defaultSections = [
        ['hero', 'Banner đầu trang (Hero)', 1, 1],
        ['highlights', 'Điểm nhấn từ fanpage (Highlights)', 2, 1],
        ['stats', 'Thanh thống kê (Stats)', 3, 1],
        ['about', 'Giới thiệu về chúng mình (About)', 4, 1],
        ['activities', 'Những khoảnh khắc đáng nhớ (Activities)', 5, 1],
        ['gallery', 'Album ảnh của CIT (Gallery)', 6, 1]
    ];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `sections` WHERE `section_key` = ?");
    $insertStmt = $pdo->prepare("
        INSERT INTO `sections` (`section_key`, `section_name`, `sort_order`, `is_visible`) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($defaultSections as [$key, $name, $order, $visible]) {
        $checkStmt->execute([$key]);
        if ((int)$checkStmt->fetchColumn() === 0) {
            $insertStmt->execute([$key, $name, $order, $visible]);
            echo "Đã chèn mặc định section: {$key}\n";
        }
    }

    // 3. Khởi tạo giá trị mặc định cho cấu hình giao diện trong `page_contents`
    $defaultSettings = [
        'theme_primary_color' => '#3b82f6',
        'theme_secondary_color' => '#06b6d4',
        'theme_accent_color' => '#f97316',
        'site_logo' => 'assets/images/cit/logoclb.png',
        'hero_bg' => 'assets/images/cit/cit-cover.webp',
        'hero_cta_text' => 'Xem tuyển thành viên',
        'hero_cta_url' => 'recruitment.php',
        'hero_explore_text' => 'Khám phá fanpage',
        'hero_explore_url' => 'https://www.facebook.com/clbcongnghe.cit'
    ];

    $checkSetting = $pdo->prepare("SELECT COUNT(*) FROM `page_contents` WHERE `content_key` = ?");
    $insertSetting = $pdo->prepare("INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES (?, ?)");

    foreach ($defaultSettings as $key => $val) {
        $checkSetting->execute([$key]);
        if ((int)$checkSetting->fetchColumn() === 0) {
            $insertSetting->execute([$key, $val]);
            echo "Đã chèn mặc định setting: {$key} = {$val}\n";
        }
    }

    echo "Hoàn thành di chuyển (migration) dữ liệu thành công!\n";
} catch (PDOException $e) {
    exit("Lỗi di chuyển database: " . $e->getMessage() . "\n");
}
