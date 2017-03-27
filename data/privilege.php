<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES',
    ],
    'account.dashboard' => [
        'callback' => 'registered',
    ],
    'account.login' => [
        'callback' => 'unregistered',
    ],
    'account.logout' => [
        'callback' => 'registered',
    ],
    'account.password' => [
        'callback' => 'registered',
    ],
    'project.switch' => [
        'callback' => 'account_global',
    ],
];
