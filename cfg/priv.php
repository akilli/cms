<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    'account-guest' => [
        'assignable' => false,
    ],
    'account-user' => [
        'assignable' => false,
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
    'app/error' => [
        'active' => false,
    ],
    'app/home' => [
        'active' => false,
    ],
    'app/js' => [
        'priv' => 'account-user',
    ],
    'file/asset' => [
        'active' => false,
    ],
    'file/browser' => [
        'priv' => 'file/admin',
    ],
    'page/index' => [
        'active' => false,
    ],
    'page/view' => [
        'active' => false,
    ],
];
