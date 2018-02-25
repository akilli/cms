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
    'asset' => [
        'name' => 'Assets',
        'sort' => 20,
    ],
    'file' => [
        'name' => 'Files',
        'url' => '/file/admin',
        'priv' => 'file/admin',
        'parent' => 'asset',
        'sort' => 10,
    ],
    'system' => [
        'name' => 'System',
        'sort' => 30,
    ],
    'url' => [
        'name' => 'URL',
        'url' => '/url/admin',
        'priv' => 'url/admin',
        'parent' => 'system',
        'sort' => 10,
    ],
    'account' => [
        'name' => 'Accounts',
        'url' => '/account/admin',
        'priv' => 'account/admin',
        'parent' => 'system',
        'sort' => 20,
    ],
    'role' => [
        'name' => 'Roles',
        'url' => '/role/admin',
        'priv' => 'role/admin',
        'parent' => 'system',
        'sort' => 30,
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
