<?php
declare(strict_types=1);

return [
    'toolbar' => [
        'active' => false,
    ],
    'header' => [
        'active' => false,
    ],
    'title' => [
        'parent_id' => 'content',
        'sort' => 100,
        'cfg' => [
            'text' => 'Login',
        ],
    ],
    'main-content' => [
        'type' => 'login',
        'parent_id' => 'content',
        'sort' => 300,
    ],
];
