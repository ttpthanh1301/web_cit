CREATE DATABASE IF NOT EXISTS club_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE club_management;

SET NAMES utf8mb4;

CREATE TABLE admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE form_fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    field_label VARCHAR(150) NOT NULL,
    field_name VARCHAR(64) NOT NULL UNIQUE,
    field_type ENUM('text', 'email', 'phone', 'textarea', 'dropdown', 'radio', 'checkbox') NOT NULL,
    options TEXT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX idx_form_fields_sort_order (sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE form_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    INDEX idx_submissions_status_date (status, submitted_at),
    INDEX idx_submissions_date (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE form_submission_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    field_id INT UNSIGNED NOT NULL,
    value TEXT NULL,
    UNIQUE KEY uq_submission_field (submission_id, field_id),
    INDEX idx_values_field (field_id),
    CONSTRAINT fk_values_submission
        FOREIGN KEY (submission_id) REFERENCES form_submissions(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_values_field
        FOREIGN KEY (field_id) REFERENCES form_fields(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin (username, password_hash) VALUES
('admin', '$2y$10$Y5HTyPQVbktoUBTOHntKGeGUX44.yYJzhkGGt5MF6NqAKFMnX2eRa');

INSERT INTO form_fields (field_label, field_name, field_type, options, is_required, sort_order) VALUES
('Họ và tên', 'ho_ten', 'text', NULL, 1, 1),
('Email', 'email', 'email', NULL, 1, 2),
('Số điện thoại', 'so_dien_thoai', 'phone', NULL, 1, 3),
('Lớp / Ngành', 'lop_nganh', 'text', NULL, 1, 4),
('Lý do tham gia', 'ly_do_tham_gia', 'textarea', NULL, 1, 5);
