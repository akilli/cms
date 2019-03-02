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
    'account/dashboard' => [
        'priv' => '_user_',
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
    'account/profile' => [
        'priv' => '_user_',
    ],
    'app/cfg' => [
        'active' => false,
    ],
    'file_audio/browser' => [
        'priv' => 'file_audio/admin',
    ],
    'file_doc/browser' => [
        'priv' => 'file_doc/admin',
    ],
    'file_image/browser' => [
        'priv' => 'file_image/admin',
    ],
    'file_video/browser' => [
        'priv' => 'file_video/admin',
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
