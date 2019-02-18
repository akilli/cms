<?php
return [
    /*******************************************************************************************************************
     * All Areas
     ******************************************************************************************************************/
    '_all_' => [
        // Root Container
        'root' => [
            'type' => 'root',
        ],
        // Root Blocks
        'head' => [
            'type' => 'container',
            'parent_id' => 'root',
            'sort' => 10,
            'cfg' => [
                'tag' => 'head',
            ],
        ],
        'body' => [
            'type' => 'container',
            'parent_id' => 'root',
            'sort' => 20,
            'cfg' => [
                'tag' => 'body',
            ],
        ],
        // Head Blocks
        'head-meta' => [
            'type' => 'meta',
            'parent_id' => 'head',
            'sort' => 10,
        ],
        // Body Blocks
        'header' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 10,
            'cfg' => [
                'tag' => 'header',
            ],
        ],
        'top' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 20,
        ],
        'main' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 30,
            'cfg' => [
                'tag' => 'main',
            ],
        ],
        'bottom' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 40,
        ],
        'footer' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 50,
            'cfg' => [
                'tag' => 'footer',
            ],
        ],
        // Top Blocks
        'msg' => [
            'type' => 'msg',
            'parent_id' => 'top',
            'sort' => 10,
        ],
        // Main Blocks
        'content' => [
            'type' => 'container',
            'parent_id' => 'main',
            'sort' => 10,
            'cfg' => [
                'tag' => 'article',
            ],
        ],
        'sidebar' => [
            'type' => 'container',
            'parent_id' => 'main',
            'sort' => 20,
            'cfg' => [
                'tag' => 'aside',
            ],
        ],
    ],
    /**
     * Invalid Requests
     */
    '_invalid_' => [
        'content-main' => [
            'type' => 'tpl',
            'tpl' => 'page/error.phtml',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    /*******************************************************************************************************************
     * Admin Area
     ******************************************************************************************************************/
    '_admin_' => [
        'head-admin' => [
            'type' => 'tpl',
            'tpl' => 'head/admin.phtml',
            'parent_id' => 'head',
            'sort' => 20,
        ],
        'toolbar' => [
            'type' => 'toolbar',
            'priv' => '_user_',
            'parent_id' => 'body',
            'sort' => 5,
        ],
    ],
    /**
     * Account Dashboard
     */
    'account/dashboard' => [],
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
            'cfg' => [
                'pager' => 'bottom',
                'search' => ['name'],
            ],
        ],
    ],
    'account/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'role_id'],
            ],
        ],
    ],
    'block_content/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name'],
            ],
        ],
    ],
    'file/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['url', 'name', 'info'],
                'search' => ['name', 'url', 'info'],
            ],
        ],
    ],
    'layout/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
            ],
        ],
    ],
    'page_article/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'parent_id', 'status', 'date'],
            ],
        ],
    ],
    'page_content/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'pos', 'parent_id', 'menu', 'status', 'date'],
            ],
        ],
    ],
    'role/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name'],
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
            'cfg' => [
                'limit' => 20,
                'pager' => 'bottom',
                'search' => ['name'],
            ],
        ],
    ],
    'file/browser' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['url', 'name', 'info'],
                'search' => ['name', 'url', 'info'],
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
            'cfg' => [
                'attr_id' => ['name', 'password', 'role_id'],
            ],
        ],
    ],
    'block_content/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'content'],
            ],
        ],
    ],
    'file/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['url', 'name', 'info'],
            ],
        ],
    ],
    'layout/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
            ],
        ],
    ],
    'page_article/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'date', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    'page_content/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => [
                    'name', 'slug', 'disabled', 'menu', 'parent_id', 'sort', 'status',
                    'title', 'image', 'teaser', 'main', 'aside', 'meta_title', 'meta_description'
                ],
            ],
        ],
    ],
    'role/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'priv'],
            ],
        ],
    ],
    /*******************************************************************************************************************
     * Public Area
     ******************************************************************************************************************/
    '_public_' => [
        'head-public' => [
            'type' => 'tpl',
            'tpl' => 'head/public.phtml',
            'parent_id' => 'head',
            'sort' => 20,
        ],
        'menu' => [
            'type' => 'menu',
            'parent_id' => 'body',
            'sort' => 15,
            'cfg' => [
                'toggle' => true,
            ],
        ],
        'header-logo' => [
            'type' => 'tpl',
            'tpl' => 'header/logo.phtml',
            'parent_id' => 'header',
            'sort' => 10,
        ],
        'footer-nav' => [
            'type' => 'tpl',
            'tpl' => 'footer/nav.phtml',
            'parent_id' => 'footer',
            'sort' => 10,
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
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent_id' => 'main',
            'sort' => 5,
        ],
    ],
    'page_article/view' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'image', 'teaser', 'main'],
            ],
        ],
    ],
    'page_content/view' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['image', 'name', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
    /**
     * Page-specific
     */
    '/' => [
        'breadcrumb' => [
            'active' => false,
        ],
        'content-main' => [
            'cfg' => [
                'attr_id' => ['image', 'teaser', 'main', 'aside'],
            ],
        ],
    ],
];
