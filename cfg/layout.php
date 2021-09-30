<?php
return [
    'html' => [
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
            'image' => [
                'sizes' => '100vw',
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
            'privilege' => '_user_',
            'parent_id' => 'body',
            'sort' => 100,
        ],
        'main' => [
            'type' => 'container',
            'tag' => 'main',
            'parent_id' => 'body',
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
        'title' => [
            'type' => 'title',
            'parent_id' => 'content',
            'sort' => 100,
        ],
        'msg' => [
            'type' => 'tag',
            'tag' => 'app-msg',
            'parent_id' => 'content',
            'sort' => 200,
        ],
    ],
    'html:_invalid_' => [
        'title' => [
            'cfg' => [
                'text' => 'Error',
            ],
        ],
        'view' => [
            'type' => 'tpl',
            'tpl' => 'error.phtml',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    'html:_public_' => [
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
        ],
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent_id' => 'main',
            'sort' => 50,
        ],
    ],
    'html:account:login' => [
        'toolbar' => [
            'active' => false,
        ],
        'title' => [
            'cfg' => [
                'text' => 'Login',
            ],
        ],
        'login' => [
            'type' => 'login',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    'html:account:profile' => [
        'title' => [
            'cfg' => [
                'text' => 'Profile',
            ],
        ],
        'profile' => [
            'type' => 'profile',
            'parent_id' => 'content',
            'sort' => 300,
            'cfg' => [
                'attr_id' => ['image', 'username', 'password', 'email'],
            ],
        ],
    ],
    'html:edit' => [
        'edit' => [
            'type' => 'edit',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    'html:index' => [
        'new' => [
            'type' => 'tpl',
            'tpl' => 'new.phtml',
            'parent_id' => 'content',
            'sort' => 300,
        ],
        'index' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 400,
            'cfg' => [
                'action' => ['view', 'edit', 'delete'],
                'pager' => true,
                'sortable' => true,
                'table' => true,
            ],
        ],
    ],
    'html:menu:index' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'url', 'position', 'parent_id', 'created'],
                'filter' => ['parent_id', 'created'],
                'search' => ['name'],
            ],
        ],
    ],
    'html:page:index' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'url', 'created'],
                'filter' => ['created'],
                'search' => ['name'],
            ],
        ],
    ],
    'html:page:view' => [
        'view' => [
            'cfg' => [
                'attr_id' => ['content', 'aside'],
            ],
        ],
    ],
    'html:view' => [
        'view' => [
            'type' => 'view',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
];
