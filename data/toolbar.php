<?php
return [
    'content' => [
        'name' => 'Content',
        'url' => '',
        'children' => [
            ['name' => 'Media', 'url' => '/media/admin'],
        ],
    ],
    'structure' => [
        'name' => 'Structure',
        'url' => '',
        'children' => [
            ['name' => 'Menu', 'url' => '/menu/admin'],
            ['name' => 'Node', 'url' => '/node/admin'],
            ['name' => 'Entity', 'url' => '/entity/admin'],
            ['name' => 'Attribute', 'url' => '/attr/admin'],
        ],
    ],
    'system' => [
        'name' => 'System',
        'url' => '',
        'children' => [
            ['name' => 'Project', 'url' => '/project/admin'],
            ['name' => 'Role', 'url' => '/role/admin'],
            ['name' => 'Account', 'url' => '/account/admin'],
            ['name' => 'URL', 'url' => '/url/admin'],
        ],
    ],
    'home' => ['name' => 'Homepage', 'url' => '/'],
    'dashboard' => ['name' => 'Dashboard', 'url' => '/account/dashboard'],
    'profile' => ['name' => 'Profile', 'url' => '/account/profile'],
    'logout' => ['name' => 'Logout', 'url' => '/account/logout'],
];
