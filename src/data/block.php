<?php
return [
    'container' => [
        'id' => 'container',
        'name' => 'Container',
        'callback' => 'akilli\block_container',
    ],
    'entity' => [
        'id' => 'entity',
        'name' => 'Entity',
        'callback' => 'akilli\block_entity',
    ],
    'menu' => [
        'id' => 'menu',
        'name' => 'Menu',
        'callback' => 'akilli\block_menu',
    ],
    'message' => [
        'id' => 'message',
        'name' => 'Message',
        'callback' => 'akilli\block_message',
    ],
    'pager' => [
        'id' => 'pager',
        'name' => 'Pager',
        'callback' => 'akilli\block_pager',
    ],
    'template' => [
        'id' => 'template',
        'name' => 'Template',
        'callback' => 'akilli\block_template',
    ],
    'toolbar' => [
        'id' => 'toolbar',
        'name' => 'Toolbar',
        'callback' => 'akilli\block_toolbar',
    ],
];
