<?php
declare(strict_types=1);

return [
    'header' => [
        'cfg' => [
            'title' => 'Error',
        ],
    ],
    'main-content' => [
        'type' => 'tpl',
        'tpl' => 'error.phtml',
        'parent_id' => 'content',
        'sort' => 20,
    ],
];
