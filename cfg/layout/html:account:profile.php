<?php
declare(strict_types=1);

return [
    'header' => [
        'cfg' => [
            'title' => 'Profile',
        ],
    ],
    'main-content' => [
        'type' => 'profile',
        'parent_id' => 'content',
        'sort' => 100,
        'cfg' => [
            'attr_id' => ['image', 'username', 'password', 'email'],
        ],
    ],
];
