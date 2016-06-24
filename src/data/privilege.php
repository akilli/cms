<?php
return [
    'all' => [
        'name' => 'All Privileges',
        'active' => true,
        'class' => 'group',
    ],
    'index.index' => [
        'name' => 'Homepage',
        'active' => false,
        'sort' => 200,
    ],
    'project.import' => [
        'name' => 'Project Import',
        'active' => true,
        'sort' => 1000,
    ],
    'project.export' => [
        'name' => 'Project Export',
        'active' => true,
        'sort' => 1000,
    ],
    'project.switch' => [
        'name' => 'Project Switch',
        'active' => true,
        'sort' => 1000,
    ],
    'user.dashboard' => [
        'name' => 'User Dashboard',
        'callback' => 'registered',
        'active' => true,
        'sort' => 1000,
    ],
    'user.profile' => [
        'name' => 'User Profile',
        'callback' => 'registered',
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
        'callback' => 'registered',
        'active' => true,
        'sort' => 1000,
    ],
];
