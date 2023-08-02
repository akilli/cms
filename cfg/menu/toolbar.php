<?php
declare(strict_types=1);

return [
    'page' => [
        'name' => 'Pages',
        'sort' => 10,
    ],
    'contentpage' => [
        'name' => 'Content Pages',
        'url' => '/contentpage:index',
        'parent_id' => 'page',
        'sort' => 10,
    ],
    'file' => [
        'name' => 'Files',
        'sort' => 20,
    ],
    'image' => [
        'name' => 'Images',
        'url' => '/image:index',
        'parent_id' => 'file',
        'sort' => 10,
    ],
    'video' => [
        'name' => 'Videos',
        'url' => '/video:index',
        'parent_id' => 'file',
        'sort' => 20,
    ],
    'audio' => [
        'name' => 'Audios',
        'url' => '/audio:index',
        'parent_id' => 'file',
        'sort' => 30,
    ],
    'iframe' => [
        'name' => 'Iframes',
        'url' => '/iframe:index',
        'parent_id' => 'file',
        'sort' => 40,
    ],
    'document' => [
        'name' => 'Documents',
        'url' => '/document:index',
        'parent_id' => 'file',
        'sort' => 50,
    ],
    'menu' => [
        'name' => 'Menu',
        'url' => '/menu:index',
        'sort' => 30,
    ],
    'user' => [
        'name' => 'User',
        'sort' => 40,
    ],
    'role' => [
        'name' => 'Roles',
        'url' => '/role:index',
        'parent_id' => 'user',
        'sort' => 10,
    ],
    'account' => [
        'name' => 'Accounts',
        'url' => '/account:index',
        'parent_id' => 'user',
        'sort' => 20,
    ],
    'my' => [
        'name' => 'My Account',
        'sort' => 50,
    ],
    'profile' => [
        'name' => 'Profile',
        'url' => '/account:profile',
        'parent_id' => 'my',
        'sort' => 10,
    ],
    'logout' => [
        'name' => 'Logout',
        'url' => '/account:logout',
        'parent_id' => 'my',
        'sort' => 20,
    ],
];
