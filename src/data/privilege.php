<?php
return [
    'index.index' => [
        'active' => false,
        'sort' => 200,
    ],
    'project.import' => [
        'active' => true,
        'sort' => 1000,
    ],
    'project.switch' => [
        'active' => true,
        'sort' => 1000,
    ],
    'user.dashboard' => [
        'callback' => 'registered',
        'active' => true,
        'sort' => 1000,
    ],
    'user.profile' => [
        'callback' => 'registered',
        'active' => true,
        'sort' => 1000,
    ],
    'user.login' => [
        'active' => false,
        'sort' => 1000,
    ],
    'user.logout' => [
        'callback' => 'registered',
        'active' => true,
        'sort' => 1000,
    ],
];
