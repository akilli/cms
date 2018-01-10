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
            'parent_id' => 'head',
            'sort' => -3,
        ],
        'user' => [
            'type' => 'tpl',
            'tpl' => 'head/user.phtml',
            'priv' => 'account-user',
            'parent_id' => 'head',
            'sort' => -2,
        ],
        'top' => [
            'type' => 'container',
        ],
        'toolbar' => [
            'type' => 'tpl',
            'tpl' => 'layout/toolbar.phtml',
            'priv' => 'account-user',
            'parent_id' => 'top',
            'sort' => -2,
        ],
        'header' => [
            'type' => 'tpl',
            'tpl' => 'layout/header.phtml',
            'parent_id' => 'top',
            'sort' => -1,
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
    '_public_' => [
        'menu' => [
            'type' => 'menu',
            'parent_id' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'submenu' => [
            'type' => 'menu',
            'parent_id' => 'sidebar',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'index' => [
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/index.phtml',
            'parent_id' => 'main',
            'vars' => ['act' => 'index'],
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/admin.phtml',
            'parent_id' => 'main',
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
            'parent_id' => 'head',
            'sort' => -1,
        ],
        'content' => [
            'type' => 'index',
            'tpl' => 'ent/browser.phtml',
            'parent_id' => 'main',
            'vars' => ['act' => 'browser'],
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/form.phtml',
            'parent_id' => 'main',
        ],
    ],
    'edit' => [
        'rte' => [
            'type' => 'tpl',
            'tpl' => 'head/rte.phtml',
            'parent_id' => 'head',
            'sort' => -1,
        ],
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/form.phtml',
            'parent_id' => 'main',
        ],
    ],
    'view' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/view.phtml',
            'parent_id' => 'main',
        ],
    ],
    'app/error' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'app/error.phtml',
            'parent_id' => 'main',
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
        'sidebar' => [
            'active' => false,
        ],
        'bottom' => [
            'active' => false,
        ],
        'content' => [
            'type' => 'tpl',
            'tpl' => 'account/login.phtml',
            'parent_id' => 'main',
        ],
    ],
    'account/password' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'account/password.phtml',
            'parent_id' => 'main',
        ],
    ],
];
