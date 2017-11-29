<?php
return [
    'top' => [
        'active' => false,
    ],
    'bottom' => [
        'active' => false,
    ],
    'rte' => [
        'section' => 'tpl',
        'tpl' => 'head/rte.phtml',
        'parent_id' => 'head',
        'sort' => -1,
    ],
    'content' => [
        'section' => 'tpl',
        'tpl' => 'ent/admin.phtml',
        'parent_id' => 'main',
        'vars' => ['mode' => 'browser'],
    ],
    'pager' => [
        'section' => 'pager',
        'tpl' => 'ent/pager.phtml',
    ],
    'search' => [
        'section' => 'tpl',
        'tpl' => 'ent/search.phtml',
        'parent_id' => 'sidebar',
    ],
];
