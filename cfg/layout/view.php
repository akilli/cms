<?php
return [
    'rte' => [
        'section' => 'tpl',
        'tpl' => 'head/rte.phtml',
        'priv' => '*/edit',
        'parent_id' => 'head',
        'sort' => -1,
    ],
    'content' => [
        'section' => 'tpl',
        'tpl' => 'ent/view.phtml',
        'parent_id' => 'main',
    ],
];
