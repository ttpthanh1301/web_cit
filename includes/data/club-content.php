<?php
declare(strict_types=1);

$clubValues = [
    [
        'icon' => 'bi-book-half',
        'title' => 'Học hỏi',
        'copy' => 'Cùng chia sẻ kiến thức, học từ trải nghiệm thực tế và giữ tinh thần chủ động trong mỗi buổi sinh hoạt.',
    ],
    [
        'icon' => 'bi-code-slash',
        'title' => 'Thực chiến',
        'copy' => 'Rèn luyện qua cuộc thi nội bộ, workshop, dự án và những bài toán gần với môi trường công nghệ.',
    ],
    [
        'icon' => 'bi-people-fill',
        'title' => 'Kết nối',
        'copy' => 'Gặp gỡ bạn bè cùng đam mê, mở rộng quan hệ học tập và cùng nhau phát triển kỹ năng nghề nghiệp.',
    ],
    [
        'icon' => 'bi-heart-fill',
        'title' => 'Gắn kết',
        'copy' => 'Duy trì văn hóa quan tâm, vinh danh đóng góp và lan tỏa tinh thần đồng hành giữa các thế hệ CIT.',
    ],
];

$clubTimeline = [
    [
        'label' => 'Nền tảng',
        'title' => 'Cộng đồng sinh viên yêu công nghệ',
        'copy' => 'CIT được xây dựng như một môi trường để sinh viên yêu thích công nghệ học hỏi, thực hành và kết nối.',
    ],
    [
        'label' => 'Dấu mốc',
        'title' => 'AETERNUM - kỉ niệm 2 năm thành lập CIT',
        'copy' => 'Album AETERNUM trên fanpage ghi lại hành trình phát triển, hoạt động chuyên môn và tinh thần gắn kết của câu lạc bộ.',
    ],
    [
        'label' => 'Tiếp nối',
        'title' => 'Vinh danh, kế thừa và phát triển',
        'copy' => 'Các hoạt động vinh danh thành viên và chúc mừng tân cử nhân thể hiện sự trân trọng đóng góp của nhiều thế hệ CIT.',
    ],
];

$clubTeams = [
    [
        'icon' => 'bi-diagram-3',
        'title' => 'Ban chủ nhiệm',
        'copy' => 'Định hướng hoạt động, kết nối các ban và giữ nhịp vận hành chung của câu lạc bộ.',
    ],
    [
        'icon' => 'bi-terminal',
        'title' => 'Ban chuyên môn',
        'copy' => 'Tổ chức nội dung học thuật, cuộc thi lập trình, workshop và hoạt động rèn luyện kỹ năng công nghệ.',
    ],
    [
        'icon' => 'bi-megaphone',
        'title' => 'Ban truyền thông',
        'copy' => 'Xây dựng hình ảnh CIT, ghi lại hoạt động và lan tỏa thông tin tới cộng đồng sinh viên.',
    ],
    [
        'icon' => 'bi-calendar-event',
        'title' => 'Ban sự kiện',
        'copy' => 'Chuẩn bị hậu cần, điều phối chương trình và tạo trải nghiệm gắn kết trong các hoạt động CLB.',
    ],
];

$clubActivityGroups = [
    [
        'group' => 'Chuyên môn',
        'intro' => 'Các hoạt động giúp thành viên rèn luyện tư duy công nghệ, kỹ năng giải quyết vấn đề và định hướng nghề nghiệp.',
        'items' => [
            [
                'title' => 'HackerRank nội bộ',
                'tag' => 'Thi đấu',
                'icon' => 'bi-trophy',
                'copy' => 'Cuộc thi nội bộ giúp thành viên rèn luyện thuật toán, tư duy thử thách và tinh thần học hỏi trong cộng đồng công nghệ.',
                'image' => 'assets/images/cit/thumbs/albums/aeternum-hackerrank.webp',
                'alt' => 'Cuộc thi nội bộ HackerRank của CIT',
                'gallery' => 'index.php?album=aeternum#gallery',
            ],
            [
                'title' => 'HTTT',
                'tag' => 'Học thuật',
                'icon' => 'bi-mic',
                'copy' => 'Hoạt động chuyên môn về hệ thống thông tin, giúp sinh viên tiếp cận kiến thức công nghệ theo cách gần gũi và thực tế.',
                'image' => 'assets/images/cit/thumbs/albums/httt.webp',
                'alt' => 'Hoạt động HTTT của CIT',
                'gallery' => 'index.php?album=httt#gallery',
            ],
        ],
    ],
    [
        'group' => 'Văn hóa CLB',
        'intro' => 'Những hoạt động giữ lửa tinh thần, ghi nhận đóng góp và tạo cảm giác thuộc về trong đại gia đình CIT.',
        'items' => [
            [
                'title' => 'Birthday With CIT',
                'tag' => 'Gắn kết',
                'icon' => 'bi-balloon-heart',
                'copy' => 'Các khoảnh khắc chúc mừng sinh nhật thành viên, thể hiện sự quan tâm, thân thiện và văn hóa gắn kết nội bộ.',
                'image' => 'assets/images/cit/thumbs/albums/birthday-with-cit.webp',
                'alt' => 'Birthday With CIT',
                'gallery' => 'index.php?album=birthday#gallery',
            ],
            [
                'title' => 'Vinh danh CIT',
                'tag' => 'Vinh danh',
                'icon' => 'bi-award',
                'copy' => 'Nơi ghi nhận dấu ấn, nỗ lực và đóng góp của các thành viên trong quá trình xây dựng, duy trì và phát triển câu lạc bộ.',
                'image' => 'assets/images/cit/thumbs/albums/vinh-danh-cit.webp',
                'alt' => 'Vinh danh thành viên CIT',
                'gallery' => 'index.php?album=vinh-danh#gallery',
            ],
        ],
    ],
    [
        'group' => 'Câu chuyện thành viên',
        'intro' => 'Những câu chuyện kế thừa giúp thành viên mới hiểu hơn về hành trình và tinh thần của các thế hệ đi trước.',
        'items' => [
            [
                'title' => 'Chúc mừng các tân cử nhân',
                'tag' => 'Thành viên',
                'icon' => 'bi-mortarboard',
                'copy' => 'CIT gửi lời chúc mừng tới các anh chị tân cử nhân, tri ân đóng góp và lan tỏa tinh thần đoàn kết, không ngừng học hỏi.',
                'image' => 'assets/images/cit/thumbs/tan-cu-nhan-04.webp',
                'alt' => 'Chúc mừng các tân cử nhân CIT',
                'gallery' => 'index.php?album=tan-cu-nhan#gallery',
            ],
        ],
    ],
];
