CREATE TABLE IF NOT EXISTS `email_batches` (
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
  CONSTRAINT `fk_email_batches_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_deliveries` (
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

INSERT INTO page_contents (content_key, content_value) VALUES
  ('mail_sender_name', 'CLB Công nghệ CIT'),
  ('mail_reply_to', ''),
  ('mail_approved_subject', 'Chúc mừng {{ho_ten}} đã trở thành thành viên CIT'),
  ('mail_approved_body', '<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Chúc mừng bạn đã vượt qua đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Chúng mình rất vui được đồng hành cùng bạn trong những hoạt động sắp tới.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>'),
  ('mail_rejected_subject', 'Kết quả tuyển thành viên {{ten_clb}}'),
  ('mail_rejected_body', '<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Cảm ơn bạn đã dành thời gian tham gia đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Rất tiếc trong đợt này chúng mình chưa thể đồng hành cùng bạn. Hy vọng sẽ được gặp lại bạn trong những cơ hội tiếp theo.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>')
ON DUPLICATE KEY UPDATE content_value = content_value;
