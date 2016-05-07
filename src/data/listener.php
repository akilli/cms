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
    // Entity
    [
        'id' => 'qnd\listener_entity_eav',
        'event' => 'entity.load.eav',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_entity_save',
        'event' => 'entity.postSave',
        'sort_order' => -100,
    ],
    [
        'id' => 'qnd\listener_entity_delete',
        'event' => 'entity.postDelete',
        'sort_order' => -100,
    ],
];
