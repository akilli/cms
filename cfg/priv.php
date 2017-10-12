<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    'app/js' => [
        'call' => 'account\user',
    ],
    'account/login' => [
        'call' => 'account\guest',
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
