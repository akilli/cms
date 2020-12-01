<?php
return [
    'header' => [
        'type' => 'tpl',
        'tpl' => 'header.phtml',
        'parent_id' => 'body',
        'sort' => 140,
    ],
    'menu' => [
        'type' => 'menu',
        'parent_id' => 'body',
        'sort' => 160,
        'cfg' => [
            'toggle' => true,
        ],
    ],
    'breadcrumb' => [
        'type' => 'breadcrumb',
        'parent_id' => 'main',
        'sort' => 50,
    ],
    'footer' => [
        'type' => 'tpl',
        'tpl' => 'footer.phtml',
        'parent_id' => 'body',
        'sort' => 300,
    ],
];
