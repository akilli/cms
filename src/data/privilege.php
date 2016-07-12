<?php
return [
    'project.import' => [
        'name' => 'Project Import',
        'active' => true,
    ],
    'project.switch' => [
        'name' => 'Project Switch',
        'active' => true,
    ],
    'user.dashboard' => [
        'name' => 'User Dashboard',
        'callback' => 'registered',
        'active' => true,
    ],
    'user.profile' => [
        'name' => 'User Profile',
        'callback' => 'registered',
        'active' => true,
    ],
    'user.login' => [
        'name' => 'User Login',
        'active' => false,
    ],
    'user.logout' => [
        'name' => 'User Logout',
        'callback' => 'registered',
        'active' => true,
    ],
];
