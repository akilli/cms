<?php
return [
    'account.dashboard' => [
        'id' => 'account.dashboard',
        'name' => 'Account Dashboard',
        'callback' => 'akilli\registered',
        'is_active' => true,
        'sort_order' => 1000,
    ],
    'account.profile' => [
        'id' => 'account.profile',
        'name' => 'Account Profile',
        'callback' => 'akilli\registered',
        'is_active' => true,
        'sort_order' => 1000,
    ],
    'account.login' => [
        'id' => 'account.login',
        'name' => 'Account Login',
        'is_active' => false,
        'sort_order' => 1000,
    ],
    'account.logout' => [
        'id' => 'account.logout',
        'name' => 'Account Logout',
        'callback' => 'akilli\registered',
        'is_active' => true,
        'sort_order' => 1000,
    ],
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
];
