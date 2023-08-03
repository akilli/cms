<?php
declare(strict_types=1);

return [
    'header' => [
        'cfg' => [
            'title' => 'Maintenance',
        ],
    ],
    'main-content' => [
        'type' => 'tpl',
        'tpl' => 'maintenance.phtml',
        'parent_id' => 'content',
        'sort' => 20,
    ],
];
