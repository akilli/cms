<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'root',
        ],
        'head' => [
            'type' => 'container',
        ],
        'meta' => [
            'type' => 'meta',
            'parent' => 'head',
            'sort' => 10,
        ],
        'admin' => [
            'type' => 'tpl',
            'tpl' => 'head/admin.phtml',
            'priv' => 'account-user',
            'parent' => 'head',
            'sort' => 20,
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
            'type' => 'msg',
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
            'vars' => ['act' => 'index', 'pager' => true, 'search' => true],
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
            'parent' => 'main',
            'vars' => ['act' => 'admin', 'pager' => true, 'search' => true],
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
            'vars' => ['act' => 'browser', 'limit' => 20, 'pager' => true, 'search' => true],
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
        ],
    ],
    'create' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'view',
            'parent' => 'main',
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
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'form',
            'parent' => 'main',
            'vars' => ['attr' => ['password', 'confirmation']],
        ],
    ],
    'app/error' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'app/error.phtml',
            'parent' => 'main',
        ],
    ],
    'app/js' => [
        'root' => [
            'type' => 'js',
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
