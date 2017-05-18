<?php
return [
    '_all_' => [
        'root' => [
            'type' => 'template',
            'template' => 'layout/root.phtml',
        ],
        'head' => [
            'type' => 'template',
            'template' => 'layout/head.phtml',
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
        'pager' => [
            'type' => 'pager',
            'template' => 'entity/pager.phtml',
            'parent' => 'content',
        ],
        'search' => [
            'type' => 'template',
            'template' => 'entity/search.phtml',
            'parent' => 'right',
        ],
        'create' => [
            'type' => 'template',
            'template' => 'entity/create.phtml',
            'privilege' => '*/edit',
            'parent' => 'right',
        ],
        'import' => [
            'type' => 'template',
            'template' => 'entity/import.phtml',
            'privilege' => '*/import',
            'parent' => 'right',
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
            'parent' => 'right',
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
    'account-user' => [
        'toolbar' => [
            'type' => 'toolbar',
            'template' => 'account/toolbar.phtml',
            'parent' => 'top',
        ],
    ],
    'account/password' => [
         'content' => [
            'type' => 'template',
            'template' => 'account/password.phtml',
            'parent' => 'main',
        ],
    ],
    'account/login' => [
        'content' => [
            'type' => 'template',
            'template' => 'account/login.phtml',
            'parent' => 'main',
        ],
    ],
    'page/index' => [
        'nav' => [
            'type' => 'nav',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'type' => 'nav',
            'parent' => 'right',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'page/view' => [
        'nav' => [
            'type' => 'nav',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'type' => 'nav',
            'parent' => 'right',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'project/home' => [
        'nav' => [
            'type' => 'nav',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'content' => [
            'type' => 'template',
            'template' => 'entity/view.phtml',
            'vars' => ['context' => 'home'],
            'parent' => 'main',
        ],
    ],
];
