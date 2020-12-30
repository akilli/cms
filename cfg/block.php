<?php
return [
    'block' => [
        'call' => 'block\block',
        'cfg' => ['attr_id' => ['content'], 'data' => [], 'entity_id' => null, 'id' => null, 'tag' => 'section'],
    ],
    'breadcrumb' => ['call' => 'block\breadcrumb'],
    'container' => ['call' => 'block\container', 'cfg' => ['id' => false, 'tag' => null]],
    'edit' => ['call' => 'block\edit', 'tpl' => 'form.phtml', 'cfg' => ['attr_id' => []]],
    'filter' => [
        'call' => 'block\filter',
        'tpl' => 'filter.phtml',
        'cfg' => ['attr' => [], 'data' => [], 'q' => null, 'search' => false],
    ],
    'html' => ['call' => 'block\html'],
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
            'parent_id' => null,
            'search' => [],
            'sort' => false,
            'title' => null,
        ],
    ],
    'login' => ['call' => 'block\login', 'tpl' => 'form.phtml'],
    'menu' => [
        'call' => 'block\menu',
        'cfg' => ['root' => false, 'submenu' => false, 'tag' => 'nav', 'toggle' => false, 'url' => null],
    ],
    'meta' => ['call' => 'block\meta', 'tpl' => 'meta.phtml'],
    'nav' => [
        'call' => 'block\nav',
        'cfg' => ['data' => [], 'tag' => 'nav', 'title' => null, 'toggle' => false],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'pager.phtml',
        'cfg' => ['cur' => null, 'limit' => null, 'limits' => [], 'pages' => 10, 'size' => null],
    ],
    'profile' => ['call' => 'block\profile', 'tpl' => 'form.phtml', 'cfg' => ['attr_id' => []]],
    'tag' => ['call' => 'block\tag', 'cfg' => ['attr' => [], 'tag' => null, 'val' => null]],
    'title' => ['call' => 'block\title', 'cfg' => ['text' => null]],
    'toolbar' => ['call' => 'block\toolbar'],
    'tpl' => ['call' => 'block\tpl', 'cfg' => ['tpl' => null]],
    'view' => [
        'call' => 'block\view',
        'cfg' => ['attr_id' => [], 'data' => [], 'entity_id' => null, 'id' => null, 'tag' => null],
    ],
];
