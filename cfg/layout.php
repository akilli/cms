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
            'sort' => 10,
        ],
        'user' => [
            'type' => 'tpl',
            'tpl' => 'head/user.phtml',
            'priv' => 'account-user',
            'parent' => 'head',
            'sort' => 20,
        ],
        'top' => [
            'type' => 'container',
        ],
        'toolbar' => [
            'type' => 'tpl',
            'tpl' => 'nav/toolbar.phtml',
            'priv' => 'account-user',
            'parent' => 'top',
            'sort' => 10,
        ],
        'msg' => [
            'type' => 'msg',
            'tpl' => 'layout/msg.phtml',
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
            'tpl' => 'layout/header.phtml',
            'parent' => 'top',
            'sort' => 20,
        ],
        'menu' => [
            'type' => 'menu',
            'parent' => 'top',
            'sort' => 30,
        ],
    ],
    'index' => [
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/index.phtml',
            'parent' => 'main',
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
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
        'rte' => [
            'type' => 'tpl',
            'tpl' => 'head/rte.phtml',
            'parent' => 'head',
            'sort' => 30,
        ],
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/index.phtml',
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
            'sort' => 30,
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
        'bottom' => [
            'active' => false,
        ],
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/form.phtml',
            'parent' => 'main',
        ],
    ],
];
