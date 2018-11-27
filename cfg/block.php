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
    /**
     * Template
     */
    'tpl' => [
        'call' => 'block\tpl',
    ],
    'content' => [
        'call' => 'block\content',
        'cfg' => [
            'content' => null,
        ],
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
    'sidebar' => [
        'call' => 'block\sidebar',
        'cfg' => [
            'inherit' => null,
        ],
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
            'entity_id' => null,
            'limit' => 10,
            'order' => [],
            'pager' => false,
            'parent_id' => null,
            'search' => [],
            'title' => '',
        ],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'block/pager.phtml',
        'cfg' => [
            'cur' => null,
            'limit' => null,
            'pages' => 10,
            'size' => null,
        ],
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'block/search.phtml',
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
    'login' => [
        'call' => 'block\login',
        'tpl' => 'block/form.phtml',
    ],
    'password' => [
        'call' => 'block\password',
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
        ],
    ],
    'toolbar' => [
        'call' => 'block\toolbar',
    ],
    'breadcrumb' => [
        'call' => 'block\breadcrumb',
    ],
];
