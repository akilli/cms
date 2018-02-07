<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'tpl',
            'tpl' => 'layout/root.phtml',
        ],
        'head' => [
            'type' => 'container',
        ],
        'meta' => [
            'type' => 'meta',
            'tpl' => 'head/meta.phtml',
            'parent' => 'head',
            'sort' => -3,
        ],
        'user' => [
            'type' => 'tpl',
            'tpl' => 'head/user.phtml',
            'priv' => 'account-user',
            'parent' => 'head',
            'sort' => -2,
        ],
        'toolbar' => [
            'type' => 'tpl',
            'tpl' => 'layout/toolbar.phtml',
            'priv' => 'account-user',
        ],
        'top' => [
            'type' => 'container',
        ],
        'header' => [
            'type' => 'tpl',
            'tpl' => 'layout/header.phtml',
            'parent' => 'top',
            'sort' => -1,
        ],
        'msg' => [
            'type' => 'msg',
            'tpl' => 'layout/msg.phtml',
        ],
        'main' => [
            'type' => 'container',
        ],
    ],
    '_public_' => [
        'menu' => [
            'type' => 'menu',
            'parent' => 'top',
        ],
    ],
    'index' => [
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/index.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'index'],
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/admin.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'admin'],
        ],
    ],
    'browser' => [
        'toolbar' => [
            'active' => false,
        ],
        'top' => [
            'active' => false,
        ],
        'rte' => [
            'type' => 'tpl',
            'tpl' => 'head/rte.phtml',
            'parent' => 'head',
            'sort' => -1,
        ],
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/browser.phtml',
            'parent' => 'main',
            'vars' => ['act' => 'browser'],
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'edit' => [
        'rte' => [
            'type' => 'tpl',
            'tpl' => 'head/rte.phtml',
            'parent' => 'head',
            'sort' => -1,
        ],
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/view.phtml',
            'parent' => 'main',
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
            'tpl' => 'app/app.js',
        ],
    ],
    'account/login' => [
        'top' => [
            'active' => false,
        ],
        'content' => [
            'type' => 'tpl',
            'tpl' => 'account/login.phtml',
            'parent' => 'main',
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'account/password.phtml',
            'parent' => 'main',
        ],
    ],
];
