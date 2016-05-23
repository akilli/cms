<?php
return [
    // data
    [
        'id' => 'qnd\listener_data_config',
        'event' => 'data.load.config',
        'sort' => -100,
    ],
    [
        'id' => 'qnd\listener_data_entity',
        'event' => 'data.load.entity',
        'sort' => -100,
    ],
    [
        'id' => 'qnd\listener_data_privilege',
        'event' => 'data.load.privilege',
        'sort' => -100,
    ],
    [
        'id' => 'qnd\listener_data_toolbar',
        'event' => 'data.load.toolbar',
        'sort' => -100,
    ],
    // Entity
    [
        'id' => 'qnd\listener_entity_save',
        'event' => 'entity.postSave',
        'sort' => -100,
    ],
    [
        'id' => 'qnd\listener_entity_delete',
        'event' => 'entity.postDelete',
        'sort' => -100,
    ],
];
