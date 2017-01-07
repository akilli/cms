<?php
return [
    [
        'id' => 'data_app',
        'event' => 'data.load.app',
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
    [
        'id' => 'data_request',
        'event' => 'data.load.request',
        'sort' => -100,
    ],
    [
        'id' => 'data_toolbar',
        'event' => 'data.load.toolbar',
        'sort' => -100,
    ],
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
        'id' => 'eav_save',
        'event' => 'model.preSave.eav',
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
