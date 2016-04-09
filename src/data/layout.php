<?php
return [
    // layout-base
    [
        'id' => 'root',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'html.root.phtml',
    ],
    [
        'id' => 'head',
        'handle' => 'layout-base',
        'type' => 'container',
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
        'template' => 'session.message.phtml',
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
    [
        'id' => 'meta',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'html.meta.phtml',
        'parent' => 'head',
        'sort_order' => 100,
    ],
    [
        'id' => 'title',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'html.title.phtml',
        'parent' => 'head',
        'sort_order' => 200,
    ],
    [
        'id' => 'css',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'html.css.phtml',
        'parent' => 'head',
        'sort_order' => 300,
    ],
    [
        'id' => 'icon',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'html.icon.phtml',
        'parent' => 'head',
        'sort_order' => 400,
    ],
    [
        'id' => 'js',
        'handle' => 'layout-base',
        'type' => 'template',
        'template' => 'html.js.phtml',
        'parent' => 'head',
        'sort_order' => 500,
    ],
    // account-registered
    [
        'id' => 'toolbar',
        'handle' => 'account-registered',
        'type' => 'template',
        'template' => 'toolbar.menu.phtml',
        'parent' => 'top',
        'sort_order' => -100,
    ],
    [
        'id' => 'toolbar.menu',
        'handle' => 'account-registered',
        'type' => 'toolbar',
        'template' => 'toolbar.list.phtml',
        'parent' => 'toolbar',
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
        'template' => 'entity.pager.phtml',
        'parent' => 'entity.index',
    ],
    [
        'id' => 'entity.index.search',
        'handle' => 'action-index',
        'type' => 'template',
        'template' => 'search.form.phtml',
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
        'template' => 'entity.pager.phtml',
        'parent' => 'entity.list',
    ],
    // account.dashboard
    [
        'id' => 'account.dashboard',
        'handle' => 'account.dashboard',
        'type' => 'template',
        'template' => 'account.dashboard.phtml',
        'parent' => 'content',
    ],
    [
        'id' => 'account.dashboard.toolbar',
        'handle' => 'account.dashboard',
        'type' => 'toolbar',
        'template' => 'toolbar.list.phtml',
        'parent' => 'account.dashboard',
    ],
    // account.profile
    [
        'id' => 'account.profile',
        'handle' => 'account.profile',
        'type' => 'template',
        'template' => 'account.profile.phtml',
        'parent' => 'content',
    ],
    // account-login
    [
        'id' => 'account.login',
        'handle' => 'account.login',
        'type' => 'template',
        'template' => 'account.login.phtml',
        'parent' => 'content',
    ],
];
