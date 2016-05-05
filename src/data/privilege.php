<?php
return [
    'all' => [
        'id' => 'all',
        'name' => 'All Privileges',
        'active' => true,
        'class' => 'group',
    ],
    'http.index' => [
        'id' => 'http.index',
        'name' => 'Homepage',
        'active' => false,
        'sort_order' => 200,
    ],
    'project.switch' => [
        'id' => 'project.switch',
        'name' => 'Project Switch',
        'active' => true,
        'sort_order' => 1000,
    ],
    'user.dashboard' => [
        'id' => 'user.dashboard',
        'name' => 'User Dashboard',
        'callback' => 'qnd\registered',
        'active' => true,
        'sort_order' => 1000,
    ],
    'user.profile' => [
        'id' => 'user.profile',
        'name' => 'User Profile',
        'callback' => 'qnd\registered',
        'active' => true,
        'sort_order' => 1000,
    ],
    'user.login' => [
        'id' => 'user.login',
        'name' => 'User Login',
        'active' => false,
        'sort_order' => 1000,
    ],
    'user.logout' => [
        'id' => 'user.logout',
        'name' => 'User Logout',
        'callback' => 'qnd\registered',
        'active' => true,
        'sort_order' => 1000,
    ],
];
