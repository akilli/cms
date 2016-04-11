<?php
return [
    // data
    [
        'id' => 'akilli\listener_config',
        'event' => 'data.load.config',
        'sort_order' => -200,
    ],
    [
        'id' => 'akilli\listener_meta',
        'event' => 'data.load.meta',
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
