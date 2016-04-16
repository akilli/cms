<?php
return [
    // data
    [
        'id' => 'qnd\listener_config',
        'event' => 'data.load.config',
        'sort_order' => -200,
    ],
    [
        'id' => 'qnd\listener_meta',
        'event' => 'data.load.meta',
        'sort_order' => -200,
    ],
    [
        'id' => 'qnd\listener_privilege',
        'event' => 'data.load.privilege',
        'sort_order' => -100,
    ],
    // model
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
    [
        'id' => 'qnd\listener_eav',
        'event' => 'model.load.eav',
        'sort_order' => -100,
    ],
];
