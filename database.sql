-- MySQL dump 10.13  Distrib 8.0.46, for macos15.7 (arm64)
--
-- Host: 127.0.0.1    Database: club_management
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$Y5HTyPQVbktoUBTOHntKGeGUX44.yYJzhkGGt5MF6NqAKFMnX2eRa');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_fields`
--

DROP TABLE IF EXISTS `form_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_fields`
--

LOCK TABLES `form_fields` WRITE;
/*!40000 ALTER TABLE `form_fields` DISABLE KEYS */;
INSERT INTO `form_fields` VALUES (1,'Họ và tên','ho_ten','text',NULL,1,1),(2,'Email','email','email',NULL,1,2),(3,'Số điện thoại','so_dien_thoai','phone',NULL,1,3),(4,'Lớp / Ngành','lop_nganh','text',NULL,1,4),(5,'Lý do tham gia','ly_do_tham_gia','textarea',NULL,1,6),(6,'Facebook','facebook','text',NULL,1,5),(7,'abc','abc','text',NULL,0,7);
/*!40000 ALTER TABLE `form_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_submission_values`
--

DROP TABLE IF EXISTS `form_submission_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_submission_values`
--

LOCK TABLES `form_submission_values` WRITE;
/*!40000 ALTER TABLE `form_submission_values` DISABLE KEYS */;
INSERT INTO `form_submission_values` VALUES (1,1,1,'Test User'),(2,1,2,'test@example.com'),(3,1,3,'0123456789'),(4,1,4,'IT01'),(5,1,5,'I want to learn coding and make new friends.'),(6,2,1,'Nguyen Van Dat'),(7,2,2,'datnv@example.com'),(8,2,3,'0987654321'),(9,2,4,'DI20V7A1'),(10,2,6,'https://facebook.com/datnv'),(11,2,5,'Em muon hoc lap trinh.'),(12,3,1,'Test Databaseless'),(13,3,2,'test@cit.org'),(14,3,3,'0987654321'),(15,3,4,'IT'),(16,3,6,'https://facebook.com/test'),(17,3,5,'Testing DB-less loading');
/*!40000 ALTER TABLE `form_submission_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_submissions`
--

DROP TABLE IF EXISTS `form_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `idx_submissions_status_date` (`status`,`submitted_at`),
  KEY `idx_submissions_date` (`submitted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_submissions`
--

LOCK TABLES `form_submissions` WRITE;
/*!40000 ALTER TABLE `form_submissions` DISABLE KEYS */;
INSERT INTO `form_submissions` VALUES (3,'2026-07-10 21:30:48','pending'),(1,'2026-07-10 11:17:37','approved'),(2,'2026-07-10 11:32:02','approved');
/*!40000 ALTER TABLE `form_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_batches`
--

DROP TABLE IF EXISTS `email_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  CONSTRAINT `fk_email_batches_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_deliveries`
--

DROP TABLE IF EXISTS `email_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_contents`
--

DROP TABLE IF EXISTS `page_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_contents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `content_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_key` (`content_key`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_contents`
--

LOCK TABLES `page_contents` WRITE;
/*!40000 ALTER TABLE `page_contents` DISABLE KEYS */;
INSERT INTO `page_contents` VALUES (1,'home_title','Cit','2026-07-10 11:29:34'),(2,'home_desc','Đây là điểm đến trực tuyến của fanpage chính thức CIT, nơi chúng tôi chọn lọc hình ảnh...','2026-07-10 10:03:46'),(3,'home_title_current','CIT Club — Cộng đồng công nghệ sinh viên TMU','2026-07-10 12:57:57'),(4,'home_desc_current','CIT là câu lạc bộ về lĩnh vực Công nghệ đầu tiên trực thuộc Đoàn TNCS Hồ Chí Minh Trường Đại học Thương mại, dưới sự quản lý của Khoa Công nghệ số ứng dụng. Website chọn lọc thông tin công khai từ fanpage để giới thiệu hoạt động, thành tích và hành trình tuyển thành viên của CLB.','2026-07-10 12:57:57'),(5,'theme_primary_color','#3b82f6','2026-07-10 14:43:29'),(6,'theme_secondary_color','#06b6d4','2026-07-10 14:43:29'),(7,'theme_accent_color','#f97316','2026-07-10 14:43:29'),(8,'site_logo','uploads/img_6a51093d92a1c0.73440216.webp','2026-07-10 15:01:17'),(9,'hero_bg','assets/images/cit/cit-cover.webp','2026-07-10 14:43:29'),(10,'hero_cta_text','Xem tuyển thành viên','2026-07-10 14:43:29'),(11,'hero_cta_url','recruitment.php','2026-07-10 14:43:29'),(12,'hero_explore_text','Khám phá fanpage','2026-07-10 14:43:29'),(13,'hero_explore_url','https://www.facebook.com/clbcongnghe.cit','2026-07-10 14:43:29'),(16,'recruitment_form_closed','0','2026-07-13 00:00:00'),(17,'recruitment_closed_message','CIT hiện đã đóng form tuyển thành viên. Hẹn gặp bạn ở đợt tuyển tiếp theo.','2026-07-13 00:00:00'),(18,'mail_sender_name','CLB Công nghệ CIT','2026-07-13 00:00:00'),(19,'mail_reply_to','','2026-07-13 00:00:00'),(20,'mail_approved_subject','Chúc mừng {{ho_ten}} đã trở thành thành viên CIT','2026-07-13 00:00:00'),(21,'mail_approved_body','<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Chúc mừng bạn đã vượt qua đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Chúng mình rất vui được đồng hành cùng bạn trong những hoạt động sắp tới.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>','2026-07-13 00:00:00'),(22,'mail_rejected_subject','Kết quả tuyển thành viên {{ten_clb}}','2026-07-13 00:00:00'),(23,'mail_rejected_body','<p>Xin chào <strong>{{ho_ten}}</strong>,</p><p>Cảm ơn bạn đã dành thời gian tham gia đợt tuyển thành viên của <strong>{{ten_clb}}</strong>.</p><p>Rất tiếc trong đợt này chúng mình chưa thể đồng hành cùng bạn. Hy vọng sẽ được gặp lại bạn trong những cơ hội tiếp theo.</p><p>Trân trọng,<br><strong>{{ten_clb}}</strong></p>','2026-07-13 00:00:00');
/*!40000 ALTER TABLE `page_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (1,'hero','Banner đầu trang (Hero)',1,1),(2,'highlights','Điểm nhấn từ fanpage (Highlights)',2,1),(3,'stats','Thanh thống kê (Stats)',3,1),(4,'about','Giới thiệu về chúng mình (About)',4,1),(5,'activities','Những khoảnh khắc đáng nhớ (Activities)',5,1),(6,'gallery','Album ảnh của CIT (Gallery)',6,1);
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-10 22:14:43
