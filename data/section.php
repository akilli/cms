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
        'call' => 'cms\section_container',
    ],
    'message' => [
        'call' => 'cms\section_message',
    ],
    'nav' => [
        'call' => 'cms\section_nav',
    ],
    'pager' => [
        'call' => 'cms\section_pager',
    ],
    'template' => [
        'call' => 'cms\section_template',
    ],
];
