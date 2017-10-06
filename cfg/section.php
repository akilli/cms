<?php
return [
    'container' => [
        'call' => 'cms\section_container',
    ],
    'message' => [
        'call' => 'cms\section_message',
        'tpl' => 'layout/message.phtml',
    ],
    'nav' => [
        'call' => 'cms\section_nav',
    ],
    'pager' => [
        'call' => 'cms\section_pager',
        'tpl' => 'entity/pager.phtml',
    ],
    'tpl' => [
        'call' => 'cms\section_tpl',
    ],
];
