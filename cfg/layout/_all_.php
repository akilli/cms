<?php
return [
    'html' => [
        'type' => 'html',
    ],
    'head' => [
        'type' => 'container',
        'parent_id' => 'html',
        'sort' => 100,
        'cfg' => [
            'tag' => 'head',
        ],
    ],
    'body' => [
        'type' => 'container',
        'parent_id' => 'html',
        'sort' => 200,
        'image' => ['sizes' => '100vw'],
        'cfg' => [
            'tag' => 'body',
        ],
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
        'type' => 'toolbar',
        'priv' => '_user_',
        'parent_id' => 'body',
        'sort' => 100,
    ],
    'top' => [
        'type' => 'container',
        'parent_id' => 'body',
        'sort' => 200,
    ],
    'main' => [
        'type' => 'container',
        'parent_id' => 'body',
        'sort' => 300,
        'cfg' => [
            'tag' => 'main',
        ],
    ],
    'bottom' => [
        'type' => 'container',
        'parent_id' => 'body',
        'sort' => 400,
    ],
    'content' => [
        'type' => 'container',
        'parent_id' => 'main',
        'sort' => 100,
        'cfg' => [
            'tag' => 'article',
        ],
    ],
    'sidebar' => [
        'type' => 'container',
        'parent_id' => 'main',
        'sort' => 200,
        'cfg' => [
            'tag' => 'aside',
        ],
    ],
    'title' => [
        'type' => 'title',
        'parent_id' => 'content',
        'sort' => 100,
    ],
    'msg' => [
        'type' => 'msg',
        'parent_id' => 'content',
        'sort' => 200,
    ],
];
