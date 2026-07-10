<?php
declare(strict_types=1);

$albums = [
    'aeternum' => [
        'label' => 'Aeternum',
        'cover' => 'assets/images/cit/albums/aeternum-hackerrank.webp',
        'desc' => 'Cuộc thi lập trình AETERNUM HackerRank',
        'photos' => array_map(
            static fn (int $i): string => sprintf('assets/images/cit/albums/aeternum/aeternum-%02d.webp', $i),
            range(1, 14)
        ),
    ],
    'birthday' => [
        'label' => 'Birthday with CIT',
        'cover' => 'assets/images/cit/albums/birthday-with-cit.webp',
        'desc' => 'Sinh nhật cùng CLB Công nghệ CIT',
        'photos' => array_map(
            static fn (int $i): string => sprintf('assets/images/cit/albums/birthday-with-cit/birthday-with-cit-%02d.webp', $i),
            range(1, 12)
        ),
    ],
    'httt' => [
        'label' => 'HTTT',
        'cover' => 'assets/images/cit/albums/httt.webp',
        'desc' => 'Hội thảo Thông tin Thị trường Lao động',
        'photos' => array_map(
            static fn (int $i): string => sprintf('assets/images/cit/albums/httt/httt-%02d.webp', $i),
            range(1, 12)
        ),
    ],
    'vinh-danh' => [
        'label' => 'Vinh danh CIT',
        'cover' => 'assets/images/cit/albums/vinh-danh-cit.webp',
        'desc' => 'Lễ vinh danh thành viên CIT xuất sắc',
        'photos' => array_map(
            static fn (int $i): string => sprintf('assets/images/cit/albums/vinh-danh-cit/vinh-danh-cit-%02d.webp', $i),
            range(1, 8)
        ),
    ],
    'tan-cu-nhan' => [
        'label' => 'Tân cử nhân',
        'cover' => 'assets/images/cit/tan-cu-nhan-04.webp',
        'desc' => 'Chúc mừng các tân cử nhân CIT',
        'photos' => array_map(
            static fn (int $i): string => sprintf('assets/images/cit/albums/tan-cu-nhan/tan-cu-nhan-%02d.webp', $i),
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

$activities = [
    [
        'image' => 'assets/images/cit/albums/aeternum-hackerrank.webp',
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
        'width' => 843,
        'height' => 707,
        'alt' => 'Hội thảo thông tin thị trường lao động',
        'tag_icon' => 'bi-mic',
        'tag' => 'Hội thảo',
        'title' => 'Hội thảo Thị trường Lao động IT',
        'copy' => 'Chia sẻ xu hướng tuyển dụng ngành CNTT và định hướng nghề nghiệp cho sinh viên.',
        'loading' => 'eager',
    ],
    [
        'image' => 'assets/images/cit/albums/birthday-with-cit.webp',
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
        'width' => 590,
        'height' => 835,
        'alt' => 'Vinh danh thành viên CIT xuất sắc',
        'tag_icon' => 'bi-award',
        'tag' => 'Vinh danh',
        'title' => 'Lễ Vinh Danh CIT',
        'copy' => 'Tôn vinh những thành viên có đóng góp xuất sắc cho CLB và cộng đồng.',
        'loading' => 'lazy',
    ],
    [
        'image' => 'assets/images/cit/tan-cu-nhan-04.webp',
        'width' => 1108,
        'height' => 1478,
        'alt' => 'Chúc mừng các tân cử nhân',
        'tag_icon' => 'bi-mortarboard',
        'tag' => 'Tốt nghiệp',
        'title' => 'Chúc Mừng Tân Cử Nhân',
        'copy' => 'Chúc mừng và đưa tiễn các thành viên bước sang trang mới của cuộc đời.',
        'loading' => 'lazy',
    ],
];
