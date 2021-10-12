<?php
declare(strict_types=1);

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
            'action' => [],
            'attr_id' => [],
            'crit' => [],
            'entity_id' => null,
            'filter' => [],
            'limit' => 20,
            'order' => ['id' => 'desc'],
            'pager' => false,
            'search' => [],
            'sortable' => false,
            'table' => false,
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
