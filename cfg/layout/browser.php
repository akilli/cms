<?php
return [
    'toolbar' => [
        'active' => false,
    ],
    'index' => [
        'type' => 'index',
        'tpl' => 'index-browser.phtml',
        'parent_id' => 'content',
        'sort' => 300,
        'cfg' => [
            'attr_id' => ['name'],
            'limit' => 20,
            'pager' => 'bottom',
            'search' => ['name'],
        ],
    ],
];
