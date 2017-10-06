<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    'account/login' => [
        'call' => 'cms\account_guest',
    ],
    'account/logout' => [
        'call' => 'cms\account_user',
    ],
    'account/password' => [
        'call' => 'cms\account_user',
    ],
    'media/view' => [
        'call' => 'cms\account_user',
    ],
    'page/index' => [
        'call' => 'cms\account_user',
    ],
    'page/view' => [
        'call' => 'cms\account_user',
    ],
];
