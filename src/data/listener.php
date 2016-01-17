<?php
return [
    // action
    [
        'id' => 'action\create_action',
        'event' => 'action.create',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\edit_action',
        'event' => 'action.edit',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\delete_action',
        'event' => 'action.delete',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\view_action',
        'event' => 'action.view',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\index_action',
        'event' => 'action.index',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\list_action',
        'event' => 'action.list',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\error_action',
        'event' => 'action.error',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\denied_action',
        'event' => 'action.denied',
        'sort_order' => 100,
    ],
    [
        'id' => 'action\account_dashboard_action',
        'event' => 'action.account.dashboard',
        'sort_order' => 0,
    ],
    [
        'id' => 'action\account_profile_action',
        'event' => 'action.account.profile',
        'sort_order' => 0,
    ],
    [
        'id' => 'action\account_login_action',
        'event' => 'action.account.login',
        'sort_order' => 0,
    ],
    [
        'id' => 'action\account_logout_action',
        'event' => 'action.account.logout',
        'sort_order' => 0,
    ],
    [
        'id' => 'action\http_index_action',
        'event' => 'action.http.index',
        'sort_order' => 0,
    ],
    // data
    [
        'id' => 'listener\config',
        'event' => 'data.load.config',
        'sort_order' => -200,
    ],
    [
        'id' => 'listener\metadata',
        'event' => 'data.load.metadata',
        'sort_order' => -200,
    ],
    [
        'id' => 'listener\privilege',
        'event' => 'data.load.privilege',
        'sort_order' => -100,
    ],
    // model
    [
        'id' => 'listener\model_save',
        'event' => 'model.save_after',
        'sort_order' => -100,
    ],
    [
        'id' => 'listener\model_delete',
        'event' => 'model.delete_after',
        'sort_order' => -100,
    ],
    [
        'id' => 'listener\eav',
        'event' => 'model.load.eav',
        'sort_order' => -100,
    ],
];
