<?php
return [
    // _base_
    [
        'id' => 'root',
        'handle' => '_base_',
        'type' => 'template',
        'template' => 'layout/root.phtml',
    ],
    [
        'id' => 'head',
        'handle' => '_base_',
        'type' => 'template',
        'template' => 'layout/head.phtml',
        'vars' => ['meta' => []],
    ],
    [
        'id' => 'top',
        'handle' => '_base_',
        'type' => 'container',
    ],
    [
        'id' => 'left',
        'handle' => '_base_',
        'type' => 'container',
        'vars' => ['tag' => 'aside'],
    ],
    [
        'id' => 'message',
        'handle' => '_base_',
        'type' => 'message',
        'template' => 'layout/message.phtml',
    ],
    [
        'id' => 'main',
        'handle' => '_base_',
        'type' => 'container',
    ],
    [
        'id' => 'right',
        'handle' => '_base_',
        'type' => 'container',
        'vars' => ['tag' => 'aside'],
    ],
    [
        'id' => 'bottom',
        'handle' => '_base_',
        'type' => 'container',
    ],
    // action-edit
    [
        'id' => 'content',
        'handle' => 'action-edit',
        'type' => 'template',
        'template' => 'entity/edit.phtml',
        'vars' => ['context' => 'edit'],
        'parent' => 'main',
    ],
    // action-form
    [
        'id' => 'content',
        'handle' => 'action-form',
        'type' => 'template',
        'template' => 'entity/edit.phtml',
        'vars' => ['context' => 'form'],
        'parent' => 'main',
    ],
    // action-view
    [
        'id' => 'content',
        'handle' => 'action-view',
        'type' => 'template',
        'template' => 'entity/view.phtml',
        'vars' => ['context' => 'view'],
        'parent' => 'main',
    ],
    // action-admin
    [
        'id' => 'content',
        'handle' => 'action-admin',
        'type' => 'template',
        'template' => 'entity/admin.phtml',
        'vars' => ['context' => 'admin'],
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
        'vars' => ['context' => 'index'],
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
        'type' => 'toolbar',
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
