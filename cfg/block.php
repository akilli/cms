<?php
return [
    /**
     * Container
     */
    'container' => [
        'call' => 'block\container',
        'cfg' => [
            'tag' => null,
        ],
    ],
    'root' => [
        'call' => 'block\root',
    ],
    /**
     * Content
     */
    'msg' => [
        'call' => 'block\msg',
    ],
    'content' => [
        'call' => 'block\content',
        'cfg' => [
            'content' => null,
        ],
    ],
    'db' => [
        'call' => 'block\db',
        'cfg' => [
            'entity_id' => null,
            'id' => null,
        ],
    ],
    /**
     * Template
     */
    'tpl' => [
        'call' => 'block\tpl',
    ],
    'meta' => [
        'call' => 'block\meta',
        'tpl' => 'head/meta.phtml',
    ],
    /**
     * View
     */
    'view' => [
        'call' => 'block\view',
        'tpl' => 'block/view.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'banner' => [
        'call' => 'block\banner',
        'tpl' => 'page/banner.phtml',
    ],
    /**
     * Index
     */
    'index' => [
        'call' => 'block\index',
        'tpl' => 'block/index.phtml',
        'cfg' => [
            'attr_id' => [],
            'crit' => [],
            'distinct' => [],
            'entity_id' => null,
            'filter' => [],
            'limit' => [10, 20, 50, 0],
            'order' => ['id' => 'desc'],
            'pager' => null,
            'parent_id' => null,
            'search' => [],
            'title' => null,
        ],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'block/pager.phtml',
        'cfg' => [
            'cur' => null,
            'limit' => null,
            'limits' => [],
            'pages' => 10,
            'size' => null,
        ],
    ],
    /**
     * Form
     */
    'form' => [
        'call' => 'block\form',
        'tpl' => 'block/form.phtml',
        'cfg' => [
            'attr_id' => [],
            'data' => [],
            'entity_id' => null,
            'title' => null,
        ],
    ],
    'create' => [
        'call' => 'block\create',
        'tpl' => 'block/form.phtml',
        'cfg' => [
            'attr_id' => [],
            'entity_id' => null,
        ],
    ],
    'edit' => [
        'call' => 'block\edit',
        'tpl' => 'block/form.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'password' => [
        'call' => 'block\password',
        'tpl' => 'block/form.phtml',
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'block/form.phtml',
    ],
    /**
     * Navigation
     */
    'nav' => [
        'call' => 'block\nav',
        'cfg' => [
            'data' => [],
            'tag' => 'nav',
            'title' => null,
            'toggle' => false,
        ],
    ],
    'menu' => [
        'call' => 'block\menu',
        'cfg' => [
            'root' => false,
            'submenu' => false,
            'tag' => 'nav',
            'toggle' => false,
            'url' => null,
        ],
    ],
    'toolbar' => [
        'call' => 'block\toolbar',
    ],
    'breadcrumb' => [
        'call' => 'block\breadcrumb',
    ],
];
