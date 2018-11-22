<?php
return [
    'banner' => [
        'call' => 'block\banner',
        'tpl' => 'page/banner.phtml',
    ],
    'breadcrumb' => [
        'call' => 'block\breadcrumb',
    ],
    'container' => [
        'call' => 'block\container',
        'vars' => [
            'tag' => null,
        ],
    ],
    'content' => [
        'call' => 'block\content',
        'tpl' => 'block/content.phtml',
        'vars' => [
            'title' => null,
            'content' => null,
        ],
    ],
    'create' => [
        'call' => 'block\create',
        'tpl' => 'entity/form.phtml',
        'vars' => [
            'attr' => [],
            'entity' => null,
        ],
    ],
    'edit' => [
        'call' => 'block\edit',
        'tpl' => 'entity/form.phtml',
        'vars' => [
            'attr' => [],
            'entity' => null,
        ],
    ],
    'form' => [
        'call' => 'block\form',
        'tpl' => 'entity/form.phtml',
        'vars' => [
            'attr' => [],
            'data' => [],
            'entity' => [],
            'title' => null,
        ],
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'entity/index.phtml',
        'vars' => [
            'attr' => [],
            'crit' => [],
            'entity' => null,
            'limit' => 10,
            'order' => [],
            'pager' => false,
            'parent_id' => null,
            'search' => [],
            'title' => '',
        ],
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'entity/form.phtml',
    ],
    'menu' => [
        'call' => 'block\menu',
        'vars' => [
            'mode' => null,
            'root' => false,
            'sticky' => false,
            'tag' => 'nav',
            'toggle' => false,
        ],
    ],
    'meta' => [
        'call' => 'block\meta',
        'tpl' => 'head/meta.phtml',
        'vars' => [
            'description' => '',
            'title' => '',
        ],
    ],
    'nav' => [
        'call' => 'block\nav',
        'vars' => [
            'data' => [],
            'sticky' => false,
            'tag' => 'nav',
            'title' => null,
            'toggle' => false,
        ],
    ],
    'page' => [
        'call' => 'block\page',
        'tpl' => 'entity/view.phtml',
        'vars' => [
            'attr' => [],
        ],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'entity/pager.phtml',
        'vars' => [
            'cur' => null,
            'limit' => null,
            'pages' => 10,
            'size' => null,
        ],
    ],
    'password' => [
        'call' => 'block\password',
        'tpl' => 'entity/form.phtml',
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'block/search.phtml',
        'vars' => [
            'q' => null,
        ],
    ],
    'sidebar' => [
        'call' => 'block\sidebar',
        'vars' => [
            'inherit' => null,
        ],
    ],
    'toolbar' => [
        'call' => 'block\toolbar',
        'vars' => [
            'sticky' => false,
            'tag' => 'nav',
            'title' => null,
            'toggle' => false,
        ],
    ],
    'tpl' => [
        'call' => 'block\tpl',
    ],
    'view' => [
        'call' => 'block\view',
        'tpl' => 'entity/view.phtml',
        'vars' => [
            'attr' => [],
            'data' => [],
            'entity' => null,
            'id' => null,
        ],
    ],
];
