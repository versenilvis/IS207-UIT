<?php
return [
    'free' => [
        'name'          => 'Miễn Phí',
        'price'         => 0,
        'period'        => 'tháng',
        'days'          => 0,
        'tier'          => 0,
        'repurchasable' => false,
    ],
    'pro' => [
        'name'          => 'Premium',
        'price'         => 69000,
        'period'        => 'tháng',
        'days'          => 30,
        'tier'          => 1,
        'repurchasable' => false,
    ],
    'pro_year' => [
        'name'          => 'Premium Năm',
        'price'         => 588000, 
        'period'        => 'năm',
        'days'          => 365,
        'tier'          => 1,
        'repurchasable' => false,
    ],
    'course' => [
        'name'          => 'Khoá Học',
        'price'         => 249000,
        'period'        => 'vĩnh viễn',
        'days'          => 36500, // lifetime
        'tier'          => 1, // Same tier level as pro, but different track
        'is_premium'    => false,
        'has_course'    => true,
        'repurchasable' => false,
    ],
    'ultra' => [
        'name'          => 'Trọn Bộ',
        'price'         => 289000,
        'period'        => 'tháng',
        'days'          => 30, // premium tháng đi kèm khoá học
        'tier'          => 2,
        'has_course'    => true,
        'repurchasable' => false,
    ],
    'ultra_year' => [
        'name'          => 'Trọn Bộ Năm',
        'price'         => 749000,
        'period'        => 'năm',
        'days'          => 365,
        'tier'          => 2,
        'has_course'    => true,
        'repurchasable' => false,
    ],
];