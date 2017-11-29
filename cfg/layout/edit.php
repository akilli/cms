<?php
return [
    'rte' => [
        'type' => 'tpl',
        'tpl' => 'head/rte.phtml',
        'parent_id' => 'head',
        'sort' => -1,
    ],
    'content' => [
        'type' => 'tpl',
        'tpl' => 'ent/edit.phtml',
        'parent_id' => 'main',
    ],
];
