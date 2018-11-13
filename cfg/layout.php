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
        'main' => [
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
        'menu' => [
            'type' => 'container',
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
        'before' => [
            'type' => 'container',
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
        'content' => [
            'type' => 'tpl',
            'tpl' => 'error.phtml',
            'parent' => 'main',
            'sort' => 10,
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
            'parent' => 'main',
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
        'content' => [
            'type' => 'index',
            'parent' => 'main',
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
        'content' => [
            'type' => 'create',
            'parent' => 'main',
            'sort' => 10,
            'vars' => [
                'redirect' => true,
                'title' => null,
            ],
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'edit',
            'parent' => 'main',
            'sort' => 10,
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'view',
            'parent' => 'main',
            'sort' => 10,
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
        'content' => [
            'type' => 'login',
            'parent' => 'main',
            'sort' => 10,
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'password',
            'parent' => 'main',
            'sort' => 10,
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
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status',
                    'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description'
                ],
            ],
        ],
    ],
    'content/edit' => [
        'content' => [
            'vars' => [
                'attr' => [
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status',
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
    '/' => [
        'breadcrumb' => [
            'active' => false,
        ],
        'content' => [
            'vars' => [
                'attr' => ['image', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
];
