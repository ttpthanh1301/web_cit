<?php
declare(strict_types=1);

function image_variant(string $fullPath, int $fullWidth): array
{
    return [
        'full' => $fullPath,
        'thumb' => str_replace('assets/images/cit/albums/', 'assets/images/cit/thumbs/albums/', $fullPath),
        'width' => $fullWidth,
    ];
}

$albums = [
    'aeternum' => [
        'label' => 'Aeternum',
        'cover' => 'assets/images/cit/albums/aeternum-hackerrank.webp',
        'desc' => 'Cuộc thi lập trình AETERNUM HackerRank',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/aeternum/aeternum-%02d.webp', $i), 843),
            range(1, 14)
        ),
    ],
    'birthday' => [
        'label' => 'Birthday with CIT',
        'cover' => 'assets/images/cit/albums/birthday-with-cit.webp',
        'desc' => 'Sinh nhật cùng CLB Công nghệ CIT',
        'photos' => array_map(
            static fn (int $i): array => image_variant(
                sprintf('assets/images/cit/albums/birthday-with-cit/birthday-with-cit-%02d.webp', $i),
                match ($i) {
                    1, 2, 3, 4, 5 => 590,
                    7, 8, 9, 10 => 720,
                    11 => 640,
                    default => 843,
                }
            ),
            range(1, 12)
        ),
    ],
    'httt' => [
        'label' => 'Collab',
        'cover' => 'assets/images/cit/albums/httt.webp',
        'desc' => 'Colab cùng các CLB và bên liên quan',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/httt/httt-%02d.webp', $i), 843),
            range(1, 6)
        ),
    ],
    'vinh-danh' => [
        'label' => 'Teambuilding',
        'cover' => 'assets/images/cit/albums/vinh-danh-cit.webp',
        'desc' => 'Hoạt động Teambuilding gắn kết thành viên CIT',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/vinh-danh-cit/vinh-danh-cit-%02d.webp', $i), 843),
            range(1, 8)
        ),
    ],
    'tan-cu-nhan' => [
        'label' => 'Tân cử nhân',
        'cover' => 'assets/images/cit/tan-cu-nhan-04.webp',
        'desc' => 'Chúc mừng các tân cử nhân CIT',
        'photos' => array_map(
            static fn (int $i): array => image_variant(sprintf('assets/images/cit/albums/tan-cu-nhan/tan-cu-nhan-%02d.webp', $i), 1000),
            range(1, 5)
        ),
    ],
];

$features = [
    [
        'number' => '01',
        'icon' => 'bi-book-half',
        'title' => 'Học hỏi',
        'copy' => 'Workshop, seminar và những buổi chia sẻ kiến thức thực tế từ chính các thành viên và khách mời.',
    ],
    [
        'number' => '02',
        'icon' => 'bi-code-slash',
        'title' => 'Thực chiến',
        'copy' => 'Cùng xây dự án, tham gia cuộc thi lập trình và biến ý tưởng thành sản phẩm có tác động thực tế.',
    ],
    [
        'number' => '03',
        'icon' => 'bi-people-fill',
        'title' => 'Kết nối',
        'copy' => 'Gặp gỡ những người bạn cùng đam mê, mở rộng mạng lưới và xây dựng nền tảng nghề nghiệp vững chắc.',
    ],
];

$achievements = [
    [
        'icon' => 'bi-trophy-fill',
        'title' => "Giải Nhất TMU's Startup 2025",
        'copy' => 'Thành viên CIT được fanpage chúc mừng vì xuất sắc giành Giải Nhất tại TMU\'s Startup 2025.',
        'image' => 'assets/images/cit/albums/nckh/giainhat_tmu.webp',
    ],
    [
        'icon' => 'bi-journal-check',
        'title' => 'Giải cao NCKH cấp Khoa',
        'copy' => 'Các thành viên CIT đạt giải cao trong hoạt động nghiên cứu khoa học cấp Khoa.',
        'source' => 'https://www.facebook.com/photo/?fbid=850552041390716&set=pcb.850527074726546',
    ],
    [
        'icon' => 'bi-code-square',
        'title' => 'HackerRank nội bộ 2026',
        'copy' => 'Cuộc thi thuật toán nội bộ giúp thành viên rèn tư duy lập trình và giải quyết vấn đề.',
        'source' => 'https://www.facebook.com/photo/?fbid=873520835760503&set=pcb.873540535758533',
    ],
];

$featuredReels = [
    [
        'title' => 'Reel nổi bật',
        'views' => 'khoảng 25K lượt xem',
        'url' => 'https://www.facebook.com/reel/1903048183715092/',
    ],
    [
        'title' => 'Khoảnh khắc hoạt động',
        'views' => 'khoảng 5,8K lượt xem',
        'url' => 'https://www.facebook.com/reel/2039372770790649/',
    ],
    [
        'title' => 'Câu chuyện CIT',
        'views' => 'khoảng 3,8K lượt xem',
        'url' => 'https://www.facebook.com/reel/998861632561849/',
    ],
];

$activities = [
    [
        'image' => 'assets/images/cit/albums/aeternum-hackerrank.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/aeternum-hackerrank.webp',
        'width' => 843,
        'height' => 674,
        'alt' => 'Cuộc thi lập trình AETERNUM HackerRank',
        'tag_icon' => 'bi-trophy',
        'tag' => 'Thi đấu',
        'title' => 'AETERNUM - HackerRank Challenge',
        'copy' => 'Cuộc thi lập trình tranh tài về thuật toán và tư duy giải quyết vấn đề.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/httt.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/httt.webp',
        'width' => 843,
        'height' => 707,
        'alt' => 'Colab cùng các CLB và bên liên quan',
        'tag_icon' => 'bi-people',
        'tag' => 'Hợp tác',
        'title' => 'Colab cùng các CLB và bên liên quan',
        'copy' => 'Hoạt động giao lưu, hợp tác và phối hợp tổ chức cùng các câu lạc bộ và các bên liên quan.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/birthday-with-cit.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/birthday-with-cit.webp',
        'width' => 590,
        'height' => 835,
        'alt' => 'Sinh nhật cùng CLB CIT',
        'tag_icon' => 'bi-balloon-heart',
        'tag' => 'Gắn kết',
        'title' => 'Birthday With CIT',
        'copy' => 'Những buổi sinh nhật ấm áp, gắn kết mọi thành viên trong gia đình CIT.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/vinh-danh-cit.webp',
        'thumb' => 'assets/images/cit/thumbs/albums/vinh-danh-cit.webp',
        'width' => 590,
        'height' => 835,
        'alt' => 'Hoạt động Teambuilding của CIT',
        'tag_icon' => 'bi-people-fill',
        'tag' => 'Teambuilding',
        'title' => 'Teambuilding CIT',
        'copy' => 'Các hoạt động teambuilding và giao lưu dã ngoại giúp thắt chặt tình đoàn kết giữa các thành viên.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/tan-cu-nhan-04.webp',
        'thumb' => 'assets/images/cit/thumbs/tan-cu-nhan-04.webp',
        'width' => 1108,
        'height' => 1478,
        'alt' => 'Chúc mừng các tân cử nhân',
        'tag_icon' => 'bi-mortarboard',
        'tag' => 'Tốt nghiệp',
        'title' => 'Chúc Mừng Tân Cử Nhân',
        'copy' => 'Chúc mừng và đưa tiễn các thành viên bước sang trang mới của cuộc đời.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/albums/club-partner/2nam_cit.jpg',
        'thumb' => 'assets/images/cit/thumbs/albums/club-partner/2nam_cit.webp',
        'width' => 2048,
        'height' => 1365,
        'alt' => 'Khoảnh khắc tập thể tại lễ kỷ niệm 2 năm thành lập CIT',
        'tag_icon' => 'bi-calendar-heart',
        'tag' => 'Kỷ niệm',
        'title' => 'AETERNUM - Kỷ Niệm 2 Năm CIT',
        'copy' => 'Khoảnh khắc đáng nhớ đánh dấu hành trình 2 năm phát triển, gắn kết và trưởng thành của đại gia đình CIT.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/albums/club-partner/lowcode.jpg',
        'thumb' => 'assets/images/cit/albums/club-partner/lowcode.jpg',
        'width' => 850,
        'height' => 850,
        'alt' => 'Hoạt động học thuật Low-code 1C Enterprise',
        'tag_icon' => 'bi-lightning-charge',
        'tag' => 'Low-code',
        'title' => 'Low-code 1C: Enterprise',
        'copy' => 'Cuộc thi phát triển ý tưởng kinh doanh dựa trên nền tảng công nghệ Low-code 1C: Enterprise.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/albums/nckh/giainhat_tmu.webp',
        'thumb' => 'assets/images/cit/albums/nckh/giainhat_tmu.webp',
        'width' => 1080,
        'height' => 1080,
        'alt' => "Thành viên CIT đạt thành tích TMU's Startup 2025",
        'tag_icon' => 'bi-trophy-fill',
        'tag' => 'Thành tích',
        'title' => "TMU's Startup 2025",
        'copy' => 'Thành viên CIT xuất sắc giành Giải Nhất tại TMU\'s Startup 2025 theo bài chúc mừng công khai.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/albums/club-partner/nckh.jpg',
        'thumb' => 'assets/images/cit/albums/club-partner/nckh.jpg',
        'width' => 1108,
        'height' => 1478,
        'alt' => 'Thành viên CIT đạt giải cao NCKH cấp Khoa',
        'tag_icon' => 'bi-journal-check',
        'tag' => 'NCKH',
        'title' => 'Giải cao NCKH cấp Khoa',
        'copy' => 'Các thành viên CIT được fanpage chúc mừng vì đạt giải cao trong nghiên cứu khoa học cấp Khoa.',
        'loading' => 'lazy',
    ],
];
