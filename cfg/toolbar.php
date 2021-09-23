<?php
return [
    'home' => ['name' => 'Homepage', 'url' => '/', 'sort' => 100],
    'page' => ['sort' => 200],
    'layout' => ['sort' => 300],
    'block' => ['sort' => 400],
    'file' => ['sort' => 500],
    'menu' => ['sort' => 600],
    'url' => ['sort' => 700],
    'user' => ['name' => 'User', 'sort' => 800],
    'role' => ['parent_id' => 'user', 'sort' => 100],
    'account' => ['parent_id' => 'user', 'sort' => 200],
    'my' => ['name' => 'My Account', 'sort' => 900],
    'profile' => [
        'name' => 'Profile',
        'privilege' => 'account:profile',
        'url' => '/account/profile',
        'parent_id' => 'my',
        'sort' => 100,
    ],
    'logout' => [
        'name' => 'Logout',
        'privilege' => 'account:logout',
        'url' => '/account/logout',
        'parent_id' => 'my',
        'sort' => 200,
    ],
];
