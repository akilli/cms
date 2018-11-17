<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'tpl',
            'tpl' => 'root.phtml',
        ],
        'head' => [
            'type' => 'container',
        ],
        'meta' => [
            'type' => 'meta',
            'parent' => 'head',
            'sort' => 10,
        ],
        'asset' => [
            'type' => 'tpl',
            'tpl' => 'head/asset.phtml',
            'parent' => 'head',
            'sort' => 20,
        ],
        'asset-user' => [
            'type' => 'tpl',
            'tpl' => 'head/asset-user.phtml',
            'priv' => '_user_',
            'parent' => 'head',
            'sort' => 30,
        ],
        'asset-ext' => [
            'type' => 'tpl',
            'tpl' => 'head/asset-ext.phtml',
            'parent' => 'head',
            'sort' => 40,
        ],
        'toolbar' => [
            'type' => 'toolbar',
            'priv' => '_user_',
        ],
        'header' => [
            'type' => 'container',
            'vars' => [
                'tag' => 'header',
            ],
        ],
        'top' => [
            'type' => 'container',
        ],
        'msg' => [
            'type' => 'tpl',
            'tpl' => 'app/msg.phtml',
            'parent' => 'top',
            'sort' => 20,
        ],
        'left' => [
            'type' => 'container',
        ],
        'content' => [
            'type' => 'container',
            'vars' => [
                'tag' => 'article',
            ],
        ],
        'right' => [
            'type' => 'container',
            'vars' => [
                'tag' => 'aside',
            ],
        ],
        'bottom' => [
            'type' => 'container',
        ],
        'footer' => [
            'type' => 'container',
            'vars' => [
                'tag' => 'footer',
            ],
        ],
    ],
    '_public_' => [
        'header-logo' => [
            'type' => 'tpl',
            'tpl' => 'header/logo.phtml',
            'parent' => 'header',
            'sort' => 10,
        ],
        'menu' => [
            'type' => 'menu',
            'parent' => 'top',
            'sort' => 10,
            'vars' => [
                'sticky' => true,
                'toggle' => true,
            ],
        ],
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent' => 'left',
            'sort' => 10,
        ],
        'page-sidebar' => [
            'type' => 'sidebar',
            'parent' => 'right',
            'sort' => 10,
            'vars' => [
                'inherit' => true,
            ],
        ],
        'footer-default' => [
            'type' => 'tpl',
            'tpl' => 'footer/nav.phtml',
            'parent' => 'footer',
            'sort' => 10,
        ],
    ],
    '_error_' => [
        'content-main' => [
            'type' => 'tpl',
            'tpl' => 'app/error.phtml',
            'parent' => 'content',
            'sort' => 10,
        ],
    ],
    'admin' => [
        'content-main' => [
            'type' => 'index',
            'parent' => 'content',
            'sort' => 10,
            'vars' => [
                'action' => ['view', 'edit', 'delete'],
                'create' => true,
                'inaccessible' => true,
                'pager' => true,
                'search' => ['name'],
                'thead' => true,
                'title' => null,
            ],
        ],
    ],
    'browser' => [
        'toolbar' => [
            'active' => false,
        ],
        'content-main' => [
            'type' => 'index',
            'parent' => 'content',
            'sort' => 10,
            'vars' => [
                'action' => ['select'],
                'limit' => 20,
                'pager' => true,
                'search' => ['name'],
                'title' => null,
            ],
        ],
    ],
    'edit' => [
        'content-main' => [
            'type' => 'edit',
            'parent' => 'content',
            'sort' => 10,
        ],
    ],
    'view' => [
        'content-main' => [
            'type' => 'view',
            'parent' => 'content',
            'sort' => 10,
        ],
    ],
    'account/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'role_id'],
            ],
        ],
    ],
    'account/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id'],
            ],
        ],
    ],
    'account/login' => [
        'toolbar' => [
            'active' => false,
        ],
        'content-main' => [
            'type' => 'login',
            'parent' => 'content',
            'sort' => 10,
        ],
    ],
    'account/password' => [
        'content-main' => [
            'type' => 'password',
            'parent' => 'content',
            'sort' => 10,
        ],
    ],
    'article/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'parent_id', 'status', 'date'],
            ],
        ],
    ],
    'article/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    'article/view' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'image', 'teaser', 'main'],
            ],
        ],
    ],
    'block/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'title'],
            ],
        ],
    ],
    'block/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'title', 'content'],
            ],
        ],
    ],
    'content/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'pos', 'parent_id', 'menu', 'status', 'date'],
            ],
        ],
    ],
    'content/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => [
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status',
                    'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description'
                ],
            ],
        ],
    ],
    'content/view' => [
        'content-main' => [
            'vars' => [
                'attr' => ['image', 'name', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
    'file/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'file/browser' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'file/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'page/view' => [
        'content-main' => [
            'type' => 'page',
        ],
    ],
    'role/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name'],
            ],
        ],
    ],
    'role/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'priv'],
            ],
        ],
    ],
    '/' => [
        'breadcrumb' => [
            'active' => false,
        ],
        'content-main' => [
            'vars' => [
                'attr' => ['image', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
];
