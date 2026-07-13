-- CIT Club website - clean MySQL/MariaDB installation
-- Updated: 2026-07-13
-- Import this file into an existing empty database on shared hosting.

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `email_deliveries`;
DROP TABLE IF EXISTS `email_batches`;
DROP TABLE IF EXISTS `form_submission_values`;
DROP TABLE IF EXISTS `form_submissions`;
DROP TABLE IF EXISTS `form_fields`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `page_contents`;
DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `page_contents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_key` (`content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `form_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `field_label` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` enum('text','email','phone','textarea','dropdown','radio','checkbox') COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` text COLLATE utf8mb4_unicode_ci,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_name` (`field_name`),
  KEY `idx_form_fields_sort_order` (`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `form_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `idx_submissions_status_date` (`status`,`submitted_at`),
  KEY `idx_submissions_date` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `form_submission_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint unsigned NOT NULL,
  `field_id` int unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_submission_field` (`submission_id`,`field_id`),
  KEY `idx_values_field` (`field_id`),
  CONSTRAINT `fk_values_field` FOREIGN KEY (`field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_values_submission` FOREIGN KEY (`submission_id`) REFERENCES `form_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `message_type` enum('approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_template` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply_to` varchar(254) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_summary` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `selected_count` int unsigned NOT NULL DEFAULT '0',
  `duplicate_count` int unsigned NOT NULL DEFAULT '0',
  `processed_count` int unsigned NOT NULL DEFAULT '0',
  `sent_count` int unsigned NOT NULL DEFAULT '0',
  `failed_count` int unsigned NOT NULL DEFAULT '0',
  `skipped_count` int unsigned NOT NULL DEFAULT '0',
  `status` enum('pending','processing','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email_batches_status_created` (`status`,`created_at`),
  KEY `idx_email_batches_created_by` (`created_by`),
  CONSTRAINT `fk_email_batches_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint unsigned NOT NULL,
  `submission_id` bigint unsigned NOT NULL,
  `message_type` enum('approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_email` varchar(254) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_html` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','sending','sent','failed','skipped') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` smallint unsigned NOT NULL DEFAULT '0',
  `last_error` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_attempt_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_delivery_submission_type` (`submission_id`,`message_type`),
  KEY `idx_email_deliveries_batch_status` (`batch_id`,`status`,`id`),
  CONSTRAINT `fk_email_deliveries_batch` FOREIGN KEY (`batch_id`) REFERENCES `email_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_email_deliveries_submission` FOREIGN KEY (`submission_id`) REFERENCES `form_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default administrator. Change the password immediately after deployment.
INSERT INTO `admin` (`id`, `username`, `password_hash`) VALUES
  (1, 'admin', '$2y$10$Y5HTyPQVbktoUBTOHntKGeGUX44.yYJzhkGGt5MF6NqAKFMnX2eRa');

INSERT INTO `sections` (`id`, `section_key`, `section_name`, `sort_order`, `is_visible`) VALUES
  (1, 'hero', 'Banner đầu trang (Hero)', 1, 1),
  (2, 'highlights', 'Điểm nhấn từ fanpage (Highlights)', 2, 1),
  (3, 'stats', 'Thanh thống kê (Stats)', 3, 1),
  (4, 'about', 'Giới thiệu về chúng mình (About)', 4, 1),
  (5, 'activities', 'Những khoảnh khắc đáng nhớ (Activities)', 5, 1),
  (6, 'gallery', 'Album ảnh của CIT (Gallery)', 6, 1);

INSERT INTO `form_fields`
  (`id`, `field_label`, `field_name`, `field_type`, `options`, `is_required`, `sort_order`) VALUES
  (1, 'Họ và tên', 'ho_ten', 'text', NULL, 1, 1),
  (2, 'Email', 'email', 'email', NULL, 1, 2),
  (3, 'Số điện thoại', 'so_dien_thoai', 'phone', NULL, 1, 3),
  (4, 'Lớp / Ngành', 'lop_nganh', 'text', NULL, 1, 4),
  (5, 'Facebook', 'facebook', 'text', NULL, 0, 5),
  (6, 'Lý do tham gia', 'ly_do_tham_gia', 'textarea', NULL, 1, 6);

INSERT INTO `page_contents` (`content_key`, `content_value`) VALUES
  ('home_title_current', 'CIT Club — Cộng đồng công nghệ sinh viên TMU'),
  ('home_desc_current', 'CIT là câu lạc bộ về lĩnh vực Công nghệ đầu tiên trực thuộc Đoàn TNCS Hồ Chí Minh Trường Đại học Thương mại, dưới sự quản lý của Khoa Công nghệ số ứng dụng.'),
  ('theme_primary_color', '#3b82f6'),
  ('theme_secondary_color', '#06b6d4'),
  ('theme_accent_color', '#f97316'),
  ('layout_content_width', '1320px'),
  ('layout_card_radius', '16px'),
  ('layout_section_spacing', 'balanced'),
  ('layout_base_font_size', '16px'),
  ('site_logo', 'assets/images/cit/logoclb.png'),
  ('hero_bg', 'assets/images/cit/cit-cover.webp'),
  ('hero_cta_text', 'Xem tuyển thành viên'),
  ('hero_cta_url', 'recruitment.php'),
  ('hero_explore_text', 'Khám phá fanpage'),
  ('hero_explore_url', 'https://www.facebook.com/clbcongnghe.cit'),
  ('recruitment_form_closed', '0'),
  ('recruitment_closed_message', 'CIT hiện đã đóng form tuyển thành viên. Hẹn gặp bạn ở đợt tuyển tiếp theo.'),
  ('mail_sender_name', 'CLB Công nghệ CIT'),
  ('mail_reply_to', ''),
  ('mail_approved_subject', 'Chúc mừng {{ho_ten}} đã trở thành thành viên CIT'),
  ('mail_approved_body', '<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Chúc mừng bạn đã vượt qua đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Chúng mình rất vui được đồng hành cùng bạn trong những hoạt động sắp tới. Thông tin tiếp theo sẽ được gửi tới bạn trong thời gian sớm nhất.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>'),
  ('mail_rejected_subject', 'Kết quả tuyển thành viên {{ten_clb}}'),
  ('mail_rejected_body', '<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Cảm ơn bạn đã dành thời gian tham gia đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Rất tiếc trong đợt này chúng mình chưa thể đồng hành cùng bạn. Hy vọng sẽ được gặp lại bạn trong những hoạt động và cơ hội tiếp theo của CIT.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>');

SET FOREIGN_KEY_CHECKS = 1;
