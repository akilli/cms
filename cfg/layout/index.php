<?php
return [
    'content' => [
        'type' => 'tpl',
        'tpl' => 'ent/index.phtml',
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
