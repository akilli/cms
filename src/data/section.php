<?php
return [
    'container' => [
        'id' => 'container',
        'name' => 'Container',
        'callback' => 'qnd\section_container',
    ],
    'entity' => [
        'id' => 'entity',
        'name' => 'Entity',
        'callback' => 'qnd\section_entity',
    ],
    'menu' => [
        'id' => 'menu',
        'name' => 'Menu',
        'callback' => 'qnd\section_menu',
    ],
    'message' => [
        'id' => 'message',
        'name' => 'Message',
        'callback' => 'qnd\section_message',
    ],
    'pager' => [
        'id' => 'pager',
        'name' => 'Pager',
        'callback' => 'qnd\section_pager',
    ],
    'template' => [
        'id' => 'template',
        'name' => 'Template',
        'callback' => 'qnd\section_template',
    ],
    'toolbar' => [
        'id' => 'toolbar',
        'name' => 'Toolbar',
        'callback' => 'qnd\section_toolbar',
    ],
];
