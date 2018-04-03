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
        'tpl' => 'form.phtml',
        'vars' => ['attr' => [], 'ent' => null, 'redirect' => false, 'title' => ''],
    ],
    'form' => [
        'call' => 'block\form',
        'tpl' => 'form.phtml',
        'vars' => ['attr' => [], 'data' => [], 'ent' => [], 'title' => ''],
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'index.phtml',
        'vars' => ['actions' => [], 'attr' => [], 'create' => false, 'crit' => [], 'ent' => null, 'head' => false, 'limit' => 10, 'link' => false, 'order' => [], 'pager' => false, 'search' => false, 'title' => '', 'unpublished' => false],
    ],
    'login' => [
        'call' => 'block\login',
        'tpl' => 'form.phtml',
        'vars' => ['title' => null],
    ],
    'menu' => [
        'call' => 'block\menu',
        'vars' => ['class' => null, 'mode' => null, 'root' => false, 'tag' => 'nav'],
    ],
    'nav' => [
        'call' => 'block\nav',
        'vars' => ['class' => null, 'data' => [], 'tag' => 'nav'],
    ],
    'pager' => [
        'call' => 'block\pager',
        'tpl' => 'pager.phtml',
        'vars' => ['cur' => null, 'limit' => null, 'pages' => 5, 'size' => null],
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'search.phtml',
        'vars' => ['q' => null],
    ],
    'sidebar' => [
        'call' => 'block\sidebar',
        'vars' => ['tag' => null],
    ],
    'toolbar' => [
        'call' => 'block\toolbar',
        'vars' => ['class' => null, 'tag' => 'nav'],
    ],
    'tpl' => [
        'call' => 'block\tpl',
    ],
    'view' => [
        'call' => 'block\view',
        'tpl' => 'view.phtml',
        'vars' => ['attr' => [], 'data' => [], 'ent' => []],
    ],
];
