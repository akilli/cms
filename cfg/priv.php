<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    '_guest_' => [
        'auto' => true,
    ],
    '_user_' => [
        'auto' => true,
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
        'priv' => '_guest_',
    ],
    'account/logout' => [
        'priv' => '_user_',
    ],
    'account/password' => [
        'priv' => '_user_',
    ],
    'app/js' => [
        'active' => false,
    ],
    'page/view' => [
        'active' => false,
    ],
    'page_article/view' => [
        'active' => false,
    ],
    'page_content/view' => [
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
