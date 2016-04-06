<?php
return [
    // action
    [
        'id' => 'akilli\action_create',
        'event' => 'action.create',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_edit',
        'event' => 'action.edit',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_delete',
        'event' => 'action.delete',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_view',
        'event' => 'action.view',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_index',
        'event' => 'action.index',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_list',
        'event' => 'action.list',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_error',
        'event' => 'action.error',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_denied',
        'event' => 'action.denied',
        'sort_order' => 100,
    ],
    [
        'id' => 'akilli\action_account_dashboard',
        'event' => 'action.account.dashboard',
        'sort_order' => 0,
    ],
    [
        'id' => 'akilli\action_account_profile',
        'event' => 'action.account.profile',
        'sort_order' => 0,
    ],
    [
        'id' => 'akilli\action_account_login',
        'event' => 'action.account.login',
        'sort_order' => 0,
    ],
    [
        'id' => 'akilli\action_account_logout',
        'event' => 'action.account.logout',
        'sort_order' => 0,
    ],
    [
        'id' => 'akilli\action_http_index',
        'event' => 'action.http.index',
        'sort_order' => 0,
    ],
    // data
    [
        'id' => 'akilli\listener_config',
        'event' => 'data.load.config',
        'sort_order' => -200,
    ],
    [
        'id' => 'akilli\listener_metadata',
        'event' => 'data.load.metadata',
        'sort_order' => -200,
    ],
    [
        'id' => 'akilli\listener_privilege',
        'event' => 'data.load.privilege',
        'sort_order' => -100,
    ],
    // model
    [
        'id' => 'akilli\listener_model_save',
        'event' => 'model.save_after',
        'sort_order' => -100,
    ],
    [
        'id' => 'akilli\listener_model_delete',
        'event' => 'model.delete_after',
        'sort_order' => -100,
    ],
    [
        'id' => 'akilli\listener_eav',
        'event' => 'model.load.eav',
        'sort_order' => -100,
    ],
];
