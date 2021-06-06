<?php
return [
    'html' => [
        'html' => ['type' => 'html'],
        'head' => ['type' => 'container', 'tag' => 'head', 'parent_id' => 'html', 'sort' => 100],
        'body' => ['type' => 'container', 'tag' => 'body', 'parent_id' => 'html', 'sort' => 200, 'image' => ['sizes' => '100vw']],
        'meta' => ['type' => 'meta', 'parent_id' => 'head', 'sort' => 100],
        'icon' => ['type' => 'tpl', 'tpl' => 'icon.phtml', 'parent_id' => 'head', 'sort' => 200],
        'asset' => ['type' => 'tpl', 'tpl' => 'asset.phtml', 'parent_id' => 'head', 'sort' => 300],
        'toolbar' => ['type' => 'toolbar', 'privilege' => '_user_', 'parent_id' => 'body', 'sort' => 100],
        'main' => ['type' => 'container', 'tag' => 'main', 'parent_id' => 'body', 'sort' => 200],
        'content' => ['type' => 'container', 'tag' => 'article', 'parent_id' => 'main', 'sort' => 100, 'cfg' => ['id' => true]],
        'sidebar' => ['type' => 'container', 'tag' => 'aside', 'parent_id' => 'main', 'sort' => 200, 'cfg' => ['id' => true]],
        'title' => ['type' => 'title', 'parent_id' => 'content', 'sort' => 100],
        'msg' => ['type' => 'tag', 'tag' => 'app-msg', 'parent_id' => 'content', 'sort' => 200],
    ],
    'html:_admin_' => [
        'asset' => ['tpl' => 'asset-admin.phtml'],
    ],
    'html:_invalid_' => [
        'title' => ['cfg' => ['text' => 'Error']],
        'view' => ['type' => 'tpl', 'tpl' => 'error.phtml', 'parent_id' => 'content', 'sort' => 300],
    ],
    'html:_public_' => [
        'header' => ['type' => 'tpl', 'tpl' => 'header.phtml', 'parent_id' => 'body', 'sort' => 140],
        'menu' => ['type' => 'menu', 'parent_id' => 'body', 'sort' => 160, 'cfg' => ['toggle' => true]],
        'breadcrumb' => ['type' => 'breadcrumb', 'parent_id' => 'main', 'sort' => 50],
        'footer' => ['type' => 'tpl', 'tpl' => 'footer.phtml', 'parent_id' => 'body', 'sort' => 300],
    ],
    'html:account:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'role_id', 'username', 'password', 'email']]],
    ],
    'html:account:index' => [
        'index' => [
            'cfg' => ['attr_id' => ['name', 'username', 'role_id', 'timestamp'], 'filter' => ['role_id'], 'search' => ['name', 'username']],
        ],
    ],
    'html:account:login' => [
        'toolbar' => ['active' => false],
        'title' => ['cfg' => ['text' => 'Login']],
        'login' => ['type' => 'login', 'parent_id' => 'content', 'sort' => 300],
    ],
    'html:account:profile' => [
        'title' => ['cfg' => ['text' => 'Profile']],
        'form' => ['type' => 'profile', 'parent_id' => 'content', 'sort' => 300, 'cfg' => ['attr_id' => ['username', 'password', 'email']]],
    ],
    'html:block:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'content']]],
    ],
    'html:block:index' => [
        'index' => ['cfg' => ['attr_id' => ['name', 'timestamp']]],
    ],
    'html:edit' => [
        'form' => ['type' => 'edit', 'parent_id' => 'content', 'sort' => 300],
    ],
    'html:file:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'url', 'thumb', 'info']]],
    ],
    'html:file:index' => [
        'index' => ['cfg' => ['attr_id' => ['url', 'name', 'timestamp'], 'search' => ['name', 'url', 'info']]],
    ],
    'html:index' => [
        'new' => ['type' => 'tpl', 'tpl' => 'new.phtml', 'parent_id' => 'content', 'sort' => 300],
        'index' => [
            'type' => 'index',
            'tpl' => 'index-admin.phtml',
            'parent_id' => 'content',
            'sort' => 400,
            'cfg' => ['pager' => 'bottom', 'search' => ['name'], 'sort' => true],
        ],
    ],
    'html:layout:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort']]],
    ],
    'html:layout:index' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'block_id', 'page_id', 'parent_id', 'sort', 'timestamp'],
                'filter' => ['block_id', 'page_id', 'parent_id'],
            ],
        ],
    ],
    'html:page:edit' => [
        'form' => [
            'cfg' => [
                'attr_id' => [
                    'name',
                    'slug',
                    'disabled',
                    'menu',
                    'parent_id',
                    'sort',
                    'breadcrumb',
                    'title',
                    'content',
                    'aside',
                    'meta_title',
                    'meta_description',
                ],
            ],
        ],
    ],
    'html:page:index' => [
        'index' => [
            'cfg' => [
                'attr_id' => ['name', 'position', 'parent_id', 'menu', 'timestamp'],
                'filter' => ['parent_id', 'account_id'],
            ],
        ],
    ],
    'html:page:view' => [
        'view' => ['cfg' => ['attr_id' => ['content', 'aside']]],
    ],
    'html:role:edit' => [
        'form' => ['cfg' => ['attr_id' => ['name', 'privilege']]],
    ],
    'html:role:index' => [
        'index' => ['cfg' => ['attr_id' => ['name', 'timestamp']]],
    ],
    'html:view' => [
        'view' => ['type' => 'view', 'parent_id' => 'content', 'sort' => 300],
    ],
];
