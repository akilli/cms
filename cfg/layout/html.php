<?php
declare(strict_types=1);

return [
    'html' => [
        'type' => 'html',
    ],
    'head' => [
        'type' => 'container',
        'tag' => 'head',
        'parent_id' => 'html',
        'sort' => 100,
    ],
    'body' => [
        'type' => 'container',
        'tag' => 'body',
        'parent_id' => 'html',
        'sort' => 200,
    ],
    'meta' => [
        'type' => 'meta',
        'parent_id' => 'head',
        'sort' => 100,
    ],
    'icon' => [
        'type' => 'tpl',
        'tpl' => 'icon.phtml',
        'parent_id' => 'head',
        'sort' => 200,
    ],
    'asset' => [
        'type' => 'tpl',
        'tpl' => 'asset.phtml',
        'parent_id' => 'head',
        'sort' => 300,
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
        'type' => 'container',
        'tag' => 'header',
        'parent_id' => 'body',
        'sort' => 200,
        'cfg' => [
            'id' => true,
        ],
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
    'footer' => [
        'type' => 'container',
        'tag' => 'footer',
        'parent_id' => 'body',
        'sort' => 600,
        'cfg' => [
            'id' => true,
        ],
    ],
    'logo' => [
        'type' => 'tpl',
        'tpl' => 'logo.phtml',
        'parent_id' => 'header',
        'sort' => 100,
    ],
    'title' => [
        'type' => 'title',
        'parent_id' => 'header',
        'sort' => 200,
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
