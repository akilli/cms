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
    'account/login' => [
        'priv' => 'account-guest',
    ],
    'account/logout' => [
        'priv' => 'account-user',
    ],
    'account/password' => [
        'priv' => 'account-user',
    ],
    'app/asset' => [
        'active' => false,
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
