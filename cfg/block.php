<?php
return [
    'banner' => [
        'call' => 'block\banner',
        'tpl' => 'banner.phtml',
    ],
    'breadcrumb' => [
        'call' => 'block\breadcrumb',
    ],
    'container' => [
        'call' => 'block\container',
        'vars' => [
            'attr' => [],
            'tag' => null,
        ],
    ],
    'create' => [
        'call' => 'block\create',
        'tpl' => 'form.phtml',
        'vars' => [
            'attr' => [],
            'entity' => null,
            'redirect' => false,
            'title' => '',
        ],
    ],
    'edit' => [
        'call' => 'block\edit',
        'tpl' => 'form.phtml',
        'vars' => [
            'attr' => [],
            'entity' => null,
        ],
    ],
    'form' => [
        'call' => 'block\form',
        'tpl' => 'form.phtml',
        'vars' => [
            'attr' => [],
            'data' => [],
            'entity' => [],
            'title' => '',
        ],
    ],
    'html' => [
        'call' => 'block\html',
        'vars' => [
            'attr' => [],
            'val' => null,
            'tag' => null,
        ],
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'index.phtml',
        'vars' => [
            'actions' => [],
            'attr' => [],
            'create' => false,
            'crit' => [],
            'entity' => null,
            'inaccessible' => false,
            'limit' => 10,
            'link' => false,
            'order' => [],
            'pager' => false,
            'parent_id' => null,
            'search' => [],
            'thead' => false,
            'title' => '',
        ],
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'form.phtml',
    ],
    'menu' => [
        'call' => 'block\menu',
        'vars' => [
            'mode' => null,
            'root' => false,
            'toggle' => null,
        ],
    ],
    'meta' => [
        'call' => 'block\meta',
        'tpl' => 'meta.phtml',
        'vars' => [
            'description' => '',
            'title' => '',
        ],
    ],
    'nav' => [
        'call' => 'block\nav',
        'vars' => [
            'data' => [],
            'title' => '',
            'toggle' => null,
        ],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'pager.phtml',
        'vars' => [
            'cur' => null,
            'limit' => null,
            'pages' => 10,
            'size' => null,
        ],
    ],
    'password' => [
        'call' => 'block\password',
        'tpl' => 'form.phtml',
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'search.phtml',
        'vars' => [
            'q' => null,
        ],
    ],
    'sidebar' => [
        'call' => 'block\sidebar',
        'vars' => [
            'inherit' => false,
        ],
    ],
    'toolbar' => [
        'call' => 'block\toolbar',
    ],
    'tpl' => [
        'call' => 'block\tpl',
    ],
    'view' => [
        'call' => 'block\view',
        'tpl' => 'view.phtml',
        'vars' => [
            'attr' => [],
            'entity' => null,
            'id' => null,
        ],
    ],
];
