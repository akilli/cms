<?php
declare(strict_types=1);

return [
    'home' => [
        'name' => 'Homepage',
        'url' => '/',
        'sort' => 10,
    ],
    'page' => [
        'name' => 'Pages',
        'url' => '/page:index',
        'sort' => 20,
    ],
    'file' => [
        'name' => 'Files',
        'url' => '/file:index',
        'sort' => 30,
    ],
    'menu' => [
        'name' => 'Menu',
        'url' => '/menu:index',
        'sort' => 40,
    ],
    'user' => [
        'name' => 'User',
        'sort' => 50,
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
        'sort' => 60,
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
