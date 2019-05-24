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
    'title' => [
        'call' => 'block\title',
        'cfg' => [
            'text' => null,
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
        'tpl' => 'meta.phtml',
    ],
    /**
     * View
     */
    'view' => [
        'call' => 'block\view',
        'tpl' => 'view.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'banner' => [
        'call' => 'block\banner',
        'tpl' => 'banner.phtml',
    ],
    /**
     * Index
     */
    'index' => [
        'call' => 'block\index',
        'tpl' => 'index.phtml',
        'cfg' => [
            'attr_id' => [],
            'crit' => [],
            'entity_id' => null,
            'filter' => [],
            'limit' => [10, 20, 50, 0],
            'link' => null,
            'mode' => null,
            'order' => ['id' => 'desc'],
            'pager' => null,
            'parent_id' => null,
            'search' => [],
            'sort' => false,
            'title' => null,
        ],
    ],
    'filter' => [
        'call' => 'block\filter',
        'tpl' => 'filter.phtml',
        'cfg' => [
            'attr' => [],
            'data' => [],
            'q' => null,
            'search' => false,
        ],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'pager.phtml',
        'cfg' => [
            'cur' => null,
            'limit' => null,
            'limits' => [],
            'pages' => 10,
            'size' => null,
        ],
    ],
    /**
     * DB
     */
    'db' => [
        'call' => 'block\db',
        'cfg' => [
            'entity_id' => null,
            'id' => null,
        ],
    ],
    'content' => [
        'call' => 'block\content',
        'cfg' => [
            'attr_id' => ['title', 'media', 'content'],
            'data' => [],
        ],
    ],
    /**
     * Form
     */
    'edit' => [
        'call' => 'block\edit',
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'profile' => [
        'call' => 'block\profile',
        'tpl' => 'form.phtml',
        'cfg' => [
            'attr_id' => [],
        ],
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'form.phtml',
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
