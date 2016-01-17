<?php
return [
    // entity
    'entity' => [
        'id' => 'entity',
        'name' => 'Entity',
        'callback' => 'block\entity',
    ],
    'pager' => [
        'id' => 'pager',
        'name' => 'Pager',
        'callback' => 'block\pager',
    ],
    // nestedset
    'menu' => [
        'id' => 'menu',
        'name' => 'Menu',
        'callback' => 'block\menu',
    ],
    // session
    'message' => [
        'id' => 'message',
        'name' => 'Message',
        'callback' => 'block\message',
    ],
    // toolbar
    'toolbar' => [
        'id' => 'toolbar',
        'name' => 'Toolbar',
        'callback' => 'block\toolbar',
    ],
    // view
    'template' => [
        'id' => 'template',
        'name' => 'Template',
        'callback' => 'block\template',
    ],
    'container' => [
        'id' => 'container',
        'name' => 'Container',
        'callback' => 'block\container',
    ],
];
