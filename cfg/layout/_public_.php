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
    'footer' => [
        'type' => 'tpl',
        'tpl' => 'footer.phtml',
        'parent_id' => 'body',
        'sort' => 500,
    ],
];
