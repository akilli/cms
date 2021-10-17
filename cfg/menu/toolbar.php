<?php
declare(strict_types=1);

return [
    'home' => [
        'name' => 'Homepage',
        'url' => '/',
        'sort' => 100,
    ],
    'page' => [
        'name' => 'Pages',
        'sort' => 200,
    ],
    'contentpage' => [
        'name' => 'Content Pages',
        'url' => '/contentpage:index',
        'parent_id' => 'page',
        'sort' => 100,
    ],
    'layout' => [
        'name' => 'Layout',
        'url' => '/layout:index',
        'sort' => 300,
    ],
    'block' => [
        'name' => 'Blocks',
        'sort' => 400,
    ],
    'contentblock' => [
        'name' => 'Content Blocks',
        'url' => '/contentblock:index',
        'parent_id' => 'block',
        'sort' => 100,
    ],
    'file' => [
        'name' => 'Files',
        'sort' => 500,
    ],
    'image' => [
        'name' => 'Images',
        'url' => '/image:index',
        'parent_id' => 'file',
        'sort' => 100,
    ],
    'video' => [
        'name' => 'Videos',
        'url' => '/video:index',
        'parent_id' => 'file',
        'sort' => 200,
    ],
    'audio' => [
        'name' => 'Audios',
        'url' => '/audio:index',
        'parent_id' => 'file',
        'sort' => 300,
    ],
    'iframe' => [
        'name' => 'Iframes',
        'url' => '/iframe:index',
        'parent_id' => 'file',
        'sort' => 400,
    ],
    'document' => [
        'name' => 'Documents',
        'url' => '/document:index',
        'parent_id' => 'file',
        'sort' => 500,
    ],
    'menu' => [
        'name' => 'Menus',
        'url' => '/menu:index',
        'sort' => 600,
    ],
    'user' => [
        'name' => 'User',
        'sort' => 700,
    ],
    'role' => [
        'name' => 'Roles',
        'url' => '/role:index',
        'parent_id' => 'user',
        'sort' => 100,
    ],
    'account' => [
        'name' => 'Accounts',
        'url' => '/account:index',
        'parent_id' => 'user',
        'sort' => 200,
    ],
    'my' => [
        'name' => 'My Account',
        'sort' => 800,
    ],
    'profile' => [
        'name' => 'Profile',
        'url' => '/account:profile',
        'parent_id' => 'my',
        'sort' => 100,
    ],
    'logout' => [
        'name' => 'Logout',
        'url' => '/account:logout',
        'parent_id' => 'my',
        'sort' => 200,
    ],
];
