<?php
return [
    'container' => [
        'call' => 'cms\section_container',
    ],
    'msg' => [
        'call' => 'cms\section_msg',
        'tpl' => 'layout/msg.phtml',
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
