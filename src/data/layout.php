<?php
namespace qnd;

return [
    // layout-base
    [
        'id' => 'root',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'layout/root.phtml',
    ],
    [
        'id' => 'head',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'layout/head.phtml',
        'vars' => ['meta' => []],
    ],
    [
        'id' => 'top',
        'handle' => 'layout-base',
        'type' => 'container',
    ],
    [
        'id' => 'left',
        'handle' => 'layout-base',
        'type' => 'container',
        'vars' => ['tag' => 'aside'],
    ],
    [
        'id' => 'message',
        'handle' => 'layout-base',
        'type' => 'message',
        'template' => 'layout/message.phtml',
    ],
    [
        'id' => 'main',
        'handle' => 'layout-base',
        'type' => 'container',
    ],
    [
        'id' => 'right',
        'handle' => 'layout-base',
        'type' => 'container',
        'vars' => ['tag' => 'aside'],
    ],
    [
        'id' => 'bottom',
        'handle' => 'layout-base',
        'type' => 'container',
    ],
    // action-create
    [
        'id' => 'content',
        'handle' => 'action-create',
        'type' => 'template',
        'template' => 'entity/edit.phtml',
        'parent' => 'main',
    ],
    // action-edit
    [
        'id' => 'content',
        'handle' => 'action-edit',
        'type' => 'template',
        'template' => 'entity/edit.phtml',
        'parent' => 'main',
    ],
    // action-view
    [
        'id' => 'content',
        'handle' => 'action-view',
        'type' => 'template',
        'template' => 'entity/view.phtml',
        'parent' => 'main',
    ],
    // action-admin
    [
        'id' => 'content',
        'handle' => 'action-admin',
        'type' => 'template',
        'template' => 'entity/admin.phtml',
        'parent' => 'main',
    ],
    [
        'id' => 'pager',
        'handle' => 'action-admin',
        'type' => 'pager',
        'template' => 'entity/pager.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'search',
        'handle' => 'action-admin',
        'type' => 'template',
        'template' => 'entity/search.phtml',
        'parent' => 'content',
    ],
    // action-index
    [
        'id' => 'content',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'entity/index.phtml',
        'parent' => 'main',
    ],
    [
        'id' => 'pager',
        'handle' => 'action-index',
        'type' => 'pager',
        'template' => 'entity/pager.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'search',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'entity/search.phtml',
        'parent' => 'content',
    ],
    // account-registered
    [
        'id' => 'toolbar',
        'handle' => 'account-registered',
        'type' => 'template',
        'template' => 'account/toolbar.phtml',
        'parent' => 'top',
    ],
    // account.dashboard
    [
        'id' => 'content',
        'handle' => 'account.dashboard',
        'type' => 'template',
        'template' => 'account/dashboard.phtml',
        'parent' => 'main',
    ],
    // account.profile
    [
        'id' => 'content',
        'handle' => 'account.profile',
        'type' => 'template',
        'template' => 'account/profile.phtml',
        'parent' => 'main',
    ],
    // account.login
    [
        'id' => 'content',
        'handle' => 'account.login',
        'type' => 'template',
        'template' => 'account/login.phtml',
        'parent' => 'main',
    ],
];
