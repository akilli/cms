<?php
return [
    'html' => [
        'html' => ['type' => 'html'],
        'head' => ['type' => 'container', 'parent_id' => 'html', 'sort' => 100, 'cfg' => ['tag' => 'head']],
        'body' => ['type' => 'container', 'parent_id' => 'html', 'sort' => 200, 'cfg' => ['tag' => 'body']],
        'meta' => ['type' => 'meta', 'parent_id' => 'head', 'sort' => 100],
        'icon' => ['type' => 'tpl', 'parent_id' => 'head', 'sort' => 200, 'cfg' => ['tpl' => 'icon.phtml']],
        'asset' => ['type' => 'tpl', 'parent_id' => 'head', 'sort' => 300, 'cfg' => ['tpl' => 'asset.phtml']],
        'toolbar' => ['type' => 'toolbar', 'privilege' => '_user_', 'parent_id' => 'body', 'sort' => 100],
        'main' => ['type' => 'container', 'parent_id' => 'body', 'sort' => 200, 'cfg' => ['tag' => 'main']],
        'content' => ['type' => 'container', 'parent_id' => 'main', 'sort' => 100, 'cfg' => ['id' => true, 'tag' => 'article']],
        'sidebar' => ['type' => 'container', 'parent_id' => 'main', 'sort' => 200, 'cfg' => ['id' => true, 'tag' => 'aside']],
        'title' => ['type' => 'title', 'parent_id' => 'content', 'sort' => 100],
        'msg' => ['type' => 'tag', 'parent_id' => 'content', 'sort' => 200, 'cfg' => ['tag' => 'app-msg']],
    ],
    'html:_admin_' => [
        'asset' => ['cfg' => ['tpl' => 'asset-admin.phtml']],
    ],
    'html:_invalid_' => [
        'title' => ['cfg' => ['text' => 'Error']],
        'view' => ['type' => 'tpl', 'parent_id' => 'content', 'sort' => 300, 'cfg' => ['tpl' => 'error.phtml']],
    ],
    'html:_public_' => [
        'header' => ['type' => 'tpl', 'parent_id' => 'body', 'sort' => 140, 'cfg' => ['tpl' => 'header.phtml']],
        'menu' => ['type' => 'menu', 'parent_id' => 'body', 'sort' => 160, 'cfg' => ['toggle' => true]],
        'breadcrumb' => ['type' => 'breadcrumb', 'parent_id' => 'main', 'sort' => 50],
        'footer' => ['type' => 'tpl', 'parent_id' => 'body', 'sort' => 300, 'cfg' => ['tpl' => 'footer.phtml']],
    ],
    'html:account:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'role_id', 'username', 'password', 'email']]],
    ],
    'html:account:index' => [
        'index' => ['cfg' => ['attr_id' => ['name', 'username', 'role_id'], 'filter' => ['role_id'], 'search' => ['name', 'username']]],
    ],
    'html:account:login' => [
        'toolbar' => ['active' => false],
        'title' => ['cfg' => ['text' => 'Login']],
        'login' => ['type' => 'login', 'parent_id' => 'content', 'sort' => 300],
    ],
    'html:account:profile' => [
        'title' => ['cfg' => ['text' => 'Profile']],
        'form' => ['type' => 'profile', 'parent_id' => 'content', 'sort' => 300, 'cfg' => ['attr_id' => ['password', 'email']]],
    ],
    'html:block:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'content']]],
    ],
    'html:block:index' => [
        'index' => ['cfg' => ['attr_id' => ['name']]],
    ],
    'html:contentpage:edit' => [
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
    'html:contentpage:index' => [
        'index' => ['cfg' => ['attr_id' => ['name', 'position', 'parent_id', 'menu', 'timestamp']]],
    ],
    'html:edit' => [
        'form' => ['type' => 'edit', 'parent_id' => 'content', 'sort' => 300],
    ],
    'html:file:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'url', 'thumb', 'info']]],
    ],
    'html:file:index' => [
        'index' => ['cfg' => ['attr_id' => ['url', 'name'], 'search' => ['name', 'url', 'info']]],
    ],
    'html:index' => [
        'new' => ['type' => 'tpl', 'parent_id' => 'content', 'sort' => 300, 'cfg' => ['tpl' => 'new.phtml']],
        'index' => [
            'type' => 'index',
            'parent_id' => 'content',
            'sort' => 400,
            'cfg' => ['pager' => 'bottom', 'search' => ['name'], 'sort' => true, 'tpl' => 'index-admin.phtml'],
        ],
    ],
    'html:layout:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort']]],
    ],
    'html:layout:index' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort'],
                'filter' => ['block_id', 'page_id', 'parent_id'],
            ],
        ],
    ],
    'html:page:index' => [
        'index' => ['cfg' => ['filter' => ['parent_id', 'account_id']]],
    ],
    'html:page:view' => [
        'view' => ['cfg' => ['attr_id' => ['content', 'aside']]],
    ],
    'html:role:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'privilege']]],
    ],
    'html:role:index' => [
        'index' => ['cfg' => ['attr_id' => ['name']]],
    ],
    'html:view' => [
        'view' => ['type' => 'view', 'parent_id' => 'content', 'sort' => 300],
    ],
];
