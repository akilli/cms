<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'block\tpl',
            'tpl' => 'layout/root.phtml',
        ],
        'head' => [
            'type' => 'block\container',
        ],
        'meta' => [
            'type' => 'block\meta',
            'tpl' => 'head/meta.phtml',
            'parent' => 'head',
            'sort' => 10,
        ],
        'admin' => [
            'type' => 'block\tpl',
            'tpl' => 'head/admin.phtml',
            'priv' => 'account-user',
            'parent' => 'head',
            'sort' => 20,
        ],
        'top' => [
            'type' => 'block\container',
        ],
        'toolbar' => [
            'type' => 'block\toolbar',
            'priv' => 'account-user',
            'parent' => 'top',
            'sort' => 10,
        ],
        'msg' => [
            'type' => 'block\msg',
            'tpl' => 'layout/msg.phtml',
        ],
        'main' => [
            'type' => 'block\container',
        ],
        'sidebar' => [
            'type' => 'block\container',
            'vars' => ['tag' => 'aside'],
        ],
        'bottom' => [
            'type' => 'block\container',
        ],
    ],
    '_admin_' => [
        'sidebar' => [
            'active' => false,
        ],
    ],
    '_public_' => [
        'header' => [
            'type' => 'block\tpl',
            'tpl' => 'layout/header.phtml',
            'parent' => 'top',
            'sort' => 20,
        ],
        'menu' => [
            'type' => 'block\menu',
            'parent' => 'top',
            'sort' => 30,
        ],
    ],
    'index' => [
        'content' => [
            'type' => 'block\index',
            'tpl' => 'ent/index.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'index', 'limit' => 10, 'pager' => 5, 'search' => true],
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'block\index',
            'tpl' => 'ent/index.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'admin', 'limit' => 10, 'pager' => 5, 'search' => true],
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
            'type' => 'block\index',
            'tpl' => 'ent/index.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'browser', 'limit' => 20, 'pager' => 5, 'search' => true],
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'block\form',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'create' => [
        'content' => [
            'type' => 'block\form',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'block\form',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'block\view',
            'tpl' => 'ent/view.phtml',
            'parent' => 'main',
        ],
    ],
    'account/admin' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'role']]],
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
            'type' => 'block\form',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
            'vars' => ['attr' => ['incl' => ['name', 'password']], 'login' => true],
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'block\form',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
            'vars' => ['attr' => ['incl' => ['password', 'confirmation']]],
        ],
    ],
    'app/error' => [
        'content' => [
            'type' => 'block\tpl',
            'tpl' => 'app/error.phtml',
            'parent' => 'main',
        ],
    ],
    'app/js' => [
        'root' => [
            'tpl' => 'app/app.js',
        ],
    ],
    'article/admin' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'parent', 'status', 'date']]],
        ],
    ],
    'article/create' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'slug', 'parent', 'status', 'image', 'teaser', 'main', 'meta']]],
        ],
    ],
    'article/edit' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'slug', 'parent', 'status', 'image', 'teaser', 'main', 'meta']]],
        ],
    ],
    'article/index' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['image', 'name', 'teaser']]],
        ],
    ],
    'article/view' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'image', 'teaser', 'main']]],
        ],
    ],
    'content/admin' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'pos', 'menu', 'status', 'date']]],
        ],
    ],
    'content/create' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'slug', 'menu', 'parent', 'sort', 'status', 'image', 'main', 'aside', 'sidebar', 'meta']]],
        ],
    ],
    'content/edit' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'slug', 'menu', 'parent', 'sort', 'status', 'image', 'main', 'aside', 'sidebar', 'meta']]],
        ],
    ],
    'content/view' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['image', 'main', 'aside']]],
        ],
    ],
    'file/admin' => [
        'content' => [
            'vars' => ['attr' => ['excl' => ['ent']]],
        ],
    ],
    'file/browser' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'info']]],
        ],
    ],
    'file/create' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'info']]],
        ],
    ],
    'file/edit' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name', 'info']]],
        ],
    ],
    'page/sitemap' => [
        'content' => [
            'type' => 'block\menu',
            'parent' => 'main',
            'vars' => ['tag' => 'section'],
        ],
    ],
    'role/admin' => [
        'content' => [
            'vars' => ['attr' => ['incl' => ['name']]],
        ],
    ],
];
