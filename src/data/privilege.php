<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
        'active' => true,
    ],
    'account.dashboard' => [
        'name' => 'Account Dashboard',
        'callback' => 'registered',
        'active' => true,
    ],
    'account.profile' => [
        'name' => 'Account Profile',
        'callback' => 'registered',
        'active' => true,
    ],
    'account.login' => [
        'name' => 'Account Login',
        'active' => false,
    ],
    'account.logout' => [
        'name' => 'Account Logout',
        'callback' => 'registered',
        'active' => true,
    ],
    'project.import' => [
        'name' => 'Project Import',
        'active' => true,
    ],
    'project.switch' => [
        'name' => 'Project Switch',
        'active' => true,
    ],
];
