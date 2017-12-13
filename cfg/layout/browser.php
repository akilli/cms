<?php
return [
    'top' => [
        'active' => false,
    ],
    'bottom' => [
        'active' => false,
    ],
    'rte' => [
        'type' => 'tpl',
        'tpl' => 'head/rte.phtml',
        'parent_id' => 'head',
        'sort' => -1,
    ],
    'content' => [
        'type' => 'tpl',
        'tpl' => 'ent/admin.phtml',
        'parent_id' => 'main',
    ],
    'pager' => [
        'type' => 'pager',
        'tpl' => 'ent/pager.phtml',
    ],
    'search' => [
        'type' => 'tpl',
        'tpl' => 'ent/search.phtml',
        'parent_id' => 'sidebar',
    ],
];
