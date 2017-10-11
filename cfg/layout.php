<?php
return [
    '_all_' => [
        'root' => [
            'section' => 'tpl',
            'tpl' => 'layout/root.phtml',
            'parent_id' => null,
        ],
        'head' => [
            'section' => 'tpl',
            'tpl' => 'layout/head.phtml',
        ],
        'top' => [
            'section' => 'container',
        ],
        'header' => [
            'section' => 'tpl',
            'tpl' => 'layout/header.phtml',
            'parent_id' => 'top',
            'sort' => -1,
        ],
        'msg' => [
            'section' => 'msg',
            'tpl' => 'layout/msg.phtml',
        ],
        'left' => [
            'section' => 'container',
            'vars' => ['tag' => 'aside'],
        ],
        'main' => [
            'section' => 'container',
        ],
        'right' => [
            'section' => 'container',
            'vars' => ['tag' => 'aside'],
        ],
        'bottom' => [
            'section' => 'container',
        ],
    ],
    'act-admin' => [
        'content' => [
            'section' => 'tpl',
            'tpl' => 'entity/admin.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'section' => 'pager',
            'tpl' => 'entity/pager.phtml',
            'parent_id' => 'content',
        ],
        'search' => [
            'section' => 'tpl',
            'tpl' => 'entity/search.phtml',
            'parent_id' => 'right',
        ],
        'create' => [
            'section' => 'tpl',
            'tpl' => 'entity/create.phtml',
            'privilege' => '*/edit',
            'parent_id' => 'right',
        ],
        'import' => [
            'section' => 'tpl',
            'tpl' => 'entity/import.phtml',
            'privilege' => '*/import',
            'parent_id' => 'right',
        ],
    ],
    'act-index' => [
        'content' => [
            'section' => 'tpl',
            'tpl' => 'entity/index.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'section' => 'pager',
            'parent_id' => 'content',
        ],
        'search' => [
            'section' => 'tpl',
            'tpl' => 'entity/search.phtml',
            'parent_id' => 'right',
        ],
    ],
    'act-edit' => [
        'content' => [
            'section' => 'tpl',
            'tpl' => 'entity/edit.phtml',
            'parent_id' => 'main',
        ],
    ],
    'act-form' => [
        'content' => [
            'section' => 'tpl',
            'tpl' => 'entity/edit.phtml',
            'parent_id' => 'main',
        ],
    ],
    'act-view' => [
        'content' => [
            'section' => 'tpl',
            'tpl' => 'entity/view.phtml',
            'parent_id' => 'main',
        ],
    ],
    'account-user' => [
        'toolbar' => [
            'section' => 'tpl',
            'tpl' => 'account/toolbar.phtml',
            'parent_id' => 'top',
            'sort' => -2,
        ],
    ],
    'account/password' => [
         'content' => [
            'section' => 'tpl',
            'tpl' => 'account/password.phtml',
            'parent_id' => 'main',
        ],
    ],
    'account/login' => [
        'header' => [
            'active' => false,
        ],
        'content' => [
            'section' => 'tpl',
            'tpl' => 'account/login.phtml',
            'parent_id' => 'main',
        ],
    ],
    'page/index' => [
        'nav' => [
            'section' => 'nav',
            'parent_id' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'section' => 'nav',
            'parent_id' => 'right',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'page/view' => [
        'nav' => [
            'section' => 'nav',
            'parent_id' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'section' => 'nav',
            'parent_id' => 'right',
            'vars' => ['mode' => 'sub'],
        ],
    ],
];
