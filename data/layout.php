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
        'left' => [
            'section' => 'container',
            'vars' => ['tag' => 'aside'],
        ],
        'message' => [
            'section' => 'message',
            'template' => 'layout/message.phtml',
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
            'vars' => ['context' => 'admin'],
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
            'parent' => 'content',
        ],
    ],
    'action-index' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/index.phtml',
            'vars' => ['context' => 'index'],
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
            'parent' => 'content',
        ],
    ],
    'action-edit' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/edit.phtml',
            'vars' => ['context' => 'edit'],
            'parent' => 'main',
        ],
    ],
    'action-view' => [
        'content' => [
            'section' => 'template',
            'template' => 'entity/view.phtml',
            'vars' => ['context' => 'view'],
            'parent' => 'main',
        ],
    ],
    'account-user' => [
        'toolbar' => [
            'section' => 'toolbar',
            'template' => 'account/toolbar.phtml',
            'parent' => 'top',
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
            'parent' => 'right',
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
            'parent' => 'right',
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
            'vars' => ['context' => 'home'],
            'parent' => 'main',
        ],
    ],
];
