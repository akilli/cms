<?php
return [
    'all' => [
        'id' => 'all',
        'name' => 'All Privileges',
        'is_active' => true,
        'class' => 'group',
    ],
    'http.index' => [
        'id' => 'http.index',
        'name' => 'Homepage',
        'is_active' => false,
        'sort_order' => 200,
    ],
    'project.switch' => [
        'id' => 'project.switch',
        'name' => 'Project Switch',
        'is_active' => true,
        'sort_order' => 1000,
    ],
    'user.dashboard' => [
        'id' => 'user.dashboard',
        'name' => 'User Dashboard',
        'callback' => 'qnd\registered',
        'is_active' => true,
        'sort_order' => 1000,
    ],
    'user.profile' => [
        'id' => 'user.profile',
        'name' => 'User Profile',
        'callback' => 'qnd\registered',
        'is_active' => true,
        'sort_order' => 1000,
    ],
    'user.login' => [
        'id' => 'user.login',
        'name' => 'User Login',
        'is_active' => false,
        'sort_order' => 1000,
    ],
    'user.logout' => [
        'id' => 'user.logout',
        'name' => 'User Logout',
        'callback' => 'qnd\registered',
        'is_active' => true,
        'sort_order' => 1000,
    ],
];
