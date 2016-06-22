<?php
return [
    // data
    [
        'id' => 'data_config',
        'event' => 'data.load.config',
        'sort' => -100,
    ],
    [
        'id' => 'data_entity',
        'event' => 'data.load.entity',
        'sort' => -100,
    ],
    [
        'id' => 'data_privilege',
        'event' => 'data.load.privilege',
        'sort' => -100,
    ],
    // Entity
    [
        'id' => 'entity_save',
        'event' => 'entity.postSave',
        'sort' => -100,
    ],
    [
        'id' => 'entity_delete',
        'event' => 'entity.postDelete',
        'sort' => -100,
    ],
];
