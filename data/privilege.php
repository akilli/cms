<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES'
    ],
    'account/login' => [
        'callback' => 'account_guest'
    ],
    'account/logout' => [
        'callback' => 'account_user'
    ],
    'account/password' => [
        'callback' => 'account_user'
    ],
    'media/view' => [
        'callback' => 'account_user'
    ],
    'project/switch' => [
        'callback' => 'account_global'
    ],
];
