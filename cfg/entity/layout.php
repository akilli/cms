<?php
declare(strict_types=1);

return [
    'name' => 'Layout',
    'action' => ['add', 'delete', 'edit', 'index'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'uid',
            'required' => true,
            'max' => 100,
        ],
        'block_entity_id' => [
            'name' => 'Block Entity',
            'type' => 'text',
            'auto' => true,
            'autoindex' => false,
            'max' => 50,
        ],
        'block_id' => [
            'name' => 'Block',
            'type' => 'entity',
            'ref' => 'block',
            'required' => true,
        ],
        'page_id' => [
            'name' => 'Page',
            'type' => 'entity',
            'ref' => 'page',
            'required' => true,
        ],
        'parent_id' => [
            'name' => 'Container',
            'type' => 'select',
            'opt' => 'container',
            'required' => true,
            'max' => 100,
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
