<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'tpl',
            'tpl' => 'root.phtml',
        ],
        'head' => [
            'type' => 'tpl',
            'tpl' => 'block/head.phtml',
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
            'tpl' => 'block/msg.phtml',
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
            'tpl' => 'block/header.phtml',
            'parent' => 'top',
            'sort' => 20,
        ],
        'menu' => [
            'type' => 'menu',
            'parent' => 'top',
            'sort' => 30,
        ],
        'footer' => [
            'type' => 'tpl',
            'tpl' => 'block/footer.phtml',
            'parent' => 'bottom',
            'sort' => 10,
        ],
    ],
    'index' => [
        'content' => [
            'type' => 'index',
            'parent' => 'main',
            'sort' => 10,
            'vars' => ['link' => true, 'pager' => 'pager', 'search' => 'search'],
        ],
        'search' => [
            'type' => 'tpl',
            'tpl' => 'block/search.phtml',
        ],
        'pager' => [
            'type' => 'pager',
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
            'parent' => 'main',
            'sort' => 10,
            'vars' => ['actions' => ['view', 'edit', 'delete'], 'create' => true, 'head' => true, 'pager' => 'pager', 'search' => 'search', 'unpublished' => true],
        ],
        'search' => [
            'type' => 'tpl',
            'tpl' => 'block/search.phtml',
        ],
        'pager' => [
            'type' => 'pager',
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
            'sort' => 10,
            'vars' => ['actions' => ['rte'], 'limit' => 20, 'pager' => 'pager', 'search' => 'search'],
        ],
        'search' => [
            'type' => 'tpl',
            'tpl' => 'block/search.phtml',
        ],
        'pager' => [
            'type' => 'pager',
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 10,
        ],
    ],
    'create' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 10,
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'form',
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
            'sort' => 10,
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'sort' => 10,
            'vars' => ['attr' => ['password', 'confirmation']],
        ],
    ],
    'app/error' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'app/error.phtml',
            'parent' => 'main',
            'sort' => 10,
        ],
    ],
    'app/js' => [
        'root' => [
            'tpl' => 'app/app.js',
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
    'article/index' => [
        'content' => [
            'vars' => ['attr' => ['image', 'name', 'teaser']],
        ],
    ],
    'article/view' => [
        'content' => [
            'vars' => ['attr' => ['name', 'image', 'teaser', 'main']],
        ],
    ],
    'content/admin' => [
        'content' => [
            'vars' => ['attr' => ['name', 'pos', 'menu', 'status', 'date']],
        ],
    ],
    'content/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'slug', 'menu', 'parent', 'sort', 'status', 'image', 'main', 'aside', 'sidebar', 'meta']],
        ],
    ],
    'content/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'slug', 'menu', 'parent', 'sort', 'status', 'image', 'main', 'aside', 'sidebar', 'meta']],
        ],
    ],
    'content/view' => [
        'content' => [
            'vars' => ['attr' => ['image', 'main', 'aside']],
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
    'page/sitemap' => [
        'content' => [
            'type' => 'menu',
            'parent' => 'main',
            'sort' => 10,
            'vars' => ['tag' => 'section'],
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
    'url/admin' => [
        'content' => [
            'vars' => ['attr' => ['name', 'target', 'redirect']],
        ],
    ],
    'url/create' => [
        'content' => [
            'vars' => ['attr' => ['name', 'target', 'redirect']],
        ],
    ],
    'url/edit' => [
        'content' => [
            'vars' => ['attr' => ['name', 'target', 'redirect']],
        ],
    ],
];
