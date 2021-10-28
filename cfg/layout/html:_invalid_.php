<?php
declare(strict_types=1);

return [
    'title' => [
        'cfg' => [
            'text' => 'Error',
        ],
    ],
    'main-content' => [
        'type' => 'tpl',
        'tpl' => 'error.phtml',
        'parent_id' => 'content',
        'sort' => 300,
    ],
];
