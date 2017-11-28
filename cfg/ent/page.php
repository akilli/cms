<?php
return [
    'name' => 'Pages',
    'act' => [
        'admin' => ['pos', 'name', 'url', 'active'],
        'delete' => [],
        'edit' => ['name', 'url', 'active', 'parent_id', 'sort', 'content'],
        'index' => ['name'],
        'view' => ['content']
    ],
    'attr' => [
        'id' => [
            'name' => 'ID',
            'auto' => true,
            'type' => 'int',
        ],
        'name' => [
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'searchable' => true,
            'maxlength' => 100,
        ],
        'url' => [
            'name' => 'URL',
            'type' => 'text',
            'required' => true,
            'unique' => true,
            'filter' => 'path',
        ],
        'active' => [
            'name' => 'Active',
            'type' => 'toggle',
        ],
        'content' => [
            'name' => 'Content',
            'type' => 'rte',
            'searchable' => true,
            'val' => '',
        ],
        'created' => [
            'name' => 'Created',
            'type' => 'datetime',
        ],
        'modified' => [
            'name' => 'Modified',
            'type' => 'datetime',
        ],
        'parent_id' => [
            'name' => 'Parent',
            'type' => 'ent',
            'nullable' => true,
            'opt' => 'page',
        ],
        'sort' => [
            'name' => 'Sort',
            'type' => 'int',
            'val' => 0,
        ],
        'pos' => [
            'name' => 'Position',
            'type' => 'text',
            'viewer' => 'pos',
        ],
        'depth' => [
            'name' => 'Depth',
            'type' => 'int',
        ],
        'path' => [
            'name' => 'Path',
            'type' => 'json',
            'opt' => 'page',
        ],
    ],
];
