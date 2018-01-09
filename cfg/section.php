<?php
return [
    'container' => [
        'opt' => ['tag' => null],
    ],
    'ent' => [
        'opt' => ['crit' => [], 'eId' => null, 'opt' => []],
        'vars' => ['attr' => [], 'data' => [], 'ent' => null, 'title' => null],
    ],
    'index' => [
        'opt' => ['crit' => [], 'eId' => null, 'opt' => []],
        'vars' => ['attr' => [], 'data' => [], 'ent' => null, 'params' => [], 'title' => null],
    ],
    'msg' => [
        'tpl' => 'layout/msg.phtml',
        'vars' => ['data' => []],
    ],
    'menu' => [
        'opt' => ['mode' => null],
    ],
    'pager' => [
        'opt' => ['params' => []],
        'vars' => ['limit' => 0, 'links' => [], 'size' => 0],
    ],
    'tpl' => [
        'vars' => ['id' => null, 'tpl' => null],
    ],
];
