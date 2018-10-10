<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'tpl',
            'tpl' => 'root.phtml',
        ],
        'head' => [
            'type' => 'head',
        ],
        'head-ext' => [
            'type' => 'container',
        ],
        'top' => [
            'type' => 'container',
        ],
        'toolbar' => [
            'type' => 'toolbar',
            'priv' => '_user_',
            'parent' => 'top',
            'sort' => 10,
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
                'tag' => 'aside'
            ],
        ],
        'bottom' => [
            'type' => 'container',
        ],
    ],
    '_admin_' => [
        'sidebar' => [
            'active' => false,
        ],
    ],
    '_public_' => [
        'header' => [
            'type' => 'tpl',
            'tpl' => 'header.phtml',
            'parent' => 'top',
            'sort' => 20,
        ],
        'menu' => [
            'type' => 'menu',
            'parent' => 'top',
            'sort' => 30,
            'vars' => [
                'toggle' => true
            ],
        ],
        'menu-top' => [
            'type' => 'container',
        ],
        'menu-bottom' => [
            'type' => 'container',
        ],
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
                'inherit' => true
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
                'head' => true,
                'inaccessible' => true,
                'pager' => true,
                'search' => ['name'],
                'title' => null
            ],
        ],
    ],
    'browser' => [
        'top' => [
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
                'title' => null
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
                'title' => null
            ],
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 20,
            'vars' => [
                'title' => null
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
                'parent_id' => true
            ],
        ],
    ],
    'page-home' => [
        'breadcrumb' => [
            'active' => false,
        ],
        'content' => [
            'vars' => [
                'attr' => ['image', 'teaser', 'main', 'aside']
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
                'search' => ['name', 'teaser', 'main', 'aside']
            ],
        ],
    ],
    'page-sitemap' => [
        'sitemap' => [
            'type' => 'menu',
            'parent' => 'content-middle',
            'sort' => 10,
            'vars' => [
                'tag' => 'section'
            ],
        ],
    ],
    'account/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'role_id']
            ],
        ],
    ],
    'account/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id']
            ],
        ],
    ],
    'account/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'password', 'role_id']
            ],
        ],
    ],
    'account/login' => [
        'top' => [
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
                'attr' => ['password', 'confirmation']
            ],
        ],
    ],
    'app/js' => [
        'root' => [
            'tpl' => 'app.js',
        ],
    ],
    'article/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'parent_id', 'status', 'date']
            ],
        ],
    ],
    'article/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'meta_title', 'meta_description']
            ],
        ],
    ],
    'article/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'slug', 'parent_id', 'status', 'image', 'teaser', 'main', 'meta_title', 'meta_description']
            ],
        ],
    ],
    'article/view' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'image', 'teaser', 'main']
            ],
        ],
    ],
    'content/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'pos', 'parent_id', 'menu', 'status', 'date']
            ],
        ],
    ],
    'content/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status', 'layout', 'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description']
            ],
        ],
    ],
    'content/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'slug', 'disabled', 'menu', 'menu_name', 'parent_id', 'sort', 'status', 'layout', 'image', 'teaser', 'main', 'aside', 'sidebar', 'meta_title', 'meta_description']
            ],
        ],
    ],
    'content/view' => [
        'content' => [
            'vars' => [
                'attr' => ['image', 'name', 'teaser', 'main', 'aside']
            ],
        ],
    ],
    'file/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'type', 'info']
            ],
        ],
    ],
    'file/browser' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info']
            ],
        ],
    ],
    'file/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info']
            ],
        ],
    ],
    'file/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'info']
            ],
        ],
    ],
    'role/admin' => [
        'content' => [
            'vars' => [
                'attr' => ['name']
            ],
        ],
    ],
    'role/create' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'priv']
            ],
        ],
    ],
    'role/edit' => [
        'content' => [
            'vars' => [
                'attr' => ['name', 'priv']
            ],
        ],
    ],
];
