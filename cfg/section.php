<?php
return [
    'container' => [
        'opt' => ['tag' => null],
    ],
    'ent' => [
        'opt' => ['crit' => [], 'ent' => null, 'opt' => []],
        'vars' => ['act' => null],
    ],
    'index' => [
        'opt' => ['crit' => [], 'ent' => null, 'opt' => []],
        'vars' => ['act' => null, 'params' => []],
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
