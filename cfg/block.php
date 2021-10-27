<?php
declare(strict_types=1);

namespace block;

return [
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
        ],
    ],
    'html' => [
        'call' => html(...),
    ],
    'index' => [
        'call' => index(...),
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
        'call' => login(...),
        'tpl' => 'form.phtml',
    ],
    'menu' => [
        'call' => menu(...),
        'cfg' => [
            'id' => null,
        ],
    ],
    'meta' => [
        'call' => meta(...),
        'tpl' => 'meta.phtml',
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
        ],
    ],
    'tag' => [
        'call' => tag(...),
        'cfg' => [
            'attr' => [],
            'val' => null,
        ],
    ],
    'title' => [
        'call' => title(...),
        'cfg' => [
            'text' => null,
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
