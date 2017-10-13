<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
        'sort' => -1,
    ],
    'app/denied' => [
        'active' => false,
    ],
    'app/error' => [
        'active' => false,
    ],
    'app/js' => [
        'call' => 'account\user',
    ],
    'account/login' => [
        'active' => false,
    ],
    'account/logout' => [
        'call' => 'account\user',
    ],
    'account/password' => [
        'call' => 'account\user',
    ],
    'media/view' => [
        'call' => 'account\user',
    ],
    'page/index' => [
        'call' => 'account\user',
    ],
    'page/view' => [
        'call' => 'account\user',
    ],
];
