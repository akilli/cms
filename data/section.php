<?php
return [
    'default' => [
        'id' => null,
        'section' => null,
        'call' => null,
        'template' => null,
        'vars' => [],
        'active' => true,
        'privilege' => null,
        'parent' => 'root',
        'sort' => 0,
        'children' => [],
    ],
    'container' => [
        'call' => 'qnd\section_container',
    ],
    'message' => [
        'call' => 'qnd\section_message',
    ],
    'nav' => [
        'call' => 'qnd\section_nav',
    ],
    'pager' => [
        'call' => 'qnd\section_pager',
    ],
    'template' => [
        'call' => 'qnd\section_template',
    ],
];
