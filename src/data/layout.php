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
        'id' => 'entity.create',
        'handle' => 'action-create',
        'type' => 'template',
        'template' => 'entity.edit.phtml',
        'parent' => 'content',
    ],
    // action-edit
    [
        'id' => 'entity.edit',
        'handle' => 'action-edit',
        'type' => 'template',
        'template' => 'entity.edit.phtml',
        'parent' => 'content',
    ],
    // action-view
    [
        'id' => 'entity.view',
        'handle' => 'action-view',
        'type' => 'template',
        'template' => 'entity.view.phtml',
        'parent' => 'content',
    ],
    // action-index
    [
        'id' => 'entity.index',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'entity.index.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'entity.index.create',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'entity.create.phtml',
        'privilege' => 'create',
        'parent' => 'entity.index',
    ],
    [
        'id' => 'entity.index.pager',
        'handle' => 'action-index',
        'type' => 'pager',
        'template' => 'pager.phtml',
        'parent' => 'entity.index',
    ],
    [
        'id' => 'entity.index.search',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'search.phtml',
        'parent' => 'main',
    ],
    // action-list
    [
        'id' => 'entity.list',
        'handle' => 'action-list',
        'type' => 'template',
        'template' => 'entity.list.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'entity.list.pager',
        'handle' => 'action-list',
        'type' => 'pager',
        'template' => 'pager.phtml',
        'parent' => 'entity.list',
    ],
    // user-registered
    [
        'id' => 'toolbar',
        'handle' => 'user-registered',
        'type' => 'node',
        'parent' => 'top',
        'sort' => -100,
        'vars' => ['root_id' => 2],
    ],
    // user.dashboard
    [
        'id' => 'user.dashboard',
        'handle' => 'user.dashboard',
        'type' => 'template',
        'template' => 'user.dashboard.phtml',
        'parent' => 'content',
    ],
    // user.profile
    [
        'id' => 'user.profile',
        'handle' => 'user.profile',
        'type' => 'template',
        'template' => 'user.profile.phtml',
        'parent' => 'content',
    ],
    // user-login
    [
        'id' => 'user.login',
        'handle' => 'user.login',
        'type' => 'template',
        'template' => 'user.login.phtml',
        'parent' => 'content',
    ],
];
