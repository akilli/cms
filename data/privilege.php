<?php
return [
    '_all_' => [
        'name' => 'ALL PRIVILEGES'
    ],
    'account/login' => [
        'call' => 'account_guest'
    ],
    'account/logout' => [
        'call' => 'account_user'
    ],
    'account/password' => [
        'call' => 'account_user'
    ],
    'media/view' => [
        'call' => 'account_user'
    ],
    'project/switch' => [
        'call' => 'account_global'
    ],
];
