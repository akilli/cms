<?php
return [
    // layout-base
    [
        'id' => 'root',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'root.phtml',
    ],
    [
        'id' => 'head',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'head.phtml',
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
        'template' => 'message.phtml',
    ],
    [
        'id' => 'content',
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
        'id' => 'entity',
        'handle' => 'action-create',
        'type' => 'template',
        'template' => 'entity.edit.phtml',
        'parent' => 'content',
    ],
    // action-edit
    [
        'id' => 'entity',
        'handle' => 'action-edit',
        'type' => 'template',
        'template' => 'entity.edit.phtml',
        'parent' => 'content',
    ],
    // action-view
    [
        'id' => 'entity',
        'handle' => 'action-view',
        'type' => 'template',
        'template' => 'entity.view.phtml',
        'parent' => 'content',
    ],
    // action-index
    [
        'id' => 'entity',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'entity.index.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'create',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'entity.create.phtml',
        'privilege' => 'create',
        'parent' => 'entity',
    ],
    [
        'id' => 'pager',
        'handle' => 'action-index',
        'type' => 'pager',
        'template' => 'pager.phtml',
        'parent' => 'entity',
    ],
    [
        'id' => 'search',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'search.phtml',
        'parent' => 'entity',
    ],
    // action-list
    [
        'id' => 'entity',
        'handle' => 'action-list',
        'type' => 'template',
        'template' => 'entity.list.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'pager',
        'handle' => 'action-list',
        'type' => 'pager',
        'template' => 'pager.phtml',
        'parent' => 'entity',
    ],
    // user-registered
    [
        'id' => 'toolbar',
        'handle' => 'user-registered',
        'type' => 'node',
        'parent' => 'top',
        'sort' => -100,
        'vars' => ['title' => 'Toolbar', 'root_id' => 2],
    ],
    // user.dashboard
    [
        'id' => 'dashboard',
        'handle' => 'user.dashboard',
        'type' => 'template',
        'template' => 'user.dashboard.phtml',
        'parent' => 'content',
    ],
    // user.profile
    [
        'id' => 'profile',
        'handle' => 'user.profile',
        'type' => 'template',
        'template' => 'user.profile.phtml',
        'parent' => 'content',
    ],
    // user-login
    [
        'id' => 'login',
        'handle' => 'user.login',
        'type' => 'template',
        'template' => 'user.login.phtml',
        'parent' => 'content',
    ],
];
