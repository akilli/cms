<?php
return [
    '_admin_' => [
        'asset' => ['tpl' => 'asset-admin.phtml'],
    ],
    '_all_' => [
        'html' => ['type' => 'html'],
        'head' => ['type' => 'container', 'parent_id' => 'html', 'sort' => 100, 'cfg' => ['tag' => 'head']],
        'body' => [
            'type' => 'container',
            'parent_id' => 'html',
            'sort' => 200,
            'image' => ['sizes' => '100vw'],
            'cfg' => ['tag' => 'body'],
        ],
        'meta' => ['type' => 'meta', 'parent_id' => 'head', 'sort' => 100],
        'icon' => ['type' => 'tpl', 'tpl' => 'icon.phtml', 'parent_id' => 'head', 'sort' => 200],
        'asset' => ['type' => 'tpl', 'tpl' => 'asset.phtml', 'parent_id' => 'head', 'sort' => 300],
        'toolbar' => ['type' => 'toolbar', 'privilege' => '_user_', 'parent_id' => 'body', 'sort' => 100],
        'main' => ['type' => 'container', 'parent_id' => 'body', 'sort' => 200, 'cfg' => ['tag' => 'main']],
        'content' => ['type' => 'container', 'parent_id' => 'main', 'sort' => 100, 'cfg' => ['id' => true, 'tag' => 'article']],
        'sidebar' => ['type' => 'container', 'parent_id' => 'main', 'sort' => 200, 'cfg' => ['id' => true, 'tag' => 'aside']],
        'title' => ['type' => 'title', 'parent_id' => 'content', 'sort' => 100],
        'msg' => ['type' => 'tag', 'parent_id' => 'content', 'sort' => 200, 'cfg' => ['tag' => 'app-msg']],
    ],
    '_invalid_' => [
        'title' => ['cfg' => ['text' => 'Error']],
        'view' => ['type' => 'tpl', 'tpl' => 'error.phtml', 'parent_id' => 'content', 'sort' => 300],
    ],
    '_public_' => [
        'header' => ['type' => 'tpl', 'tpl' => 'header.phtml', 'parent_id' => 'body', 'sort' => 140],
        'menu' => ['type' => 'menu', 'parent_id' => 'body', 'sort' => 160, 'cfg' => ['toggle' => true]],
        'breadcrumb' => ['type' => 'breadcrumb', 'parent_id' => 'main', 'sort' => 50],
        'footer' => ['type' => 'tpl', 'tpl' => 'footer.phtml', 'parent_id' => 'body', 'sort' => 300],
    ],
    'account:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'role_id', 'username', 'password', 'email']]],
    ],
    'account:index' => [
        'index' => ['cfg' => ['attr_id' => ['name', 'username', 'role_id'], 'filter' => ['role_id'], 'search' => ['name', 'username']]],
    ],
    'account:login' => [
        'toolbar' => ['active' => false],
        'title' => ['cfg' => ['text' => 'Login']],
        'login' => ['type' => 'login', 'parent_id' => 'content', 'sort' => 300],
    ],
    'account:profile' => [
        'title' => ['cfg' => ['text' => 'Profile']],
        'form' => ['type' => 'profile', 'parent_id' => 'content', 'sort' => 300, 'cfg' => ['attr_id' => ['password', 'email']]],
    ],
    'block:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'content']]],
    ],
    'block:index' => [
        'index' => ['cfg' => ['attr_id' => ['name']]],
    ],
    'contentpage:edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => [
                    'name',
                    'slug',
                    'disabled',
                    'menu',
                    'parent_id',
                    'sort',
                    'title',
                    'content',
                    'aside',
                    'meta_title',
                    'meta_description',
                ],
            ],
        ],
    ],
    'contentpage:index' => [
        'index' => ['cfg' => ['attr_id' => ['name', 'position', 'parent_id', 'menu', 'timestamp']]],
    ],
    'edit' => [
        'form' => ['type' => 'edit', 'parent_id' => 'content', 'sort' => 300],
    ],
    'file:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'url', 'thumb', 'info']]],
    ],
    'file:index' => [
        'index' => ['cfg' => ['attr_id' => ['url', 'name'], 'search' => ['name', 'url', 'info']]],
    ],
    'index' => [
        'new' => ['type' => 'tpl', 'tpl' => 'new.phtml', 'parent_id' => 'content', 'sort' => 300],
        'index' => [
            'type' => 'index',
            'tpl' => 'index-admin.phtml',
            'parent_id' => 'content',
            'sort' => 400,
            'cfg' => ['pager' => 'bottom', 'search' => ['name'], 'sort' => true],
        ],
    ],
    'layout:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort']]],
    ],
    'layout:index' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
                'filter' => ['block_id', 'page_id', 'parent_id'],
            ],
        ],
    ],
    'page:index' => [
        'index' => ['cfg' => ['filter' => ['parent_id', 'account_id']]],
    ],
    'page:view' => [
        'view' => ['cfg' => ['attr_id' => ['content', 'aside']]],
    ],
    'page:view:1' => [
        'breadcrumb' => ['active' => false],
        'title' => ['active' => false],
    ],
    'role:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'privilege']]],
    ],
    'role:index' => [
        'index' => ['cfg' => ['attr_id' => ['name']]],
    ],
    'view' => [
        'view' => ['type' => 'view', 'parent_id' => 'content', 'sort' => 300],
    ],
];
