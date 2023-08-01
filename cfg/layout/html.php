<?php
declare(strict_types=1);

return [
    'html' => [
        'type' => 'tpl',
        'tpl' => 'html.phtml',
    ],
    'head' => [
        'type' => 'head',
        'parent_id' => 'html',
    ],
    'body' => [
        'type' => 'container',
        'parent_id' => 'html',
    ],
    'toolbar' => [
        'type' => 'menu',
        'privilege' => '_user_',
        'parent_id' => 'body',
        'sort' => 100,
        'cfg' => [
            'id' => 'toolbar',
        ],
    ],
    'header' => [
        'type' => 'header',
        'parent_id' => 'body',
        'sort' => 200,
    ],
    'menu' => [
        'type' => 'menu',
        'active' => false,
        'parent_id' => 'body',
        'sort' => 300,
    ],
    'breadcrumb' => [
        'type' => 'breadcrumb',
        'active' => false,
        'parent_id' => 'body',
        'sort' => 400,
    ],
    'main' => [
        'type' => 'container',
        'tag' => 'main',
        'parent_id' => 'body',
        'sort' => 500,
    ],
    'content' => [
        'type' => 'container',
        'tag' => 'article',
        'parent_id' => 'main',
        'sort' => 100,
        'cfg' => [
            'id' => true,
        ],
    ],
    'sidebar' => [
        'type' => 'container',
        'tag' => 'aside',
        'parent_id' => 'main',
        'sort' => 200,
        'cfg' => [
            'id' => true,
        ],
    ],
    'msg' => [
        'type' => 'tag',
        'tag' => 'app-msg',
        'parent_id' => 'content',
        'sort' => 200,
    ],
];
