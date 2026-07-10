# CIT Club Recruitment

Website tuyển thành viên CLB viết bằng PHP thuần, PDO, MySQL, Bootstrap 5 và PhpSpreadsheet.

## Yêu cầu

- PHP 8.1 trở lên với các extension `pdo_mysql`, `mbstring`, `xml`, `zip`, `gd`
- MySQL 5.7+/MariaDB tương thích
- Composer 2

## Cài đặt

1. Chạy `composer install --no-dev --optimize-autoloader`.
2. Import file `database.sql` vào MySQL.
3. Sao chép/cấu hình file `.env` với các biến `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`; biến môi trường hệ thống (nếu có) được ưu tiên hơn file này.
4. Trỏ document root của website vào thư mục dự án và bảo đảm PHP có quyền tạo session.
5. Đăng nhập tại `/admin/login.php` bằng tài khoản mẫu `admin` / `admin123`, sau đó đổi mật khẩu hash trong database trước khi đưa lên môi trường thật.

Không commit file chứa thông tin đăng nhập thật. Thư mục `vendor/` được tạo lại bằng Composer và đã nằm trong `.gitignore`.

## Cấu hình Tối ưu cho Shared Hosting (OPcache)

Để website phản hồi nhanh nhất và tiết kiệm RAM/CPU trên shared hosting (ví dụ: cPanel):
1. Đăng nhập vào cPanel của bạn.
2. Tìm và chọn mục **Select PHP Version**.
3. Chuyển sang tab **Extensions**.
4. Tìm extension **opcache** và tích chọn để bật nó lên.
5. (Tùy chọn) Nếu có quyền cấu hình `php.ini`, thêm các thông số sau để tối ưu bộ nhớ đệm:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=64
   opcache.interned_strings_buffer=8
   opcache.max_accelerated_files=2000
   opcache.validate_timestamps=1
   opcache.revalidate_freq=2
   ```

