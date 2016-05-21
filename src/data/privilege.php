<?php
return [
    'all' => [
        'name' => 'All Privileges',
        'active' => true,
        'class' => 'group',
    ],
    'http.index' => [
        'name' => 'Homepage',
        'active' => false,
        'sort' => 200,
    ],
    'project.switch' => [
        'name' => 'Project Switch',
        'active' => true,
        'sort' => 1000,
    ],
    'user.dashboard' => [
        'name' => 'User Dashboard',
        'callback' => 'qnd\registered',
        'active' => true,
        'sort' => 1000,
    ],
    'user.profile' => [
        'name' => 'User Profile',
        'callback' => 'qnd\registered',
        'active' => true,
        'sort' => 1000,
    ],
    'user.login' => [
        'name' => 'User Login',
        'active' => false,
        'sort' => 1000,
    ],
    'user.logout' => [
        'name' => 'User Logout',
        'callback' => 'qnd\registered',
        'active' => true,
        'sort' => 1000,
    ],
];
