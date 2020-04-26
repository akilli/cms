<?php
return [
    /**
     * All
     */
    '_all_' => [
        'html' => [
            'type' => 'html',
        ],
        'head' => [
            'type' => 'container',
            'parent_id' => 'html',
            'sort' => 100,
            'cfg' => [
                'tag' => 'head',
            ],
        ],
        'body' => [
            'type' => 'container',
            'parent_id' => 'html',
            'sort' => 200,
            'image' => ['sizes' => '100vw'],
            'cfg' => [
                'tag' => 'body',
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
            'priv' => '_user_',
            'parent_id' => 'body',
            'sort' => 100,
        ],
        'top' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 200,
        ],
        'main' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 300,
            'cfg' => [
                'tag' => 'main',
            ],
        ],
        'bottom' => [
            'type' => 'container',
            'parent_id' => 'body',
            'sort' => 400,
        ],
        'content' => [
            'type' => 'container',
            'parent_id' => 'main',
            'sort' => 100,
            'cfg' => [
                'tag' => 'article',
            ],
        ],
        'sidebar' => [
            'type' => 'container',
            'parent_id' => 'main',
            'sort' => 200,
            'cfg' => [
                'tag' => 'aside',
            ],
        ],
        'title' => [
            'type' => 'title',
            'parent_id' => 'content',
            'sort' => 100,
        ],
        'msg' => [
            'type' => 'msg',
            'parent_id' => 'content',
            'sort' => 200,
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
        'view' => [
            'type' => 'tpl',
            'tpl' => 'error.phtml',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    /**
     * Admin Area
     */
    '_admin_' => [
        'asset' => [
            'tpl' => 'asset-admin.phtml',
        ],
    ],
    /**
     * Public Area
     */
    '_public_' => [
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
            'cfg' => [
                'toggle' => true,
            ],
        ],
        'footer' => [
            'type' => 'tpl',
            'tpl' => 'footer.phtml',
            'parent_id' => 'body',
            'sort' => 500,
        ],
    ],
    /**
     * Action Defaults
     */
    'admin' => [
        'new' => [
            'type' => 'tpl',
            'tpl' => 'new.phtml',
            'parent_id' => 'content',
            'sort' => 300,
        ],
        'index' => [
            'type' => 'index',
            'tpl' => 'index-admin.phtml',
            'parent_id' => 'content',
            'sort' => 400,
            'cfg' => [
                'pager' => 'bottom',
                'search' => ['name'],
                'sort' => true,
            ],
        ],
    ],
    'browser' => [
        'toolbar' => [
            'active' => false,
        ],
        'index' => [
            'type' => 'index',
            'tpl' => 'index-browser.phtml',
            'parent_id' => 'content',
            'sort' => 300,
            'cfg' => [
                'attr_id' => ['name'],
                'limit' => 20,
                'pager' => 'bottom',
                'search' => ['name'],
            ],
        ],
    ],
    'edit' => [
        'form' => [
            'type' => 'edit',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    'view' => [
        'view' => [
            'type' => 'view',
            'parent_id' => 'content',
            'sort' => 300,
        ],
    ],
    /**
     * Account
     */
    'account/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'username', 'role_id'],
                'filter' => ['role_id'],
                'search' => ['name', 'username'],
            ],
        ],
    ],
    'account/dashboard' => [
        'title' => [
            'cfg' => [
                'text' => 'Dashboard',
            ],
        ],
    ],
    'account/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'role_id', 'username', 'password', 'email'],
            ],
        ],
    ],
    'account/login' => [
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
    'account/profile' => [
        'title' => [
            'cfg' => [
                'text' => 'Profile',
            ],
        ],
        'form' => [
            'type' => 'profile',
            'parent_id' => 'content',
            'sort' => 300,
            'cfg' => [
                'attr_id' => ['password', 'confirmation', 'email'],
            ],
        ],
    ],
    /**
     * Block
     */
    'block/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name'],
            ],
        ],
    ],
    'block/browser' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'title', 'file', 'content'],
                'filter' => ['entity_id'],
                'search' => ['name', 'title', 'content'],
            ],
        ],
    ],
    'block/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'title', 'link', 'file', 'content'],
            ],
        ],
    ],
    /**
     * File
     */
    'file/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['url', 'name'],
                'search' => ['name', 'url', 'info'],
            ],
        ],
    ],
    'file/browser' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['url', 'name'],
                'filter' => ['entity_id'],
                'search' => ['name', 'url', 'info'],
            ],
        ],
    ],
    'file/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'url', 'thumb', 'info'],
            ],
        ],
    ],
    /**
     * File Audio
     */
    'file_audio/browser' => [
        'index' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    /**
     * File Doc
     */
    'file_doc/browser' => [
        'index' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    /**
     * File Iframe
     */
    'file_iframe/browser' => [
        'index' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    /**
     * File Image
     */
    'file_image/browser' => [
        'index' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    /**
     * File Video
     */
    'file_video/browser' => [
        'index' => [
            'cfg' => [
                'filter' => [],
            ],
        ],
    ],
    /**
     * Layout
     */
    'layout/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
                'filter' => ['block_id', 'page_id', 'parent_id'],
            ],
        ],
    ],
    'layout/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
            ],
        ],
    ],
    /**
     * Page
     */
    'page/admin' => [
        'index' => [
            'cfg' => [
                'filter' => ['parent_id', 'account_id'],
            ],
        ],
    ],
    'page/view' => [
        'breadcrumb' => [
            'type' => 'breadcrumb',
            'parent_id' => 'main',
            'sort' => 50,
        ],
        'view' => [
            'cfg' => [
                'attr_id' => ['content', 'aside'],
            ],
        ],
    ],
    /**
     * Page Article
     */
    'page_article/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'parent_id', 'timestamp'],
            ],
        ],
    ],
    'page_article/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'slug', 'parent_id', 'content', 'aside', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    /**
     * Page Content
     */
    'page_content/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'pos', 'parent_id', 'menu', 'timestamp'],
            ],
        ],
    ],
    'page_content/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'slug', 'disabled', 'menu', 'parent_id', 'sort', 'title', 'content', 'aside', 'meta_title', 'meta_description'],
            ],
        ],
    ],
    /**
     * Role
     */
    'role/admin' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name'],
            ],
        ],
    ],
    'role/edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => ['name', 'priv'],
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
