<?php
return [
    'block' => [
        'call' => 'block\block',
        'tag' => 'section',
        'cfg' => [
            'attr_id' => ['content'],
            'data' => [],
            'entity_id' => null,
            'id' => null,
        ],
    ],
    'breadcrumb' => [
        'call' => 'block\breadcrumb',
    ],
    'container' => [
        'call' => 'block\container',
        'cfg' => [
            'id' => false,
        ],
    ],
    'edit' => [
        'call' => 'block\edit',
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'filter' => [
        'call' => 'block\filter',
        'tpl' => 'filter.phtml',
        'cfg' => [
            'attr' => [],
            'data' => [],
            'q' => null,
            'searchable' => false,
        ],
    ],
    'html' => [
        'call' => 'block\html',
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'index.phtml',
        'cfg' => [
            'attr_id' => [],
            'crit' => [],
            'entity_id' => null,
            'filter' => [],
            'limits' => [10, 20, 50, 0],
            'link' => null,
            'order' => ['id' => 'desc'],
            'pager' => null,
            'search' => [],
            'sortable' => false,
            'title' => null,
        ],
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'form.phtml',
    ],
    'menu' => [
        'call' => 'block\menu',
    ],
    'meta' => [
        'call' => 'block\meta',
        'tpl' => 'meta.phtml',
    ],
    'nav' => [
        'call' => 'block\nav',
        'cfg' => [
            'data' => [],
            'title' => null,
        ],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'pager.phtml',
        'cfg' => [
            'cur' => null,
            'limit' => null,
            'limits' => [],
            'pages' => 10,
            'size' => null,
        ],
    ],
    'profile' => [
        'call' => 'block\profile',
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'tag' => [
        'call' => 'block\tag',
        'cfg' => [
            'attr' => [],
            'val' => null,
        ],
    ],
    'title' => [
        'call' => 'block\title',
        'cfg' => [
            'text' => null,
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
        'cfg' => [
            'attr_id' => [],
            'data' => [],
            'entity_id' => null,
            'id' => null,
        ],
    ],
];
