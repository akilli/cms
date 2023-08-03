<?php
declare(strict_types=1);

return [
    'html' => [
        'type' => 'tpl',
        'tpl' => 'html.phtml',
    ],
    'head' => [
        'type' => 'head',
    ],
    'toolbar' => [
        'type' => 'menu',
        'privilege' => '_user_',
        'cfg' => [
            'id' => 'toolbar',
        ],
    ],
    'header' => [
        'type' => 'header',
    ],
    'menu' => [
        'type' => 'menu',
        'active' => false,
    ],
    'breadcrumb' => [
        'type' => 'breadcrumb',
        'active' => false,
    ],
    'content' => [
        'type' => 'container',
        'tag' => 'article',
        'cfg' => [
            'id' => true,
        ],
    ],
    'sidebar' => [
        'type' => 'container',
        'tag' => 'aside',
        'cfg' => [
            'id' => true,
        ],
    ],
    'msg' => [
        'type' => 'tag',
        'tag' => 'app-msg',
        'parent_id' => 'content',
        'sort' => 10,
    ],
];
