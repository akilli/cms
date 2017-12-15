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
            'type' => 'tpl',
            'tpl' => 'head/meta.phtml',
            'parent_id' => 'head',
            'sort' => -4,
        ],
        'all' => [
            'type' => 'tpl',
            'tpl' => 'head/all.phtml',
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
        'nav' => [
            'type' => 'nav',
            'parent_id' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'type' => 'nav',
            'parent_id' => 'sidebar',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'index' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/index.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'type' => 'pager',
            'tpl' => 'ent/pager.phtml',
        ],
        'search' => [
            'type' => 'tpl',
            'tpl' => 'ent/search.phtml',
            'parent_id' => 'sidebar',
        ],
    ],
    'admin' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/admin.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'type' => 'pager',
            'tpl' => 'ent/pager.phtml',
        ],
        'search' => [
            'type' => 'tpl',
            'tpl' => 'ent/search.phtml',
            'parent_id' => 'sidebar',
        ],
        'create' => [
            'type' => 'tpl',
            'tpl' => 'ent/create.phtml',
            'priv' => '*/edit',
            'parent_id' => 'sidebar',
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
            'type' => 'tpl',
            'tpl' => 'ent/admin.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'type' => 'pager',
            'tpl' => 'ent/pager.phtml',
        ],
        'search' => [
            'type' => 'tpl',
            'tpl' => 'ent/search.phtml',
            'parent_id' => 'sidebar',
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
            'tpl' => 'ent/edit.phtml',
            'parent_id' => 'main',
        ],
    ],
    'form' => [
        'content' => [
            'type' => 'tpl',
            'tpl' => 'ent/edit.phtml',
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
        'header' => [
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
