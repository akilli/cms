<?php
declare(strict_types=1);

return [
    'page' => [
        'name' => 'Pages',
        'url' => '/page:index',
        'sort' => 10,
    ],
    'file' => [
        'name' => 'Files',
        'url' => '/file:index',
        'sort' => 20,
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
