<?php
return [
    'container' => [
        'call' => 'block\container',
        'vars' => ['tag' => null],
    ],
    'ent' => [
        'call' => 'block\ent',
        'vars' => ['attr' => [], 'crit' => [], 'ent' => null, 'limit' => 10, 'offset' => 0, 'order' => []],
    ],
    'form' => [
        'call' => 'block\form',
        'tpl' => 'ent/form.phtml',
        'vars' => ['attr' => [], 'data' => [], 'title' => null],
    ],
    'index' => [
        'call' => 'block\index',
        'tpl' => 'ent/index.phtml',
        'vars' => ['act' => 'index', 'attr' => [], 'ent' => null, 'limit' => 10, 'pager' => 0, 'search' => false],
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
        'tpl' => 'layout/msg.phtml',
        'vars' => [],
    ],
    'nav' => [
        'call' => 'block\nav',
        'vars' => ['data' => [], 'tag' => 'nav'],
    ],
    'search' => [
        'call' => 'block\search',
        'tpl' => 'layout/search.phtml',
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
        'vars' => ['attr' => [], 'data' => []],
    ],
];
