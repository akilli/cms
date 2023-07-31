<?php
declare(strict_types=1);

namespace block;

return [
    'add' => [
        'call' => add(...),
        'cfg' => [
            'entity_id' => null,
        ],
    ],
    'block' => [
        'call' => block(...),
        'tag' => 'section',
        'cfg' => [
            'attr_id' => ['content'],
            'data' => [],
            'entity_id' => null,
            'id' => null,
        ],
    ],
    'breadcrumb' => [
        'call' => breadcrumb(...),
        'cfg' => [
            'id' => null,
        ],
    ],
    'container' => [
        'call' => container(...),
        'cfg' => [
            'id' => false,
        ],
    ],
    'filter' => [
        'call' => filter(...),
        'tpl' => 'filter.phtml',
        'cfg' => [
            'attr' => [],
            'data' => [],
            'q' => null,
            'searchable' => false,
        ],
    ],
    'form' => [
        'call' => form(...),
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
            'title' => null,
        ],
    ],
    'head' => [
        'call' => head(...),
        'tpl' => 'head.phtml',
    ],
    'header' => [
        'call' => header(...),
        'tpl' => 'header.phtml',
        'cfg' => [
            'title' => null,
        ],
    ],
    'index' => [
        'call' => index(...),
        'tpl' => 'index.phtml',
        'cfg' => [
            'action' => [],
            'add' => false,
            'attr_id' => [],
            'crit' => [],
            'entity_id' => null,
            'filter' => [],
            'filterable' => false,
            'limit' => 20,
            'order' => ['id' => 'desc'],
            'pager' => false,
            'search' => [],
            'searchable' => false,
            'sortable' => false,
            'table' => false,
            'title' => null,
        ],
    ],
    'login' => [
        'call' => login(...),
        'tpl' => 'form.phtml',
        'cfg' => [
            'title' => null,
        ],
    ],
    'menu' => [
        'call' => menu(...),
        'cfg' => [
            'id' => null,
        ],
    ],
    'pager' => [
        'call' => pager(...),
        'tpl' => 'pager.phtml',
        'cfg' => [
            'cur' => null,
            'limit' => null,
            'pages' => 10,
            'size' => null,
        ],
    ],
    'profile' => [
        'call' => profile(...),
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
            'title' => null,
        ],
    ],
    'tag' => [
        'call' => tag(...),
        'cfg' => [
            'attr' => [],
            'val' => null,
        ],
    ],
    'tpl' => [
        'call' => tpl(...),
    ],
    'view' => [
        'call' => view(...),
        'cfg' => [
            'attr_id' => [],
            'data' => [],
            'entity_id' => null,
            'id' => null,
        ],
    ],
];
