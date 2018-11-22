<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'tpl',
            'tpl' => 'root.phtml',
        ],
        'head' => [
            'type' => 'container',
            'parent_id' => 'root',
        ],
        'meta' => [
            'type' => 'meta',
            'parent_id' => 'head',
            'sort' => 10,
        ],
        'asset' => [
            'type' => 'tpl',
            'tpl' => 'head/asset.phtml',
            'parent_id' => 'head',
            'sort' => 20,
        ],
        'asset-user' => [
            'type' => 'tpl',
            'tpl' => 'head/asset-user.phtml',
            'priv' => '_user_',
            'parent_id' => 'head',
            'sort' => 30,
        ],
        'asset-ext' => [
            'type' => 'tpl',
            'tpl' => 'head/asset-ext.phtml',
            'parent_id' => 'head',
            'sort' => 40,
        ],
        'toolbar' => [
            'type' => 'toolbar',
            'priv' => '_user_',
            'parent_id' => 'root',
        ],
        'header' => [
            'type' => 'container',
            'parent_id' => 'root',
            'vars' => [
                'tag' => 'header',
            ],
        ],
        'top' => [
            'type' => 'container',
            'parent_id' => 'root',
        ],
        'msg' => [
            'type' => 'tpl',
            'tpl' => 'app/msg.phtml',
            'parent_id' => 'top',
            'sort' => 20,
        ],
        'left' => [
            'type' => 'container',
            'parent_id' => 'root',
        ],
        'content' => [
            'type' => 'container',
            'parent_id' => 'root',
            'vars' => [
                'tag' => 'article',
            ],
        ],
        'right' => [
            'type' => 'container',
            'parent_id' => 'root',
            'vars' => [
                'tag' => 'aside',
            ],
        ],
        'bottom' => [
            'type' => 'container',
            'parent_id' => 'root',
        ],
        'footer' => [
            'type' => 'container',
            'parent_id' => 'root',
            'vars' => [
                'tag' => 'footer',
            ],
        ],
    ],
    '_public_' => [
        'header-logo' => [
            'type' => 'tpl',
            'tpl' => 'header/logo.phtml',
            'parent_id' => 'header',
            'sort' => 10,
        ],
        'menu' => [
            'type' => 'menu',
            'parent_id' => 'top',
            'sort' => 10,
            'vars' => [
                'toggle' => true,
            ],
        ],
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent_id' => 'left',
            'sort' => 10,
        ],
        'page-sidebar' => [
            'type' => 'sidebar',
            'parent_id' => 'right',
            'sort' => 10,
            'vars' => [
                'inherit' => 0,
            ],
        ],
        'footer-nav' => [
            'type' => 'tpl',
            'tpl' => 'footer/nav.phtml',
            'parent_id' => 'footer',
            'sort' => 10,
        ],
    ],
    '_error_' => [
        'content-main' => [
            'type' => 'tpl',
            'tpl' => 'app/error.phtml',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    'admin' => [
        'content-main' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 10,
            'vars' => [
                'pager' => true,
                'search' => ['name'],
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
            'parent_id' => 'content',
            'sort' => 10,
            'vars' => [
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
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    'view' => [
        'content-main' => [
            'type' => 'view',
            'parent_id' => 'content',
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
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    'account/password' => [
        'content-main' => [
            'type' => 'password',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    'block_content/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'title'],
            ],
        ],
    ],
    'block_content/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'title', 'content'],
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
    'layout/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
            ],
        ],
    ],
    'layout/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
            ],
        ],
    ],
    'page/view' => [
        'content-main' => [
            'type' => 'page',
        ],
    ],
    'page_article/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'parent_id', 'status', 'date'],
            ],
        ],
    ],
    'page_article/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    'page_article/view' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'image', 'teaser', 'main'],
            ],
        ],
    ],
    'page_content/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'pos', 'parent_id', 'menu', 'status', 'date'],
            ],
        ],
    ],
    'page_content/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => [
                    'name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status',
                    'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description'
                ],
            ],
        ],
    ],
    'page_content/view' => [
        'content-main' => [
            'vars' => [
                'attr' => ['image', 'name', 'teaser', 'main', 'aside'],
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
