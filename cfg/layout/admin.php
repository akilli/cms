<?php
return [
    'content' => [
        'section' => 'tpl',
        'tpl' => 'ent/admin.phtml',
        'parent_id' => 'main',
    ],
    'pager' => [
        'section' => 'pager',
        'tpl' => 'ent/pager.phtml',
    ],
    'search' => [
        'section' => 'tpl',
        'tpl' => 'ent/search.phtml',
        'parent_id' => 'right',
    ],
    'create' => [
        'section' => 'tpl',
        'tpl' => 'ent/create.phtml',
        'priv' => '*/edit',
        'parent_id' => 'right',
    ],
];
