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
    'role' => [
        'name' => 'Roles',
        'url' => '/role:index',
        'sort' => 50,
    ],
    'account' => [
        'name' => 'Accounts',
        'url' => '/account:index',
        'sort' => 60,
    ],
    'profile' => [
        'name' => 'Profile',
        'url' => '/account:profile',
        'sort' => 70,
    ],
    'logout' => [
        'name' => 'Logout',
        'url' => '/account:logout',
        'sort' => 80,
    ],
];
