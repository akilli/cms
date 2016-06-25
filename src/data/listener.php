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
        'id' => 'save',
        'event' => 'entity.postSave',
        'sort' => -100,
    ],
    [
        'id' => 'delete',
        'event' => 'entity.postDelete',
        'sort' => -100,
    ],
    [
        'id' => 'entity_save',
        'event' => 'entity.postSave.entity',
        'sort' => -100,
    ],
    [
        'id' => 'entity_delete',
        'event' => 'entity.postDelete.entity',
        'sort' => -100,
    ],
    [
        'id' => 'project_save',
        'event' => 'entity.postSave.project',
        'sort' => -100,
    ],
    [
        'id' => 'project_delete',
        'event' => 'entity.postDelete.project',
        'sort' => -100,
    ],
];
