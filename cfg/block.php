<?php
return [
    'block' => [
        'call' => 'block\block\render',
        'cfg' => ['attr_id' => ['content'], 'data' => [], 'entity_id' => null, 'id' => null, 'tag' => 'section'],
    ],
    'breadcrumb' => ['call' => 'block\breadcrumb\render'],
    'container' => ['call' => 'block\container\render', 'cfg' => ['id' => false, 'tag' => null]],
    'edit' => ['call' => 'block\edit\render', 'cfg' => ['attr_id' => [], 'tpl' => 'form.phtml']],
    'filter' => [
        'call' => 'block\filter\render',
        'cfg' => ['attr' => [], 'data' => [], 'q' => null, 'search' => false, 'tpl' => 'filter.phtml'],
    ],
    'html' => ['call' => 'block\html\render'],
    'index' => [
        'call' => 'block\index\render',
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
            'tpl' => 'index.phtml',
        ],
    ],
    'login' => ['call' => 'block\login\render', 'cfg' => ['tpl' => 'form.phtml']],
    'menu' => [
        'call' => 'block\menu\render',
        'cfg' => ['root' => false, 'submenu' => false, 'tag' => 'nav', 'toggle' => false, 'url' => null],
    ],
    'meta' => ['call' => 'block\meta\render', 'cfg' => ['tpl' => 'meta.phtml']],
    'nav' => [
        'call' => 'block\nav\render',
        'cfg' => ['data' => [], 'tag' => 'nav', 'title' => null, 'toggle' => false],
    ],
    'pager' => [
        'call' => 'block\pager\render',
        'cfg' => ['cur' => null, 'limit' => null, 'limits' => [], 'pages' => 10, 'size' => null, 'tpl' => 'pager.phtml'],
    ],
    'profile' => ['call' => 'block\profile\render', 'cfg' => ['attr_id' => [], 'tpl' => 'form.phtml']],
    'tag' => ['call' => 'block\tag\render', 'cfg' => ['attr' => [], 'tag' => null, 'val' => null]],
    'title' => ['call' => 'block\title\render', 'cfg' => ['text' => null]],
    'toolbar' => ['call' => 'block\toolbar\render'],
    'tpl' => ['call' => 'block\tpl\render', 'cfg' => ['tpl' => null]],
    'view' => [
        'call' => 'block\view\render',
        'cfg' => ['attr_id' => [], 'data' => [], 'entity_id' => null, 'id' => null, 'tag' => null],
    ],
];
