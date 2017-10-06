<?php
return [
    'container' => [
        'call' => 'cms\section_container',
    ],
    'message' => [
        'call' => 'cms\section_message',
        'template' => 'layout/message.phtml',
    ],
    'nav' => [
        'call' => 'cms\section_nav',
    ],
    'pager' => [
        'call' => 'cms\section_pager',
        'template' => 'entity/pager.phtml',
    ],
    'template' => [
        'call' => 'cms\section_template',
    ],
];
