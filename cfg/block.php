<?php
return [
    'breadcrumb' => [
        'call' => 'block\breadcrumb',
        'vars' => ['tag' => 'nav'],
    ],
    'container' => [
        'call' => 'block\container',
        'vars' => ['tag' => null],
    ],
    'create' => [
        'call' => 'block\create',
        'tpl' => 'ent/form.phtml',
        'vars' => ['attr' => [], 'ent' => null, 'redirect' => false, 'title' => null],
    ],
    'ent' => [
        'call' => 'block\ent',
        'tpl' => 'ent/index.phtml',
        'vars' => ['attr' => [], 'crit' => [], 'ent' => null, 'link' => false, 'opt' => [], 'title' => false, 'unpublished' => false],
    ],
    'form' => [
        'call' => 'block\form',
        'tpl' => 'ent/form.phtml',
        'vars' => ['attr' => [], 'data' => [], 'ent' => [], 'title' => null],
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'ent/index.phtml',
        'vars' => ['actions' => [], 'attr' => [], 'create' => false, 'crit' => [], 'ent' => null, 'head' => false, 'limit' => 10, 'link' => false, 'pager' => false, 'search' => false, 'unpublished' => false],
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'ent/form.phtml',
        'vars' => ['title' => null],
    ],
    'menu' => [
        'call' => 'block\menu',
        'vars' => ['tag' => 'nav'],
    ],
    'nav' => [
        'call' => 'block\nav',
        'vars' => ['data' => [], 'tag' => 'nav'],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'block/pager.phtml',
        'vars' => ['cur' => null, 'limit' => null, 'pages' => 5, 'size' => null],
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'block/search.phtml',
        'vars' => ['q' => null],
    ],
    'sidebar' => [
        'call' => 'block\sidebar',
        'vars' => ['tag' => null],
    ],
    'toolbar' => [
        'call' => 'block\toolbar',
        'vars' => ['tag' => 'nav'],
    ],
    'tpl' => [
        'call' => 'block\tpl',
    ],
    'view' => [
        'call' => 'block\view',
        'tpl' => 'ent/view.phtml',
        'vars' => ['attr' => [], 'data' => [], 'ent' => []],
    ],
];
