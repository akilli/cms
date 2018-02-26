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
    'account/admin' => [
        'priv' => '_all_',
    ],
    'account/delete' => [
        'priv' => '_all_',
    ],
    'account/edit' => [
        'priv' => '_all_',
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
    'app/js' => [
        'priv' => 'account-user',
    ],
    'article/index' => [
        'active' => false,
    ],
    'article/view' => [
        'active' => false,
    ],
    'content/index' => [
        'active' => false,
    ],
    'content/view' => [
        'active' => false,
    ],
    'role/admin' => [
        'priv' => '_all_',
    ],
    'role/delete' => [
        'priv' => '_all_',
    ],
    'role/edit' => [
        'priv' => '_all_',
    ],
];
