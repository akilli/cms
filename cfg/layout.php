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
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'block\index',
            'tpl' => 'ent/index.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'admin'],
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
            'vars' => ['act' => 'browser', 'limit' => 20],
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'block\tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'edit' => [
        'content' => [
            'type' => 'block\tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'block\tpl',
            'tpl' => 'ent/view.phtml',
            'parent' => 'main',
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
    'account/login' => [
        'top' => [
            'active' => false,
        ],
        'bottom' => [
            'active' => false,
        ],
        'content' => [
            'type' => 'block\tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'block\tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
];
