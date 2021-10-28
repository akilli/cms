<?php
declare(strict_types=1);

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
            'active' => false,
            'parent_id' => 'header',
            'sort' => 100,
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
        'main-content' => [
            'type' => 'tpl',
            'tpl' => 'error.phtml',
            'parent_id' => 'content',
            'sort' => 300,
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
        'main-content' => [
            'type' => 'login',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    'html:add' => [
        'main-content' => [
            'type' => 'form',
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
        'main-content' => [
            'type' => 'profile',
            'parent_id' => 'content',
            'sort' => 300,
            'cfg' => [
                'attr_id' => ['image', 'username', 'password', 'email'],
            ],
        ],
    ],
    'html:contentblock:index' => [
        'main-content' => [
            'cfg' => [
                'attr_id' => ['name', 'created'],
                'filter' => ['created'],
                'search' => ['name'],
            ],
        ],
    ],
    'html:edit' => [
        'main-content' => [
            'type' => 'form',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    'html:index' => [
        'main-content' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 300,
            'cfg' => [
                'action' => ['view', 'edit', 'delete'],
                'add' => true,
                'pager' => true,
                'sortable' => true,
                'table' => true,
            ],
        ],
    ],
    'html:menu:index' => [
        'main-content' => [
            'cfg' => [
                'attr_id' => ['name', 'url', 'position', 'parent_id', 'created'],
                'filter' => ['parent_id', 'created'],
                'search' => ['name'],
            ],
        ],
    ],
    'html:page:index' => [
        'main-content' => [
            'cfg' => [
                'attr_id' => ['name', 'url', 'created'],
                'filter' => ['created'],
                'search' => ['name'],
            ],
        ],
    ],
    'html:page:view' => [
        'main-content' => [
            'cfg' => [
                'attr_id' => ['content', 'aside'],
            ],
        ],
    ],
    'html:view' => [
        'body' => [
            'image' => [
                'sizes' => '100vw',
            ],
        ],
        'menu' => [
            'active' => true,
        ],
        'breadcrumb' => [
            'active' => true,
        ],
        'logo' => [
            'active' => true,
        ],
        'main-content' => [
            'type' => 'view',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
];
