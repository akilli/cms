<?php
return [
    'page' => [
        'name' => 'Pages',
        'sort' => 10,
    ],
    'content' => [
        'name' => 'Content Pages',
        'url' => '/content/admin',
        'priv' => 'content/admin',
        'parent' => 'page',
        'sort' => 10,
    ],
    'article' => [
        'name' => 'Articles',
        'url' => '/article/admin',
        'priv' => 'article/admin',
        'parent' => 'page',
        'sort' => 20,
    ],
    'file' => [
        'name' => 'Files',
        'sort' => 20,
    ],
    'audio' => [
        'name' => 'Audios',
        'url' => '/audio/admin',
        'priv' => 'audio/admin',
        'parent' => 'file',
        'sort' => 10,
    ],
    'doc' => [
        'name' => 'Documents',
        'url' => '/doc/admin',
        'priv' => 'doc/admin',
        'parent' => 'file',
        'sort' => 20,
    ],
    'image' => [
        'name' => 'Images',
        'url' => '/image/admin',
        'priv' => 'image/admin',
        'parent' => 'file',
        'sort' => 30,
    ],
    'video' => [
        'name' => 'Videos',
        'url' => '/video/admin',
        'priv' => 'video/admin',
        'parent' => 'file',
        'sort' => 40,
    ],
    'system' => [
        'name' => 'System',
        'sort' => 30,
    ],
    'account' => [
        'name' => 'Accounts',
        'url' => '/account/admin',
        'priv' => 'account/admin',
        'parent' => 'system',
        'sort' => 10,
    ],
    'role' => [
        'name' => 'Roles',
        'url' => '/role/admin',
        'priv' => 'role/admin',
        'parent' => 'system',
        'sort' => 20,
    ],
    'home' => [
        'name' => 'Homepage',
        'url' => '/',
        'priv' => 'content/view',
        'sort' => 40,
    ],
    'password' => [
        'name' => 'Password',
        'url' => '/account/password',
        'priv' => 'account/password',
        'sort' => 50,
    ],
    'logout' => [
        'name' => 'Logout',
        'url' => '/account/logout',
        'priv' => 'account/logout',
        'sort' => 60,
    ],
];
