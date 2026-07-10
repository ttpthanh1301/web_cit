# Shared Hosting Checklist

Checklist deploy cho gói hosting nhỏ 2GB SSD, 512MB RAM, 1 CPU.

## Upload

- Upload source PHP, `assets/`, `admin/`, `includes/`, `vendor/` nếu dùng export Excel.
- Không upload ảnh nháp trong `utils/img/` nếu không được trang public/admin reference.
- Không đặt `.env`, `database.sql`, `composer.json`, `composer.lock`, `agent.md`, `data/*.json`, `data/*.md` ở thư mục public nếu hosting không áp dụng `.htaccess`.

## PHP

- Bật OPcache trong cPanel nếu hosting hỗ trợ.
- Gợi ý cấu hình an toàn cho shared hosting:
  - `opcache.enable=1`
  - `opcache.memory_consumption=64`
  - `opcache.max_accelerated_files=4000`
  - `opcache.validate_timestamps=1`
  - `opcache.revalidate_freq=60`
- Giữ `memory_limit` khoảng `128M`; export Excel đã giới hạn 2.000 đơn/lần để tránh vượt RAM.

## HTTP Cache

- `.htaccess` đã cache dài hạn cho ảnh/CSS/JS và bật gzip qua `mod_deflate` nếu server hỗ trợ.
- PHP public page tự set `Cache-Control: public, max-age=300, stale-while-revalidate=600`.
- Khi thay CSS/JS mà trình duyệt vẫn giữ cache cũ, đổi tên file hoặc thêm query version khi include asset.

## Database

- Import `database.sql` một lần khi setup.
- Giữ pagination 20 dòng ở admin.
- Khi dữ liệu vượt khoảng 5.000-10.000 đơn, ưu tiên lọc theo ngày/trạng thái trước khi search text.
