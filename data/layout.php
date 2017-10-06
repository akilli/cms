<?php
return [
    '_all_' => [
        'root' => [
            'section' => 'template',
            'template' => 'layout/root.phtml',
            'parent_id' => null,
        ],
        'head' => [
            'section' => 'template',
            'template' => 'layout/head.phtml',
        ],
        'top' => [
            'section' => 'container',
        ],
        'header' => [
            'section' => 'template',
            'template' => 'layout/header.phtml',
            'parent_id' => 'top',
            'sort' => -1,
        ],
        'message' => [
            'section' => 'message',
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
    'action-admin' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/admin.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'section' => 'pager',
            'parent_id' => 'content',
        ],
        'search' => [
            'section' => 'template',
            'template' => 'entity/search.phtml',
            'parent_id' => 'right',
        ],
        'create' => [
            'section' => 'template',
            'template' => 'entity/create.phtml',
            'privilege' => '*/edit',
            'parent_id' => 'right',
        ],
    ],
    'action-index' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/index.phtml',
            'parent_id' => 'main',
        ],
        'pager' => [
            'section' => 'pager',
            'parent_id' => 'content',
        ],
        'search' => [
            'section' => 'template',
            'template' => 'entity/search.phtml',
            'parent_id' => 'right',
        ],
    ],
    'action-edit' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/edit.phtml',
            'parent_id' => 'main',
        ],
    ],
    'action-form' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/edit.phtml',
            'parent_id' => 'main',
        ],
    ],
    'action-view' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/view.phtml',
            'parent_id' => 'main',
        ],
    ],
    'account-user' => [
        'toolbar' => [
            'section' => 'template',
            'template' => 'account/toolbar.phtml',
            'parent_id' => 'top',
            'sort' => -2,
        ],
    ],
    'account/password' => [
         'content' => [
            'section' => 'template',
            'template' => 'account/password.phtml',
            'parent_id' => 'main',
        ],
    ],
    'account/login' => [
        'header' => [
            'active' => false,
        ],
        'content' => [
            'section' => 'template',
            'template' => 'account/login.phtml',
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
