<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    'account.dashboard' => [
        'name' => 'Account Dashboard',
        'callback' => 'registered',
    ],
    'account.login' => [
        'name' => 'Account Login',
        'callback' => 'unregistered',
    ],
    'account.logout' => [
        'name' => 'Account Logout',
        'callback' => 'registered',
    ],
    'account.password' => [
        'name' => 'Account Password',
        'callback' => 'registered',
    ],
    'project.export' => [
        'name' => 'Project Export',
    ],
    'project.import' => [
        'name' => 'Project Import',
    ],
    'project.switch' => [
        'name' => 'Project Switch',
    ],
];
