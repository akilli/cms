<?php
return [
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
    'create' => [
        'type' => 'tpl',
        'tpl' => 'ent/create.phtml',
        'priv' => '*/edit',
        'parent_id' => 'sidebar',
    ],
];
