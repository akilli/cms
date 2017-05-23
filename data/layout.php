<?php
return [
    '_all_' => [
        'root' => [
            'section' => 'template',
            'template' => 'layout/root.phtml',
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
            'parent' => 'top',
            'sort' => -1,
        ],
        'message' => [
            'section' => 'message',
            'template' => 'layout/message.phtml',
        ],
        'main' => [
            'section' => 'container',
        ],
        'sidebar' => [
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
            'parent' => 'main',
        ],
        'pager' => [
            'section' => 'pager',
            'template' => 'entity/pager.phtml',
            'parent' => 'content',
        ],
        'search' => [
            'section' => 'template',
            'template' => 'entity/search.phtml',
            'parent' => 'sidebar',
        ],
        'create' => [
            'section' => 'template',
            'template' => 'entity/create.phtml',
            'privilege' => '*/edit',
            'parent' => 'sidebar',
        ],
        'import' => [
            'section' => 'template',
            'template' => 'entity/import.phtml',
            'privilege' => '*/import',
            'parent' => 'sidebar',
        ],
        'export' => [
            'section' => 'template',
            'template' => 'entity/export.phtml',
            'privilege' => '*/export',
            'parent' => 'sidebar',
        ],
    ],
    'action-index' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/index.phtml',
            'parent' => 'main',
        ],
        'pager' => [
            'section' => 'pager',
            'template' => 'entity/pager.phtml',
            'parent' => 'content',
        ],
        'search' => [
            'section' => 'template',
            'template' => 'entity/search.phtml',
            'parent' => 'sidebar',
        ],
    ],
    'action-edit' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/edit.phtml',
            'parent' => 'main',
        ],
    ],
    'action-view' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/view.phtml',
            'parent' => 'main',
        ],
    ],
    'account-user' => [
        'toolbar' => [
            'section' => 'template',
            'template' => 'account/toolbar.phtml',
            'parent' => 'top',
            'sort' => -2,
        ],
    ],
    'account/password' => [
         'content' => [
            'section' => 'template',
            'template' => 'account/password.phtml',
            'parent' => 'main',
        ],
    ],
    'account/login' => [
        'header' => [
            'active' => false,
        ],
        'content' => [
            'section' => 'template',
            'template' => 'account/login.phtml',
            'parent' => 'main',
        ],
    ],
    'page/index' => [
        'nav' => [
            'section' => 'nav',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'section' => 'nav',
            'parent' => 'sidebar',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'page/view' => [
        'nav' => [
            'section' => 'nav',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'subnav' => [
            'section' => 'nav',
            'parent' => 'sidebar',
            'vars' => ['mode' => 'sub'],
        ],
    ],
    'project/home' => [
        'nav' => [
            'section' => 'nav',
            'parent' => 'top',
            'vars' => ['mode' => 'top'],
        ],
        'content' => [
            'section' => 'template',
            'template' => 'entity/view.phtml',
            'parent' => 'main',
        ],
        'search' => [
            'section' => 'template',
            'template' => 'entity/search.phtml',
            'parent' => 'sidebar',
            'vars' => ['action' => 'page/index'],
        ],
    ],
];
