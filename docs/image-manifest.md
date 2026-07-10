# Image Manifest

Manifest này dùng cho deploy shared hosting: chỉ upload ảnh đang được source reference, tránh tốn SSD 2GB cho ảnh nháp.

| Path | Kích thước | Trạng thái | Khuyến nghị |
|---|---:|---|---|
| `assets/images/cit/cit-cover.webp` | 1000x370 | Đang dùng làm hero/LCP | Giữ; preload trong `index.php`. Nếu thay ảnh hero, xuất WebP 1200-1600px ngang. |
| `assets/images/cit/logoclb.png` | logo nhỏ | Đang dùng header/footer/login | Giữ; đã khai báo width/height. Có thể chuyển WebP nếu logo không cần alpha PNG. |
| `assets/images/cit/albums/*.webp` | 590-843px cạnh dài phổ biến | Đang dùng activity/gallery | Giữ WebP; đã lazy load gallery và thêm kích thước ổn định. |
| `assets/images/cit/thumbs/**/*.webp` | 480px ngang | Đang dùng làm thumbnail cho activity/gallery | Bắt buộc upload cùng ảnh full; lightbox vẫn dùng ảnh full trong `assets/images/cit/albums/`. |
| `assets/images/favicon.png` | 1.4KB | Đang dùng | Giữ. |
| `assets/images/apple-touch-icon.png` | 1.4KB | Đang dùng | Giữ. |
| `utils/img/588759490_756723144106940_5000757703198795694_n.jpg` | 321KB | Không thấy reference trong PHP/CSS/JS | Không upload lên hosting nếu không cần nội dung này. |
| `utils/img/658357424_857200190725901_2803081843781456827_n.jpg` | 660KB | Không thấy reference trong PHP/CSS/JS | Không upload lên hosting; nếu cần dùng, resize 800px và convert WebP. |
| `utils/img/485719623_557103754068881_8226761970738418247_n.jpg` | 93KB | Không thấy reference trong PHP/CSS/JS | Không upload lên hosting nếu không cần nội dung này. |

Ước tính tiết kiệm khi loại `utils/img/*.jpg` khỏi deploy: khoảng 1.07MB. Nếu vẫn cần dùng các ảnh JPG này, resize + WebP thường giảm thêm 50-80% tùy chất lượng ảnh.
