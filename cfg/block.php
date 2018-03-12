<?php
return [
    'container' => [
        'call' => 'block\container',
        'vars' => ['tag' => null],
    ],
    'ent' => [
        'call' => 'block\ent',
        'vars' => ['attr' => [], 'crit' => [], 'ent' => null, 'opt' => []],
    ],
    'form' => [
        'call' => 'block\form',
        'tpl' => 'ent/form.phtml',
        'vars' => ['attr' => [], 'data' => [], 'ent' => [], 'title' => null],
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'ent/index.phtml',
        'vars' => ['act' => 'index', 'actions' => [], 'attr' => [], 'ent' => null, 'limit' => 10, 'pager' => false, 'search' => false],
    ],
    'js' => [
        'call' => 'block\js',
        'tpl' => 'app/app.js',
        'vars' => ['file' => [], 'i18n' => []],
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
    'meta' => [
        'call' => 'block\meta',
        'tpl' => 'head/meta.phtml',
        'vars' => ['desc' => null, 'title' => null],
    ],
    'msg' => [
        'call' => 'block\msg',
        'tpl' => 'block/msg.phtml',
        'vars' => [],
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
    'root' => [
        'call' => 'block\root',
        'tpl' => 'root.phtml',
        'vars' => ['act' => null, 'area' => null, 'ent' => null, 'id' => null, 'lang' => null],
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'block/search.phtml',
        'vars' => ['q' => null],
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
