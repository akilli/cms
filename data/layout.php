<?php
return [
    '_base_' => [
        'root' => [
            'type' => 'template',
            'template' => 'layout/root.phtml',
        ],
        'head' => [
            'type' => 'template',
            'template' => 'layout/head.phtml',
            'vars' => ['meta' => []],
        ],
        'top' => [
            'type' => 'container',
        ],
        'left' => [
            'type' => 'container',
            'vars' => ['tag' => 'aside'],
        ],
        'message' => [
            'type' => 'message',
            'template' => 'layout/message.phtml',
        ],
        'main' => [
            'type' => 'container',
        ],
        'right' => [
            'type' => 'container',
            'vars' => ['tag' => 'aside'],
        ],
        'bottom' => [
            'type' => 'container',
        ],
    ],
    'action-admin' => [
        'content' => [
            'type' => 'template',
            'template' => 'entity/admin.phtml',
            'vars' => ['context' => 'admin'],
            'parent' => 'main',
        ],
        'create' => [
            'type' => 'template',
            'template' => 'entity/create.phtml',
            'privilege' => 'edit',
            'parent' => 'content',
        ],
        'import' => [
            'type' => 'template',
            'template' => 'entity/import.phtml',
            'privilege' => 'import',
            'parent' => 'content',
        ],
        'pager' => [
            'type' => 'pager',
            'template' => 'entity/pager.phtml',
            'parent' => 'content',
        ],
        'search' => [
            'type' => 'template',
            'template' => 'entity/search.phtml',
            'parent' => 'content',
        ],
    ],
    'action-index' => [
        'content' => [
            'type' => 'template',
            'template' => 'entity/index.phtml',
            'vars' => ['context' => 'index'],
            'parent' => 'main',
        ],
        'pager' => [
            'type' => 'pager',
            'template' => 'entity/pager.phtml',
            'parent' => 'content',
        ],
        'search' => [
            'type' => 'template',
            'template' => 'entity/search.phtml',
            'parent' => 'content',
        ],
    ],
    'action-edit' => [
        'content' => [
            'type' => 'template',
            'template' => 'entity/edit.phtml',
            'vars' => ['context' => 'edit'],
            'parent' => 'main',
        ],
    ],
    'action-view' => [
        'content' => [
            'type' => 'template',
            'template' => 'entity/view.phtml',
            'vars' => ['context' => 'view'],
            'parent' => 'main',
        ],
    ],
    'account-registered' => [
        'toolbar' => [
            'type' => 'toolbar',
            'template' => 'account/toolbar.phtml',
            'parent' => 'top',
        ],
    ],
    'account.password' => [
         'content' => [
            'type' => 'template',
            'template' => 'account/password.phtml',
            'parent' => 'main',
        ],
    ],
    'account.login' => [
        'content' => [
            'type' => 'template',
            'template' => 'account/login.phtml',
            'parent' => 'main',
        ],
    ],
    'page.index' => [
        'nav' => [
            'type' => 'tree',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
    ],
    'page.view' => [
        'nav' => [
            'type' => 'tree',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'type' => 'tree',
            'parent' => 'right',
            'vars' => ['mode' => 'sub'],
        ],
    ],
];
