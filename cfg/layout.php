<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'tpl',
            'tpl' => 'root.phtml',
        ],
        'head' => [
            'type' => 'tpl',
            'tpl' => 'head.phtml',
        ],
        'head-ext' => [
            'type' => 'container',
        ],
        'top' => [
            'type' => 'container',
        ],
        'toolbar' => [
            'type' => 'toolbar',
            'priv' => 'account-user',
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
            'vars' => ['tag' => 'aside'],
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
            'vars' => ['inherit' => true],
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
            'vars' => ['actions' => ['view', 'edit', 'delete'], 'create' => true, 'head' => true, 'inaccessible' => true, 'pager' => true, 'search' => true, 'title' => null],
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
            'vars' => ['actions' => ['rte'], 'limit' => 20, 'pager' => true, 'search' => true, 'title' => null],
        ],
    ],
    'create' => [
        'content' => [
            'type' => 'create',
            'parent' => 'main',
            'sort' => 20,
            'vars' => ['redirect' => true, 'title' => null],
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 20,
            'vars' => ['title' => null],
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
            'vars' => ['attr' => ['image', 'date', 'name', 'teaser'], 'ent' => 'article', 'link' => true, 'pager' => true, 'parent' => true],
        ],
    ],
    'page-home' => [
        'breadcrumb' => [
            'active' => false,
        ],
        'content' => [
            'vars' => ['attr' => ['image', 'main', 'aside']],
        ],
    ],
    'page-index' => [
        'index' => [
            'type' => 'index',
            'parent' => 'content-middle',
            'sort' => 10,
            'vars' => ['attr' => ['name', 'teaser'], 'ent' => 'page', 'link' => true, 'pager' => true, 'parent' => true, 'search' => true],
        ],
    ],
    'page-sitemap' => [
        'sitemap' => [
            'type' => 'menu',
            'parent' => 'content-middle',
            'sort' => 10,
            'vars' => ['tag' => 'section'],
        ],
    ],
    'account/admin' => [
        'content' => [
            'vars' => ['attr' => ['name', 'role']],
        ],
    ],
    'account/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'password', 'role']],
        ],
    ],
    'account/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'password', 'role']],
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
            'vars' => ['attr' => ['password', 'confirmation']],
        ],
    ],
    'app/js' => [
        'root' => [
            'tpl' => 'app.js',
        ],
    ],
    'article/admin' => [
        'content' => [
            'vars' => ['attr' => ['name', 'parent', 'status', 'date']],
        ],
    ],
    'article/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'slug', 'parent', 'status', 'image', 'teaser', 'main', 'meta']],
        ],
    ],
    'article/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'slug', 'parent', 'status', 'image', 'teaser', 'main', 'meta']],
        ],
    ],
    'article/view' => [
        'content' => [
            'vars' => ['attr' => ['name', 'image', 'teaser', 'main']],
        ],
    ],
    'content/admin' => [
        'content' => [
            'vars' => ['attr' => ['name', 'pos', 'parent', 'menu', 'status', 'date']],
        ],
    ],
    'content/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'slug', 'disabled', 'menu', 'menuname', 'parent', 'sort', 'status', 'layout', 'image', 'main', 'aside', 'sidebar', 'meta']],
        ],
    ],
    'content/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'slug', 'disabled', 'menu', 'menuname', 'parent', 'sort', 'status', 'layout', 'image', 'main', 'aside', 'sidebar', 'meta']],
        ],
    ],
    'content/view' => [
        'content' => [
            'vars' => ['attr' => ['image', 'name', 'main', 'aside']],
        ],
    ],
    'file/admin' => [
        'content' => [
            'vars' => ['attr' => ['name', 'type', 'info']],
        ],
    ],
    'file/browser' => [
        'content' => [
            'vars' => ['attr' => ['name', 'info']],
        ],
    ],
    'file/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'info']],
        ],
    ],
    'file/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'info']],
        ],
    ],
    'role/admin' => [
        'content' => [
            'vars' => ['attr' => ['name']],
        ],
    ],
    'role/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'priv']],
        ],
    ],
    'role/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'priv']],
        ],
    ],
];
