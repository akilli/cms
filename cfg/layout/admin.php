<?php
return [
    'new' => [
        'type' => 'tpl',
        'tpl' => 'new.phtml',
        'parent_id' => 'content',
        'sort' => 300,
    ],
    'index' => [
        'type' => 'index',
        'tpl' => 'index-admin.phtml',
        'parent_id' => 'content',
        'sort' => 400,
        'cfg' => [
            'pager' => 'bottom',
            'search' => ['name'],
            'sort' => true,
        ],
    ],
];
