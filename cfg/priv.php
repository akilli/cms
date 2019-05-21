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
    'api/cfg' => [
        'active' => false,
    ],
    'block/api' => [
        'priv' => 'block/admin',
    ],
    'block/browser' => [
        'priv' => 'block/admin',
    ],
    'file_audio/browser' => [
        'priv' => 'file/browser',
    ],
    'file_doc/browser' => [
        'priv' => 'file/browser',
    ],
    'file_iframe/browser' => [
        'priv' => 'file/browser',
    ],
    'file_image/browser' => [
        'priv' => 'file/browser',
    ],
    'file_media/browser' => [
        'priv' => 'file/browser',
    ],
    'file_video/browser' => [
        'priv' => 'file/browser',
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
