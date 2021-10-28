<?php
declare(strict_types=1);

return [
    'title' => [
        'cfg' => [
            'text' => 'Profile',
        ],
    ],
    'main-content' => [
        'type' => 'profile',
        'parent_id' => 'content',
        'sort' => 300,
        'cfg' => [
            'attr_id' => ['image', 'username', 'password', 'email'],
        ],
    ],
];
