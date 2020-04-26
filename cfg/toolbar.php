<?php
return [
    'home' => [
        'name' => 'Homepage',
        'url' => '/',
        'sort' => 100,
    ],
    'page' => [
        'name' => 'Pages',
        'sort' => 200,
    ],
    'page_content' => [
        'name' => 'Content Pages',
        'action' => 'page_content/admin',
        'parent_id' => 'page',
        'sort' => 100,
    ],
    'page_article' => [
        'name' => 'Articles',
        'action' => 'page_article/admin',
        'parent_id' => 'page',
        'sort' => 200,
    ],
    'layout' => [
        'name' => 'Layout',
        'action' => 'layout/admin',
        'sort' => 300,
    ],
    'block' => [
        'name' => 'Blocks',
        'sort' => 400,
    ],
    'block_content' => [
        'name' => 'Content Blocks',
        'action' => 'block_content/admin',
        'parent_id' => 'block',
        'sort' => 100,
    ],
    'file' => [
        'name' => 'Files',
        'sort' => 500,
    ],
    'file_image' => [
        'name' => 'Images',
        'action' => 'file_image/admin',
        'parent_id' => 'file',
        'sort' => 100,
    ],
    'file_doc' => [
        'name' => 'Documents',
        'action' => 'file_doc/admin',
        'parent_id' => 'file',
        'sort' => 200,
    ],
    'file_video' => [
        'name' => 'Videos',
        'action' => 'file_video/admin',
        'parent_id' => 'file',
        'sort' => 300,
    ],
    'file_audio' => [
        'name' => 'Audios',
        'action' => 'file_audio/admin',
        'parent_id' => 'file',
        'sort' => 400,
    ],
    'file_iframe' => [
        'name' => 'Iframes',
        'action' => 'file_iframe/admin',
        'parent_id' => 'file',
        'sort' => 500,
    ],
    'role' => [
        'name' => 'Roles',
        'action' => 'role/admin',
        'sort' => 600,
    ],
    'account' => [
        'name' => 'Accounts',
        'action' => 'account/admin',
        'sort' => 700,
    ],
    'profile' => [
        'name' => 'Profile',
        'action' => 'account/profile',
        'sort' => 800,
    ],
    'logout' => [
        'name' => 'Logout',
        'action' => 'account/logout',
        'sort' => 900,
    ],
];
