<?php
return [
    // entity
    'entity' => [
        'id' => 'entity',
        'name' => 'Entity',
        'callback' => 'akilli\block_entity',
    ],
    'pager' => [
        'id' => 'pager',
        'name' => 'Pager',
        'callback' => 'akilli\block_pager',
    ],
    // nestedset
    'menu' => [
        'id' => 'menu',
        'name' => 'Menu',
        'callback' => 'akilli\block_menu',
    ],
    // session
    'message' => [
        'id' => 'message',
        'name' => 'Message',
        'callback' => 'akilli\block_message',
    ],
    // toolbar
    'toolbar' => [
        'id' => 'toolbar',
        'name' => 'Toolbar',
        'callback' => 'akilli\block_toolbar',
    ],
    // view
    'template' => [
        'id' => 'template',
        'name' => 'Template',
        'callback' => 'akilli\block_template',
    ],
    'container' => [
        'id' => 'container',
        'name' => 'Container',
        'callback' => 'akilli\block_container',
    ],
];
