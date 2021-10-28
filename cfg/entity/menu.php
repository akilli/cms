<?php
declare(strict_types=1);

return [
    'name' => 'Menu',
    'action' => ['add', 'delete', 'edit', 'index'],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'type' => 'serial',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'max' => 100,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'urlpath',
            'nullable' => true,
        ],
        'parent_id' => [
            'name' => 'Parent Item',
            'type' => 'entity',
            'ref' => 'menu',
            'nullable' => true,
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
        ],
        'position' => [
            'name' => 'Position',
            'type' => 'position',
        ],
        'level' => [
            'name' => 'Level',
            'type' => 'int',
            'auto' => true,
        ],
        'path' => [
            'name' => 'Path',
            'type' => 'multientity',
            'ref' => 'menu',
            'auto' => true,
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
            'auto' => true,
        ],
    ],
];
