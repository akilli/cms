<?php
return [
    // data
    [
        'id' => 'qnd\listener_data_config',
        'event' => 'data.load.config',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_data_meta',
        'event' => 'data.load.meta',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_data_privilege',
        'event' => 'data.load.privilege',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_data_toolbar',
        'event' => 'data.load.toolbar',
        'sort_order' => -100,
    ],
    // model
    [
        'id' => 'qnd\listener_model_eav',
        'event' => 'model.load.eav',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_model_save',
        'event' => 'model.save_after',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_model_delete',
        'event' => 'model.delete_after',
        'sort_order' => -100,
    ],
];
