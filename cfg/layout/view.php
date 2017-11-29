<?php
return [
    'rte' => [
        'type' => 'tpl',
        'tpl' => 'head/rte.phtml',
        'priv' => '*/edit',
        'parent_id' => 'head',
        'sort' => -1,
    ],
    'content' => [
        'type' => 'tpl',
        'tpl' => 'ent/view.phtml',
        'parent_id' => 'main',
    ],
];
