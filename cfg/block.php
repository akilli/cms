<?php
return [
    'block' => [
        'call' => 'block\block\render',
        'cfg' => ['attr_id' => ['content'], 'data' => [], 'entity_id' => null, 'id' => null, 'tag' => 'section'],
    ],
    'breadcrumb' => ['call' => 'block\breadcrumb\render'],
    'container' => ['call' => 'block\container\render', 'cfg' => ['id' => false, 'tag' => null]],
    'edit' => ['call' => 'block\edit\render', 'tpl' => 'form.phtml', 'cfg' => ['attr_id' => []]],
    'filter' => [
        'call' => 'block\filter\render',
        'tpl' => 'filter.phtml',
        'cfg' => ['attr' => [], 'data' => [], 'q' => null, 'search' => false],
    ],
    'html' => ['call' => 'block\html\render'],
    'index' => [
        'call' => 'block\index\render',
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
    'login' => ['call' => 'block\login\render', 'tpl' => 'form.phtml'],
    'menu' => [
        'call' => 'block\menu\render',
        'cfg' => ['root' => false, 'submenu' => false, 'tag' => 'nav', 'toggle' => false, 'url' => null],
    ],
    'meta' => ['call' => 'block\meta\render', 'tpl' => 'meta.phtml'],
    'nav' => [
        'call' => 'block\nav\render',
        'cfg' => ['data' => [], 'tag' => 'nav', 'title' => null, 'toggle' => false],
    ],
    'pager' => [
        'call' => 'block\pager\render',
        'tpl' => 'pager.phtml',
        'cfg' => ['cur' => null, 'limit' => null, 'limits' => [], 'pages' => 10, 'size' => null],
    ],
    'profile' => ['call' => 'block\profile\render', 'tpl' => 'form.phtml', 'cfg' => ['attr_id' => []]],
    'tag' => ['call' => 'block\tag\render', 'cfg' => ['attr' => [], 'tag' => null, 'val' => null]],
    'title' => ['call' => 'block\title\render', 'cfg' => ['text' => null]],
    'toolbar' => ['call' => 'block\toolbar\render'],
    'tpl' => ['call' => 'block\tpl\render'],
    'view' => [
        'call' => 'block\view\render',
        'cfg' => ['attr_id' => [], 'data' => [], 'entity_id' => null, 'id' => null, 'tag' => null],
    ],
];
