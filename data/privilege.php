<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES'
    ],
    'account/login' => [
        'call' => 'qnd\account_guest'
    ],
    'account/logout' => [
        'call' => 'qnd\account_user'
    ],
    'account/password' => [
        'call' => 'qnd\account_user'
    ],
    'media/view' => [
        'call' => 'qnd\account_user'
    ],
    'project/home' => [
        'call' => 'qnd\account_user'
    ],
    'project/switch' => [
        'call' => 'qnd\account_global'
    ],
];
