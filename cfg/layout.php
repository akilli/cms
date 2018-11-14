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
            'type' => 'container',
            'priv' => '_user_',
            'vars' => [
                'tag' => 'nav',
            ],
        ],
        'toolbar-nav' => [
            'type' => 'toolbar',
            'priv' => '_user_',
            'parent' => 'toolbar',
            'sort' => 10,
        ],
        'msg' => [
            'type' => 'tpl',
            'tpl' => 'msg.phtml',
        ],
        'content' => [
            'type' => 'container',
        ],
    ],
    '_public_' => [
        'top' => [
            'type' => 'container',
        ],
        'header' => [
            'type' => 'tpl',
            'tpl' => 'header.phtml',
        ],
        'before' => [
            'type' => 'container',
        ],
        'menu' => [
            'type' => 'container',
            'parent' => 'before',
            'sort' => 10,
            'vars' => [
                'attr' => ['data-toggle' => '', 'data-sticky' => ''],
                'tag' => 'nav',
            ],
        ],
        'menu-nav' => [
            'type' => 'menu',
            'parent' => 'menu',
            'sort' => 10,
            'vars' => [
                'toggle' => 'menu',
            ],
        ],
        'left' => [
            'type' => 'container',
        ],
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent' => 'left',
            'sort' => 10,
        ],
        'right' => [
            'type' => 'container',
            'vars' => [
                'tag' => 'aside',
            ],
        ],
        'page-sidebar' => [
            'type' => 'sidebar',
            'parent' => 'right',
            'sort' => 10,
            'vars' => [
                'inherit' => true,
            ],
        ],
        'after' => [
            'type' => 'container',
        ],
        'footer' => [
            'type' => 'tpl',
            'tpl' => 'footer.phtml',
        ],
        'bottom' => [
            'type' => 'container',
        ],
    ],
    '_error_' => [
        'content-main' => [
            'type' => 'tpl',
            'tpl' => 'error.phtml',
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
        'content-main' => [
            'type' => 'index',
            'parent' => 'content',
            'sort' => 10,
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
        'content-main' => [
            'type' => 'create',
            'parent' => 'content',
            'sort' => 10,
            'vars' => [
                'redirect' => true,
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
    'account/create' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id'],
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
    'article/create' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
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
    'content/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'pos', 'parent_id', 'menu', 'status', 'date'],
            ],
        ],
    ],
    'content/create' => [
        'content-main' => [
            'vars' => [
                'attr' => [
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status',
                    'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description'
                ],
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
    'file/create' => [
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
    'role/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name'],
            ],
        ],
    ],
    'role/create' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'priv'],
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
