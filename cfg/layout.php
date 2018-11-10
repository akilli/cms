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
            'tpl' => 'meta.phtml',
            'parent' => 'head',
            'sort' => 10,
        ],
        'asset' => [
            'type' => 'tpl',
            'tpl' => 'asset.phtml',
            'parent' => 'head',
            'sort' => 20,
        ],
        'asset-user' => [
            'type' => 'tpl',
            'tpl' => 'asset-user.phtml',
            'priv' => '_user_',
            'parent' => 'head',
            'sort' => 30,
        ],
        'toolbar' => [
            'type' => 'toolbar',
            'priv' => '_user_',
        ],
        'header' => [
            'type' => 'tpl',
            'tpl' => 'header.phtml',
        ],
        'menu' => [
            'type' => 'menu',
            'vars' => [
                'sticky' => true,
                'toggle' => true,
            ],
        ],
        'menu-top' => [
            'type' => 'container',
        ],
        'menu-middle' => [
            'type' => 'container',
        ],
        'menu-bottom' => [
            'type' => 'container',
        ],
        'msg' => [
            'type' => 'tpl',
            'tpl' => 'msg.phtml',
        ],
        'main' => [
            'type' => 'container',
        ],
        'sidebar' => [
            'type' => 'container',
            'vars' => [
                'tag' => 'aside',
            ],
        ],
        'bottom' => [
            'type' => 'container',
        ],
    ],
    '_admin_' => [
        'header' => [
            'active' => false,
        ],
        'menu' => [
            'active' => false,
        ],
        'sidebar' => [
            'active' => false,
        ],
    ],
    '_public_' => [
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent' => 'main',
            'sort' => 10,
        ],
        'sidebar-page' => [
            'type' => 'sidebar',
            'parent' => 'sidebar',
            'sort' => 10,
            'vars' => [
                'inherit' => true,
            ],
        ],
    ],
    '_error_' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'error.phtml',
            'parent' => 'main',
            'sort' => 20,
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
            'parent' => 'main',
            'sort' => 20,
            'vars' => [
                'actions' => ['view', 'edit', 'delete'],
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
        'bottom' => [
            'active' => false,
        ],
        'content' => [
            'type' => 'index',
            'parent' => 'main',
            'sort' => 20,
            'vars' => [
                'actions' => ['select'],
                'limit' => 20,
                'pager' => true,
                'search' => ['name'],
                'title' => null,
            ],
        ],
    ],
    'create' => [
        'content' => [
            'type' => 'create',
            'parent' => 'main',
            'sort' => 20,
            'vars' => [
                'redirect' => true,
                'title' => null,
            ],
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 20,
            'vars' => [
                'title' => null,
            ],
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'view',
            'parent' => 'main',
            'sort' => 20,
        ],
        'content-top' => [
            'type' => 'container',
        ],
        'content-middle' => [
            'type' => 'container',
        ],
        'content-bottom' => [
            'type' => 'container',
        ],
    ],
    'page-article' => [
        'index' => [
            'type' => 'index',
            'parent' => 'content-middle',
            'sort' => 10,
            'vars' => [
                'attr' => ['image', 'date', 'name', 'teaser'],
                'entity' => 'article',
                'link' => true,
                'pager' => true,
                'parent_id' => true,
            ],
        ],
    ],
    'page-home' => [
        'breadcrumb' => [
            'active' => false,
        ],
        'content' => [
            'vars' => [
                'attr' => ['image', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
    'page-index' => [
        'index' => [
            'type' => 'index',
            'parent' => 'content-middle',
            'sort' => 10,
            'vars' => [
                'attr' => ['name', 'teaser'],
                'entity' => 'page',
                'link' => true,
                'pager' => true,
                'parent_id' => true,
                'search' => ['name', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
    'page-sitemap' => [
        'sitemap' => [
            'type' => 'menu',
            'parent' => 'content-middle',
            'sort' => 10,
            'vars' => [
                'tag' => 'section',
            ],
        ],
    ],
    'account/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'role_id'],
            ],
        ],
    ],
    'account/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id'],
            ],
        ],
    ],
    'account/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id'],
            ],
        ],
    ],
    'account/login' => [
        'toolbar' => [
            'active' => false,
        ],
        'bottom' => [
            'active' => false,
        ],
        'content' => [
            'type' => 'login',
            'parent' => 'main',
            'sort' => 20,
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 20,
            'vars' => [
                'attr' => ['password', 'confirmation'],
            ],
        ],
    ],
    'article/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'parent_id', 'status', 'date'],
            ],
        ],
    ],
    'article/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    'article/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    'article/view' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'image', 'teaser', 'main'],
            ],
        ],
    ],
    'content/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'pos', 'parent_id', 'menu', 'status', 'date'],
            ],
        ],
    ],
    'content/create' => [
        'content' => [
            'vars' => [
                'attr' => [
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status', 'layout',
                    'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description'
                ],
            ],
        ],
    ],
    'content/edit' => [
        'content' => [
            'vars' => [
                'attr' => [
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status', 'layout',
                    'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description'
                ],
            ],
        ],
    ],
    'content/view' => [
        'content' => [
            'vars' => [
                'attr' => ['image', 'name', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
    'file/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'file/browser' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'file/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'file/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    'role/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name'],
            ],
        ],
    ],
    'role/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'priv'],
            ],
        ],
    ],
    'role/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'priv'],
            ],
        ],
    ],
];
