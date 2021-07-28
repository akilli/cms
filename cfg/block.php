<?php
return [
    /** @uses block\block() */
    'block' => [
        'call' => 'block\block',
        'tag' => 'section',
        'cfg' => ['attr_id' => ['content'], 'data' => [], 'entity_id' => null, 'id' => null],
    ],
    /** @uses block\breadcrumb() */
    'breadcrumb' => ['call' => 'block\breadcrumb'],
    /** @uses block\container() */
    'container' => ['call' => 'block\container', 'cfg' => ['id' => false]],
    /** @uses block\edit() */
    'edit' => ['call' => 'block\edit', 'tpl' => 'form.phtml', 'cfg' => ['attr_id' => []]],
    /** @uses block\filter() */
    'filter' => [
        'call' => 'block\filter',
        'tpl' => 'filter.phtml',
        'cfg' => ['attr' => [], 'data' => [], 'q' => null, 'search' => false],
    ],
    /** @uses block\html() */
    'html' => ['call' => 'block\html'],
    /** @uses block\index() */
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
    /** @uses block\login() */
    'login' => ['call' => 'block\login', 'tpl' => 'form.phtml'],
    /** @uses block\menu() */
    'menu' => ['call' => 'block\menu', 'cfg' => ['root' => false, 'submenu' => false, 'toggle' => false, 'url' => null]],
    /** @uses block\meta() */
    'meta' => ['call' => 'block\meta', 'tpl' => 'meta.phtml'],
    /** @uses block\nav() */
    'nav' => ['call' => 'block\nav', 'cfg' => ['data' => [], 'title' => null, 'toggle' => false]],
    /** @uses block\pager() */
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'pager.phtml',
        'cfg' => ['cur' => null, 'limit' => null, 'limits' => [], 'pages' => 10, 'size' => null],
    ],
    /** @uses block\profile() */
    'profile' => ['call' => 'block\profile', 'tpl' => 'form.phtml', 'cfg' => ['attr_id' => []]],
    /** @uses block\tag() */
    'tag' => ['call' => 'block\tag', 'cfg' => ['attr' => [], 'val' => null]],
    /** @uses block\title() */
    'title' => ['call' => 'block\title', 'cfg' => ['text' => null]],
    /** @uses block\toolbar() */
    'toolbar' => ['call' => 'block\toolbar'],
    /** @uses block\tpl() */
    'tpl' => ['call' => 'block\tpl'],
    /** @uses block\view() */
    'view' => ['call' => 'block\view', 'cfg' => ['attr_id' => [], 'data' => [], 'entity_id' => null, 'id' => null]],
];
