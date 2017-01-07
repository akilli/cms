<?php
return [
    [
        'name' => 'Homepage',
        'url' => '/',
    ],
    [
        'name' => 'Dashboard',
        'url' => '/account/dashboard',
    ],
    [
        'name' => 'Profile',
        'url' => '/account/profile',
    ],
    [
        'name' => 'Logout',
        'url' => '/account/logout',
    ],
    [
        'name' => 'Content',
        'url' => '',
        'children' => [
            [
                'name' => 'Page',
                'url' => '/page/admin',
            ],
        ],
    ],
    [
        'name' => 'Structure',
        'url' => '',
        'children' => [
            [
                'name' => 'Menu',
                'url' => '/menu/admin',
            ],
            [
                'name' => 'Node',
                'url' => '/node/admin',
            ],
            [
                'name' => 'Entity',
                'url' => '/entity/admin',
            ],
            [
                'name' => 'Attribute',
                'url' => '/attr/admin',
            ],
        ],
    ],
    [
        'name' => 'System',
        'url' => '',
        'children' => [
            [
                'name' => 'Project',
                'url' => '/project/admin',
            ],
            [
                'name' => 'Role',
                'url' => '/role/admin',
            ],
            [
                'name' => 'Account',
                'url' => '/account/admin',
            ],
            [
                'name' => 'URL',
                'url' => '/url/admin',
            ],
        ],
    ],
];
