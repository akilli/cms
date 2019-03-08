<?php
return [
    'dashboard' => [
        'name' => 'Dashboard',
        'action' => 'account/dashboard',
        'sort' => 10,
    ],
    'page' => [
        'name' => 'Pages',
        'sort' => 20,
    ],
    'page_content' => [
        'name' => 'Content Pages',
        'action' => 'page_content/admin',
        'parent_id' => 'page',
        'sort' => 10,
    ],
    'page_article' => [
        'name' => 'Articles',
        'action' => 'page_article/admin',
        'parent_id' => 'page',
        'sort' => 20,
    ],
    'layout' => [
        'name' => 'Layout',
        'action' => 'layout/admin',
        'sort' => 30,
    ],
    'block' => [
        'name' => 'Blocks',
        'sort' => 40,
    ],
    'block_content' => [
        'name' => 'Content Blocks',
        'action' => 'block_content/admin',
        'parent_id' => 'block',
        'sort' => 10,
    ],
    'file' => [
        'name' => 'Files',
        'sort' => 50,
    ],
    'file_image' => [
        'name' => 'Images',
        'action' => 'file_image/admin',
        'parent_id' => 'file',
        'sort' => 10,
    ],
    'file_doc' => [
        'name' => 'Documents',
        'action' => 'file_doc/admin',
        'parent_id' => 'file',
        'sort' => 20,
    ],
    'file_video' => [
        'name' => 'Videos',
        'action' => 'file_video/admin',
        'parent_id' => 'file',
        'sort' => 30,
    ],
    'file_audio' => [
        'name' => 'Audios',
        'action' => 'file_audio/admin',
        'parent_id' => 'file',
        'sort' => 40,
    ],
    'role' => [
        'name' => 'Roles',
        'action' => 'role/admin',
        'sort' => 60,
    ],
    'account' => [
        'name' => 'Accounts',
        'action' => 'account/admin',
        'sort' => 70,
    ],
    'home' => [
        'name' => 'Homepage',
        'url' => '/',
        'sort' => 80,
    ],
    'profile' => [
        'name' => 'Profile',
        'action' => 'account/profile',
        'sort' => 90,
    ],
    'logout' => [
        'name' => 'Logout',
        'action' => 'account/logout',
        'sort' => 100,
    ],
];
