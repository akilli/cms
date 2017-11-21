<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
        'sort' => -1,
    ],
    'account-guest' => [
        'assignable' => false,
    ],
    'account-user' => [
        'assignable' => false,
    ],
    'app/denied' => [
        'active' => false,
    ],
    'app/error' => [
        'active' => false,
    ],
    'app/js' => [
        'priv' => 'account-user',
    ],
    'account/login' => [
        'priv' => 'account-guest',
    ],
    'account/logout' => [
        'priv' => 'account-user',
    ],
    'account/password' => [
        'priv' => 'account-user',
    ],
    'media/asset' => [
        'active' => false,
    ],
    'media/browser' => [
        'priv' => 'media/admin',
    ],
    'page/index' => [
        'active' => false,
    ],
    'page/view' => [
        'active' => false,
    ],
];
