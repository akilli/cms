<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    'account-guest' => [
        'auto' => true,
    ],
    'account-user' => [
        'auto' => true,
    ],
    'account/admin' => [
        'priv' => '_all_',
    ],
    'account/create' => [
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
    'app/js' => [
        'priv' => 'account-user',
    ],
    'article/view' => [
        'active' => false,
    ],
    'content/view' => [
        'active' => false,
    ],
    'page/view' => [
        'active' => false,
    ],
    'role/admin' => [
        'priv' => '_all_',
    ],
    'role/create' => [
        'priv' => '_all_',
    ],
    'role/delete' => [
        'priv' => '_all_',
    ],
    'role/edit' => [
        'priv' => '_all_',
    ],
];
