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
        'meta' => [
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
        // Content Blocks
        'title' => [
            'type' => 'title',
            'parent_id' => 'content',
            'sort' => 10,
        ],
    ],
    /**
     * Invalid Requests
     */
    '_invalid_' => [
        'title' => [
            'cfg' => [
                'text' => 'Error',
            ],
        ],
        'content-main' => [
            'type' => 'tpl',
            'tpl' => 'page/error.phtml',
            'parent_id' => 'content',
            'sort' => 20,
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
    'account/dashboard' => [
        'title' => [
            'cfg' => [
                'text' => 'Dashboard',
            ],
        ],
        'content-published' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 20,
            'cfg' => [
                'attr_id' => ['name', 'account_id', 'timestamp'],
                'crit' => [['status', 'published']],
                'entity_id' => 'page',
                'limit' => 10,
                'mode' => 'admin',
                'order' => ['timestamp' => 'desc', 'id' => 'desc'],
                'title' => 'Published Pages',
            ],
        ],
        'content-pending' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 30,
            'cfg' => [
                'attr_id' => ['name', 'account_id', 'timestamp'],
                'crit' => [['status', 'pending']],
                'entity_id' => 'version',
                'limit' => 10,
                'mode' => 'admin',
                'order' => ['timestamp' => 'asc', 'id' => 'asc'],
                'title' => 'Pending Versions',
            ],
        ],
    ],
    /**
     * Account Login
     */
    'account/login' => [
        'toolbar' => [
            'active' => false,
        ],
        'title' => [
            'cfg' => [
                'text' => 'Login',
            ],
        ],
        'content-main' => [
            'type' => 'login',
            'parent_id' => 'content',
            'sort' => 20,
        ],
    ],
    /**
     * Account Profile
     */
    'account/profile' => [
        'title' => [
            'cfg' => [
                'text' => 'Profile',
            ],
        ],
        'content-main' => [
            'type' => 'profile',
            'parent_id' => 'content',
            'sort' => 20,
            'cfg' => [
                'attr_id' => ['password', 'confirmation', 'email'],
            ],
        ],
    ],
    /**
     * Admin Action
     */
    'admin' => [
        'content-new' => [
            'type' => 'tpl',
            'tpl' => 'block/new.phtml',
            'parent_id' => 'content',
            'sort' => 20,
        ],
        'content-main' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 30,
            'cfg' => [
                'mode' => 'admin',
                'pager' => 'bottom',
                'search' => ['name'],
                'sort' => true,
            ],
        ],
    ],
    'account/admin' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'username', 'role_id'],
                'filter' => ['role_id'],
                'search' => ['name', 'username'],
            ],
        ],
    ],
    'block/admin' => [
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
                'filter' => ['block_id', 'page_id', 'parent_id'],
            ],
        ],
    ],
    'page/admin' => [
        'content-main' => [
            'cfg' => [
                'filter' => ['parent_id', 'account_id', 'status'],
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
            'sort' => 20,
            'cfg' => [
                'limit' => 20,
                'mode' => 'browser',
                'pager' => 'bottom',
                'search' => ['name'],
            ],
        ],
    ],
    'file/browser' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['url', 'name', 'info'],
                'filter' => ['entity_id'],
                'search' => ['name', 'url', 'info'],
            ],
        ],
    ],
    'file_audio/browser' => [
        'content-main' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    'file_doc/browser' => [
        'content-main' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    'file_iframe/browser' => [
        'content-main' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    'file_image/browser' => [
        'content-main' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    'file_video/browser' => [
        'content-main' => [
            'cfg' => [
                'filter' => [],
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
            'sort' => 20,
        ],
    ],
    'account/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'role_id', 'username', 'password', 'email'],
            ],
        ],
    ],
    'block/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'title', 'link', 'media', 'content'],
            ],
        ],
    ],
    'file/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'url', 'thumb_url', 'info'],
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
                'attr_id' => ['name', 'slug', 'parent_id', 'status', 'image', 'main', 'aside', 'date', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    'page_content/edit' => [
        'content-main' => [
            'cfg' => [
                'attr_id' => ['name', 'slug', 'disabled', 'menu', 'parent_id', 'sort', 'status', 'title', 'image', 'main', 'aside', 'meta_title', 'meta_description'],
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
            'sort' => 20,
        ],
        'teaser' => [
            'type' => 'container',
            'parent_id' => 'content',
            'sort' => 30,
            'cfg' => [
                'tag' => 'section',
            ],
        ],
    ],
    'page/view' => [
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent_id' => 'main',
            'sort' => 5,
        ],
        'content-main' => [
            'cfg' => [
                'attr_id' => ['image', 'main', 'aside'],
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
        'title' => [
            'active' => false,
        ],
    ],
];
