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
    ],
    'container' => [
        'call' => container(...),
        'cfg' => [
            'id' => false,
        ],
    ],
    'edit' => [
        'call' => edit(...),
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
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
    ],
    'meta' => [
        'call' => meta(...),
        'tpl' => 'meta.phtml',
    ],
    'nav' => [
        'call' => nav(...),
        'cfg' => [
            'data' => [],
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
    'toolbar' => [
        'call' => toolbar(...),
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
