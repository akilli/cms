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
    // action-list
    [
        'id' => 'content',
        'handle' => 'action-list',
        'type' => 'template',
        'template' => 'entity/list.phtml',
        'parent' => 'main',
    ],
    [
        'id' => 'pager',
        'handle' => 'action-list',
        'type' => 'pager',
        'template' => 'entity/pager.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'search',
        'handle' => 'action-list',
        'type' => 'template',
        'template' => 'entity/search.phtml',
        'parent' => 'content',
    ],
    // user-registered
    [
        'id' => 'toolbar',
        'handle' => 'user-registered',
        'type' => 'template',
        'template' => 'user/toolbar.phtml',
        'parent' => 'top',
    ],
    [
        'id' => 'toolbar.nav',
        'handle' => 'user-registered',
        'type' => 'node',
        'parent' => 'toolbar',
        'vars' => ['crit' => ['uid' => 'toolbar', 'project_id' => PROJECT]],
    ],
    // user.dashboard
    [
        'id' => 'content',
        'handle' => 'user.dashboard',
        'type' => 'template',
        'template' => 'user/dashboard.phtml',
        'parent' => 'main',
    ],
    // user.profile
    [
        'id' => 'content',
        'handle' => 'user.profile',
        'type' => 'template',
        'template' => 'user/profile.phtml',
        'parent' => 'main',
    ],
    // user-login
    [
        'id' => 'content',
        'handle' => 'user.login',
        'type' => 'template',
        'template' => 'user/login.phtml',
        'parent' => 'main',
    ],
];
