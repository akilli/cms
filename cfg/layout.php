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
        'asset-ext' => [
            'type' => 'tpl',
            'tpl' => 'head/asset-ext.phtml',
            'parent_id' => 'head',
            'sort' => 30,
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
    /**
     * Admin Area
     */
    '_admin_' => [
        'asset-admin' => [
            'type' => 'tpl',
            'tpl' => 'head/asset-admin.phtml',
            'parent_id' => 'head',
            'sort' => 25,
        ],
    ],
    /**
     * Public Area
     */
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
    /**
     * Error
     */
    '_error_' => [
        'content-main' => [
            'type' => 'tpl',
            'tpl' => 'app/error.phtml',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    /**
     * Account Login
     */
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
    /**
     * Account Password
     */
    'account/password' => [
        'content-main' => [
            'type' => 'password',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    /**
     * Admin Action
     */
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
    'account/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'role_id'],
            ],
        ],
    ],
    'block_content/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'title'],
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
    'layout/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
            ],
        ],
    ],
    'page_article/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'parent_id', 'status', 'date'],
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
    'role/admin' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name'],
            ],
        ],
    ],
    /**
     * Browser Action
     */
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
    'file/browser' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'info'],
            ],
        ],
    ],
    /**
     * Edit Action
     */
    'edit' => [
        'content-main' => [
            'type' => 'edit',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    'account/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id'],
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
    'file/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'info'],
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
    'page_article/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
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
    'role/edit' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'priv'],
            ],
        ],
    ],
    /**
     * View Action
     */
    'view' => [
        'content-main' => [
            'type' => 'view',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    'page/view' => [
        'content-main' => [
            'type' => 'page',
        ],
    ],
    'page_article/view' => [
        'content-main' => [
            'vars' => [
                'attr' => ['name', 'image', 'teaser', 'main'],
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
